<?php
/*
- Created by Torin
- This is a simple HelloTrigger module that generate trigger/triggers on even minutes (ie. alternate minutes)
*/

require_once("baseclass_trigger.php");
require_once("util_preferences.php");

class trigger_rss extends baseclass_trigger
{
	const RSS_NEW_TRIGGER_ALIAS = "rss_new";
	
	/*
	 * Override superclass to return my own version number
	 */
	public function getModuleVersion()
	{
		return "1.0";
	}
		
	private function get_preference_name($url)
	{
		$url = preg_replace("/[^a-zA-Z0-9s]/", "", $url);
		$pref_name = "" . $this->module_id . "_previous_feed_" . $url;
		return $pref_name;
	}
	
	private function get_previous_feed($url)
	{
		//Get previous remembered feed entries & convert it to an associative array
		$pref_name = $this->get_preference_name($url);
		$pref_value = func_getPreference($this->user_id, $pref_name);
		$param_array = null;
		if ($pref_value != null)
			$param_array = json_decode(func_json_clean_param_string($pref_value), true);
		return $param_array;
	}
	
	private function save_new_feed($url, $feed_array)
	{
		$pref_name = $this->get_preference_name($url);
		func_setPreference($this->user_id, $pref_name, json_encode($feed_array));
	}
	
	/*
	 * Perform a trigger by its alias
	 * We use alias instead of ID for portability & coder-friendly purposes
	 * Subclass to override this function and perform their magic
	 * $rule_trigger_param is optional depending on the trigger implementation
	 */
	protected function trigger_by_alias($trigger_alias, $trigger_param_array, $rule_id)
	{
		/*
		 * No headless trigger for this module
		 */
		
		/*
		 * Implement all headless triggers above this line
		 */
		if ($trigger_param_array == null) {
			echo "error: trigger_param_array is empty";
			return array();
		}
		
		/*
		 * All triggers need these parameters
		 */
		$url = null;
		$number = null;
		$latitude = null;
		foreach ($trigger_param_array as $key => $value) {
			if ($key == 'url')		$url = trim($value);
			if ($key == 'number')	$number = trim($value);
		}
		if ($url == null /* || $number === null */) {
			echo "error: trigger_param_array is missing some parameters";
			return null;
		}
		
		$feed_array = array();
		
		/*
		 * Download new feed data
		 */
		$xmlDoc = new DOMDocument();
		$xmlDoc->load($url);

		//get metadata of the channel
		/*
		$channel=$xmlDoc->getElementsByTagName('channel')->item(0);
		$channel_title = $channel->getElementsByTagName('title')->item(0)->childNodes->item(0)->nodeValue;
		$channel_link = $channel->getElementsByTagName('link')->item(0)->childNodes->item(0)->nodeValue;
		$channel_desc = $channel->getElementsByTagName('description')->item(0)->childNodes->item(0)->nodeValue;
		*/

		//get and output "<item>" elements
		$x = $xmlDoc->getElementsByTagName('item');
		$arrFeeds = array();
		foreach ($xmlDoc->getElementsByTagName('item') as $node)
		{
		    $item_title = $node->getElementsByTagName('title')->item(0)->nodeValue;
		    //$item_desc = $node->getElementsByTagName('description')->item(0)->nodeValue;
		    //$item_link = $node->getElementsByTagName('link')->item(0)->nodeValue;
		    //$item_date = $node->getElementsByTagName('pubDate')->item(0)->nodeValue;
		
			//Convert each entry into a dictionary & insert into a linear array
			$item_array = array();
			$item_array['title'] = $item_title;
			//$item_array['link'] = $item_link;		//save some database space
			//$item_array['desc'] = $item_desc;		//save some database space
			$feed_array[] = $item_array;
		}
		
		//this trigger has not been run before, no data to compare
		$previous_feed = $this->get_previous_feed($url);
		if ($previous_feed == null) {
			echo "trigger: no previous feed data to compare";
			$this->save_new_feed($url, $feed_array);
			return array();
		}
		
		//Currently has no news at all. Some RSS are weird
		if (count($feed_array) <= 0) {
			echo "trigger: RSS feed has no item";
			$this->save_new_feed($url, $feed_array);
			return array();
		}
						
		//Compare new feed with old feed
		$new_titles = array();
		foreach($feed_array as $item_array)
		{
			$new_title = strtolower($item_array['title']);
			$exists = false;
			foreach($previous_feed as $old_item_array)
			{
				$old_title = strtolower($old_item_array['title']);
				if (strcmp($old_title, $new_title) != 0)
					continue;
				$exists = true;
				break;
			}
				
			if (!$exists)
				$new_titles[] = $item_array['title'];
		}
		
		//No new update
		if (count($new_titles) <= 0) {
			echo "trigger: no changes. total " . count($feed_array)  . " items ";
			$this->save_new_feed($url, $feed_array);
			return array();
		}
		
		echo "trigger: has changes. total " . count($feed_array)  . " items, " . count($new_titles) . " new items <br/>";
		$this->save_new_feed($url, $feed_array);
		
		$trigger_param_array = array();
		$trigger_param_array['url'] = $url;
		$trigger_param_array['count'] = count($new_titles);
		$trigger_param_array['titles'] = join(", ", $new_titles);
		for($i=0; $i<count($new_titles); $i++)
			$trigger_param_array['title'.($i+1)] = $new_titles[$i];
		$trigger_param_json = json_encode($trigger_param_array);
		
		//Add new event to global event queue
		$event_id = $this->add_event($trigger_alias, $trigger_param_json, $rule_id);
		$this->prettyPrintout($trigger_alias, $event_id);
		return array($event_id);
	}
	
}

?>