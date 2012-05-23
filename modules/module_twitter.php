<?php
/*
- Created by Torin
- This is a Twitter module that provides triggers and actions for Twitter
*/

require_once("baseclass_hybrid.php");
require_once("util_preferences.php");
require_once('Zend/Service/Twitter.php');
require_once('Zend/Oauth/Token/Access.php');

class module_twitter extends baseclass_hybrid
{
	const TWEET_ACTION_NAME = "tweet";
	const NEW_TWEET_TRIGGER_ALIAS = "new tweet from you";
	const NEW_TWEET_SOMEONE_TRIGGER_ALIAS = "new tweet from someone";
	const NEW_FOLLOWER_TRIGGER_ALIAS = "new follower";
	const MENTIONED_TRIGGER_ALIAS = "mentioned";
	
	protected $twitter_client = null;
			
	/*
	 * Override superclass to return my own version number
	 */
	public function getModuleVersion()
	{
		return "1.0";
	}
	
	protected function setup_client()
	{
		$this->twitter_client = null;
		
		//Configuration from database
		$access_token = $this->getConfigByName('oauth_token');
		$access_token_secret = $this->getConfigByName('oauth_token_secret');
		if ($access_token == null || $access_token_secret == null)
			return null;
		//$access_token = "277880346-pSPZhSNGUibulpcH33HYM1uVugBx9QHH06tsXMG9";
		//$access_token_secret = "BkrsHuGDVEw6tPe1xCYKCwqmHcXg7aN4Ea1Q8Gebpg";
		
		//Setup Twitter OAuth client
		$oAuthConfig = array(
		    'callbackUrl'    => func_getSystemPreference('system_ext_callbackUrl_twitter'),
			'consumerKey' 	 => func_getSystemPreference('system_ext_consumerKey_twitter'),
			'consumerSecret' => func_getSystemPreference('system_ext_consumerSecret_twitter'),
		    'siteUrl'        => 'http://twitter.com/oauth'
		);
		$token = new Zend_Oauth_Token_Access();
		$token->setToken($access_token);
		$token->setTokenSecret($access_token_secret);			 
		$this->twitter_client = new Zend_Service_Twitter();
		$this->twitter_client->setLocalHttpClient( $token->getHttpClient($oAuthConfig) );
		return $this->twitter_client;
	}
	
	private function get_preference_name($prefix, $username)
	{
		$url = preg_replace("/[^a-zA-Z0-9s]/", "", $user);
		$pref_name = "" . $this->module_id . "_previous_" . $prefix . "_" . $username;
		return $pref_name;
	}
	
	private function get_previous_array($prefix, $username)
	{
		//Get previous remembered feed entries & convert it to an associative array
		$pref_name = $this->get_preference_name($prefix, $username);
		$pref_value = func_getPreference($this->user_id, $pref_name);
		$param_array = null;
		if ($pref_value != null)
			$param_array = json_decode(func_json_clean_param_string($pref_value), true);
		return $param_array;
	}
	
	private function save_new_array($prefix, $username, $current_array)
	{
		$pref_name = $this->get_preference_name($prefix, $username);
		func_setPreference($this->user_id, $pref_name, json_encode($current_array));
	}
	
	
	//---------------------------------------------------------------------------------------------------
	//---------------------------------------------------------------------------------------------------
	//---------------------------------------------------------------------------------------------------

	protected function action_by_name($action_alias, $event_params_array, $action_params_array)
	{
		/*
		 * This module has no headless action
		 */


		
		/*
		 * Implement all headless actions above this line
		 */
		if ($action_params_array == null) {
			echo "error: event_param is empty<br/>\n";
			return null;
		}
		
		//Replace special tags with dynamic variables
		if ($event_params_array != null) {
			foreach ($event_params_array as $dyn_key => $dyn_value) {
				$dyn_key = "[" . $dyn_key . "]";
				foreach ($action_params_array as $key => $value) {
					$action_params_array[$key] = str_replace($dyn_key, $dyn_value, $value);
				}
			}
		}
		
		if ($action_alias == self::TWEET_ACTION_NAME)
			return $this->action_tweet($event_params_array, $action_params_array);

		echo "error: action_alias $action_alias is not supported<br/>\n";
		return null;
	}

	/*
	   Parameter:
		$text in free format <= 140 characters
	 */
	protected function action_tweet($event_params_array, $action_params_array)
	{
		$text = null;
		foreach ($action_params_array as $key => $value) {
			if ($key == 'text')			$text = $value;
		}
		if ($text == null)
			return null;
		
		//Setup Twitter OAuth client
		$this->setup_client();

		$response = $this->twitter_client->status->update($text);
		$date = "" . $status->created_at; 
	    $id = "" . $status->id;
	
		$success = false;
		if (strlen($date) > 5 && strlen($id) > 5)
			$success = true;

		$action_params_array['success'] = $success;
		return $action_params_array;
	}
	
	//---------------------------------------------------------------------------------------------------
	//---------------------------------------------------------------------------------------------------
	//---------------------------------------------------------------------------------------------------
	
	/*
	 * Perform a trigger by its alias
	 * We use alias instead of ID for portability & coder-friendly purposes
	 * Subclass to override this function and perform their magic
	 * $rule_trigger_param is optional depending on the trigger implementation
	 */
	protected function trigger_by_alias($trigger_alias, $trigger_param_array, $rule_id)
	{	
		//Setup Twitter OAuth client
		$this->setup_client();
		if ($this->twitter_client == null) {
			echo "error: failed to initialize Twitter client <br/>\n";
			return null;
		}
				
		if ($trigger_alias == self::NEW_TWEET_TRIGGER_ALIAS)
		{
			//Get current user timeline
			$raw_response = $this->twitter_client->status->userTimeline();
			return $this->compare_timeline($trigger_alias, $trigger_param_array, $rule_id, $raw_response);
		}
		
		if ($trigger_alias == self::NEW_FOLLOWER_TRIGGER_ALIAS)
		{
			$raw_response = $this->twitter_client->user->followers();
			return $this->compare_follower($trigger_alias, $trigger_param_array, $rule_id, $raw_response);
		}
		
		if ($trigger_alias == self::MENTIONED_TRIGGER_ALIAS)
		{
			return null;
		}
		
		/*
		 * Implement all headless triggers above this line
		 */
		if ($trigger_param_array == null) {
			return null;
		}
		
		if ($trigger_alias == self::NEW_TWEET_SOMEONE_TRIGGER_ALIAS)
		{
			$screen_name = null;
			foreach ($trigger_param_array as $key => $value)
				if ($key == 'screen_name')		$screen_name = trim($value);
				
			if ($screen_name == null) {
				echo "error: trigger_param_array is missing some parameters <br/>\n";
				return null;
			}
			
			$param = array();
			$param['screen_name'] = $screen_name;
			$raw_userinfo = $this->twitter_client->status->userTimeline($param);
			return $this->compare_timeline($trigger_alias, $trigger_param_array, $rule_id, $raw_userinfo);
		}
	}
	
	/*
	 * A common function to compare old/new timeline & trigger neccessary event
	 * $raw_response must only contains tweets/statuses from desired user, use Twitter API to do correct filtering
	 */
	protected function compare_timeline($trigger_alias, $trigger_param_array, $rule_id, $raw_response)
	{
		//Loop through results, convert it to a native array
		$current_array = array();
		$username = null;
		foreach ($raw_response->status as $status)
		{
		    $username = "" . $status->user->screen_name;
			$item_dict = array();
		    $item_dict['date'] = "" . $status->created_at; 
		    $item_dict['text'] = "" . $status->text; 
			$item_dict['screen_name'] = $username; 
			$current_array[] = $item_dict;
		}

		//This trigger has not been run before, no data to compare
		$previous_array = $this->get_previous_array("timeline", $username);
		if ($previous_array == null) {
			echo "trigger $trigger_alias: no previous data to compare <br/>\n";
			$this->save_new_array("timeline", $username, $current_array);
			return array();
		}
		
		//Currently has no tweets at all. Some people are weird
		if (count($current_array) <= 0) {
			echo "trigger $trigger_alias: timeline has no item. wat?? <br/>\n";
			$this->save_new_array("timeline", $username, $current_array);
			return array();
		}
		
		//Compare new feed with old feed
		//This way of comparison allows user to delete old tweet without triggering
		$new_items = array();
		foreach($current_array as $item_dict)
		{
			$new_key = "" . $item_dict['date'] . "|" . $item_dict['screen_name'] . "|" . $item_dict['text'];
			$exists = false;
			foreach($previous_array as $old_item_dict)
			{
				$old_key = "" . $old_item_dict['date'] . "|" . $old_item_dict['screen_name'] . "|" . $old_item_dict['text'];				
				if (strcmp($new_key, $old_key) != 0)
					continue;
				$exists = true;
				break;
			}

			if (!$exists)
				$new_items[] = $item_dict['text'];
		}
		
		//No new update
		if (count($new_items) <= 0) {
			echo "trigger $trigger_alias: no changes. total " . count($current_array)  . " items <br/>\n";
			$this->save_new_array("timeline", $username, $current_array);
			return array();
		}
		
		echo "trigger $trigger_alias: has changes. total " . count($current_array)  . " items, " . count($new_items) . " new items <br/>\n";
		$this->save_new_array("timeline", $username, $current_array);

		$trigger_param_array = array();
		$trigger_param_array['username'] = $username;
		$trigger_param_array['count'] = count($new_items);
		$trigger_param_array['tweets'] = join(", ", $new_items);
		for($i=0; $i<count($new_items); $i++)
			$trigger_param_array['tweet'.($i+1)] = $new_items[$i];
		$trigger_param_json = json_encode($trigger_param_array);

		//Add new event to global event queue
		$event_id = $this->add_event($trigger_alias, $trigger_param_json, $rule_id);
		$this->prettyPrintout($trigger_alias, $event_id);
		return array($event_id);
	}
	
	/*
	 * A common function to compare old/new follower list & trigger neccessary event
	 * $raw_response must only contains tweets/statuses from desired user, use Twitter API to do correct filtering
	 */
	protected function compare_follower($trigger_alias, $trigger_param_array, $rule_id, $raw_response)
	{
		//Loop through results, convert it to a native array
		$current_array = array();
		$username = "my_followers";
		foreach ($raw_response as $user)
		{
			$item_dict = array();
		    $item_dict['created_at'] = "" . $status->created_at; 
			$item_dict['screen_name'] = "" . $user->screen_name;
			$current_array[] = $item_dict;
		}
		
		//This trigger has not been run before, no data to compare
		$previous_array = $this->get_previous_array("follower", $username);
		if ($previous_array == null) {
			echo "trigger $trigger_alias: no previous data to compare <br/>\n";
			$this->save_new_array("follower", $username, $current_array);
			return array();
		}
		
		//Currently has no tweets at all. Some people are weird
		if (count($current_array) <= 0) {
			echo "trigger $trigger_alias: user has no follower. wat?? <br/>\n";
			$this->save_new_array("follower", $username, $current_array);
			return array();
		}
		
		//Compare new feed with old feed
		//This way of comparison allows user to delete old tweet without triggering
		$new_items = array();
		foreach($current_array as $item_dict)
		{
			$new_key = "" . $item_dict['created_at'] . "|" . $item_dict['screen_name'];
			$exists = false;
			foreach($previous_array as $old_item_dict)
			{
				$old_key = "" . $old_item_dict['created_at'] . "|" . $old_item_dict['screen_name'];				
				if (strcmp($new_key, $old_key) != 0)
					continue;
				$exists = true;
				break;
			}

			if (!$exists)
				$new_items[] = $item_dict['screen_name'];
		}
		
		//No new update
		if (count($new_items) <= 0) {
			echo "trigger $trigger_alias: no changes. total " . count($current_array)  . " items <br/>\n";
			$this->save_new_array("follower", $username, $current_array);
			return array();
		}
		
		echo "trigger $trigger_alias: has changes. total " . count($current_array)  . " items, " . count($new_items) . " new items <br/>\n";
		$this->save_new_array("follower", $username, $current_array);

		$trigger_param_array = array();
		$trigger_param_array['count'] = count($new_items);
		$trigger_param_array['followers'] = join(", ", $new_items);
		for($i=0; $i<count($new_items); $i++)
			$trigger_param_array['follower'.($i+1)] = $new_items[$i];
		$trigger_param_json = json_encode($trigger_param_array);

		//Add new event to global event queue
		$event_id = $this->add_event($trigger_alias, $trigger_param_json, $rule_id);
		$this->prettyPrintout($trigger_alias, $event_id);
		return array($event_id);
	}
	
}

?>