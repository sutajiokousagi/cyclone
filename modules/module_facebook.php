<?php
/*
- Created by Torin
- This is a Facebook module that provides triggers and actions for Facebook
*/

require_once("baseclass_hybrid.php");
require_once("util_preferences.php");
require_once("facebook_sdk/facebook.php");

class module_facebook extends baseclass_hybrid
{
	const UPDATE_STATUS_ACTION_NAME = "update_status";
	const STATUS_CHANGE_TRIGGER_ALIAS = "status_change";
	const GOT_TAGGED_TRIGGER_ALIAS = "got_tagged";
	const NEW_POST_TRIGGER_ALIAS = "new_post";
	const UPLOAD_NEW_PHOTO_TRIGGER_ALIAS = "upload_new_photo";
	const PROFILE_CHANGE_TRIGGER_ALIAS = "profile_change";
	
	protected $facebook_client = null;
	protected $user_profile = null;
	
	/*
	 * Override superclass to return my own version number
	 */
	public function getModuleVersion()
	{
		return "1.0";
	}
	
	protected function setup_client()
	{
		if ($this->facebook_client != null)
			return $this->facebook_client;
			
		$this->facebook_client = null;
        $this->user_profile = null;

		//Configuration from database
		$access_token = $this->getConfigByName('oauth_token');
		if ($access_token == null)
			return null;

		//Setup Facebook SDK object
		$this->facebook_client = new Facebook(array(
			'appId'  => func_getSystemPreference('system_ext_appid_facebook'),
			'secret' => func_getSystemPreference('system_ext_appsecret_facebook'),
		));
		$this->facebook_client->setAccessToken($access_token);
		
		//Not logged in
		$user_id = $this->facebook_client->getUser();
		if(!$user_id) {
			$this->facebook_client = null;
			return null;
		}
			
		//Check for error with access/permission/expired token
		try
		{
	        $this->user_profile = $this->facebook_client->api('/me','GET');
	
			/* Nothing much here
			   "id": "566862027",
			   "name": "Torin Nguyen",
			   "first_name": "Torin",
			   "last_name": "Nguyen",
			   "link": "https://www.facebook.com/torinnguyen",
			   "username": "torinnguyen",
			   "birthday": "07/31/1985",
			   "hometown": {
			      "id": "108458769184495",
			      "name": "Ho Chi Minh City, Vietnam"
			   },
			   "location": {
			      "id": "101883206519751",
			      "name": "Singapore, Singapore"
			   },
			*/
		}
		catch(FacebookApiException $e)
		{
	        // If the user is logged out, you can have a 
	        // user ID even though the access token is invalid.
	        // In this case, we'll get an exception
	
			//TODO: Notify the system about this error
			
			//Invalidate Facebook SDK object
	        $this->facebook_client = null;
  		}
		return $this->facebook_client;
	}
	
	private function get_graph_api($graph_path)
	{
		$access_token = $this->getConfigByName('oauth_token');
		$url = "https://graph.facebook.com" . $graph_path . "?access_token=" . $access_token;
		
		$ch = curl_init(); 
        curl_setopt($ch, CURLOPT_URL, $url); 
        curl_setopt($ch, CURLOPT_HEADER, FALSE); 
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE); 
        $response = curl_exec($ch); 
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE); 
        curl_close($ch);

		return json_decode($response, true);
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
			echo "error: action_param is empty<br/>\n";
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
		
		if ($action_alias == self::UPDATE_STATUS_ACTION_NAME)
			return $this->action_update_status($event_params_array, $action_params_array);

		echo "error: action_alias $action_alias is not supported<br/>\n";
		return null;
	}

	/*
	   Parameter:
		$message in free format
		$link in free format
	 */
	protected function action_update_status($event_params_array, $action_params_array)
	{
		$message = null;
		$link = null;
		foreach ($action_params_array as $key => $value) {
			if ($key == 'message')		$message = $value;
			if ($key == 'link')			$link = $value;
		}
		if ($message == null && $link == null)
			return null;
		
		//Setup Facebook OAuth client
		$this->setup_client();
		if ($this->facebook_client == null)
			return null;
		
		$ret_obj = $this->facebook_client->api('/me/feed', 'POST', array('link' => $link, 'message' => $message));
        //echo '<pre>Post ID: ' . $ret_obj['id'] . '</pre>';
	
		$success = false;
		if (strlen("" . $ret_obj['id']) > 5)
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
		//Setup Facebook OAuth client
		$this->setup_client();
		if ($this->facebook_client == null) {
			echo "error: failed to initialize Facebook client <br/>\n";
			return null;
		}
				
		if ($trigger_alias == self::STATUS_CHANGE_TRIGGER_ALIAS)
		{		
			//Get current user feed
			$raw_response = $this->facebook_client->api('/me/feed','GET');
			$raw_feed_array = $raw_response['data'];
			//var_dump($raw_response);
	
			return $this->compare_feed($trigger_alias, $trigger_param_array, $rule_id, $raw_feed_array);
		}

		/*
		 * Implement all headless triggers above this line
		 */
		if ($trigger_param_array == null) {
			return null;
		}
		
	}
	
	/*
	 * A common function to compare old/new feed & trigger neccessary event
	 * $raw_response must only contains posts/statuses from desired user, use Facebook API to do correct filtering
	 */
	protected function compare_feed($trigger_alias, $trigger_param_array, $rule_id, $raw_feed_array)
	{
		//Loop through results, convert it to a native array
		$current_array = array();
		$username = $this->user_profile['name'];
		foreach ($raw_feed_array as $item)
		{
		    $username = "" . $item['from']['name'];
			$item_dict = array();
		    $item_dict['id'] = "" . $item['id'];
		    $item_dict['message'] = "" . $item['message'];
		    $item_dict['link'] = "" . $item['link'];
		    $item_dict['name'] = "" . $item['name'];	//short title of the item/link
			$current_array[] = $item_dict;
		}

		//This trigger has not been run before, no data to compare
		$previous_array = $this->get_previous_array("feed", $username);
		if ($previous_array == null) {
			echo "trigger $trigger_alias: no previous data to compare <br/>\n";
			$this->save_new_array("feed", $username, $current_array);
			return array();
		}
		
		//Currently has no posts at all. Some people are weird
		if (count($current_array) <= 0) {
			echo "trigger $trigger_alias: timeline has no item. wat?? <br/>\n";
			$this->save_new_array("feed", $username, $current_array);
			return array();
		}
		
		//Compare new feed with old feed
		//This way of comparison allows user to delete old post without triggering
		$new_items = array();
		foreach($current_array as $item_dict)
		{
			$new_key = "" . $item_dict['id'] . "|" . $item_dict['message'];
			$exists = false;
			foreach($previous_array as $old_item_dict)
			{
				$old_key = "" . $old_item_dict['id'] . "|" . $old_item_dict['message'];
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
			$this->save_new_array("feed", $username, $current_array);
			return array();
		}
		
		echo "trigger $trigger_alias: has changes. total " . count($current_array)  . " items, " . count($new_items) . " new items <br/>\n";
		$this->save_new_array("feed", $username, $current_array);

		$trigger_param_array = array();
		$trigger_param_array['username'] = $username;
		$trigger_param_array['count'] = count($new_items);
		$trigger_param_array['posts'] = join(", ", $new_items);
		for($i=0; $i<count($new_items); $i++)
			$trigger_param_array['post'.($i+1)] = $new_items[$i];
		$trigger_param_json = json_encode($trigger_param_array);

		//Add new event to global event queue
		$event_id = $this->add_event($trigger_alias, $trigger_param_json, $rule_id);
		$this->prettyPrintout($trigger_alias, $event_id);
		return array($event_id);
	}
}

?>