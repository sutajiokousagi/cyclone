<?php
/*
- Created by Torin
- This is a Twitter module that provides actions for Twitter
*/

require_once("baseclass_action.php");
require_once("util_preferences.php");

//additional include path
$path = 'modules';
set_include_path(get_include_path() . PATH_SEPARATOR . $path);

require_once('modules/Zend/Service/Twitter.php');
require_once('modules/Zend/Oauth/Token/Access.php');

class action_twitter extends baseclass_action
{
	const TWEET_ACTION_NAME = "tweet";
	protected $twitter_client = null;
		
	/*
	 * Override superclass to return my own version number
	 */
	public function getModuleVersion()
	{
		return "1.0";
	}
	
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
		$twitter = new Zend_Service_Twitter();
		$twitter->setLocalHttpClient( $token->getHttpClient($oAuthConfig) );

		$response = $twitter->status->update($text);
		print_r($response);
		
		$action_params_array['success'] = success;
		return $action_params_array;
	}
}

?>