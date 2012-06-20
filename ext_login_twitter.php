<?php

	set_include_path('modules' . PATH_SEPARATOR . get_include_path());

	require_once ("Zend/Oauth/Consumer.php");
	require_once("Zend/Service/Twitter.php");
	require_once("Zend/Oauth/Token/Access.php");
	require_once ("util_preferences.php");
	require_once ("util_modules.php");
	require_once ("util_configs.php");
	
	session_start();
	
	//Hardcoded user or returning user (from logout or callback)
	$cyclone_user_id = 1;
	if (isset($_SESSION['cyclone_user_id']))
		$cyclone_user_id = $_SESSION['cyclone_user_id'];
	$_SESSION['cyclone_user_id'] = $cyclone_user_id;

	//Retrieve the secrets from database
	$oauth_token = null;
	$oauth_token_secret = null;
	$module_id = func_getModuleIDByAlias('twitter');
	$sql_result_configs = func_getConfigsByModuleIDAndUserID($module_id, $cyclone_user_id);
	while ($sql_result_configs != null && $one_record = mysql_fetch_assoc($sql_result_configs))
	{
		$config_name = $one_record['config_name'];
		$config_value = $one_record['config_param'];
		if ($config_name == 'oauth_token')
			$oauth_token = $config_value;
		if ($config_name == 'oauth_token_secret')
			$oauth_token_secret = $config_value;
	}

	//No secrets in database
	if ($oauth_token == null) {
		displayLogin();
		die();
	}
	
	//Validate the secrets
	
	//Generic OAuth consumer client object
	$oAuthConfig = array(
	    'callbackUrl'    => "" . func_getSystemPreference('system_ext_callbackUrl_twitter'),
			'consumerKey' 	 => func_getSystemPreference('system_ext_consumerKey_twitter'),
			'consumerSecret' => func_getSystemPreference('system_ext_consumerSecret_twitter'),
	    'siteUrl'        => 'http://twitter.com/oauth'
	);
	$token = new Zend_Oauth_Token_Access();
	$token->setToken($oauth_token);
	$token->setTokenSecret($oauth_token_secret);			 
	$twitter = new Zend_Service_Twitter();
	$twitter->setLocalHttpClient( $token->getHttpClient($oAuthConfig) );
	
	//Make a test API call
	$user = null;
  try {
    // Proceed knowing you have a logged in user who's authenticated.
    $user = $twitter->account->verifyCredentials();
  } catch (Exception $e) {
    error_log($e);
    $user = null;
  }
	
	$error = "" . $user->error;

	//Valid response
	if ($error == null || strlen($error) <= 0) {
		displayLogout();
		die();
	}
	
	//Invalid response
	
	//Clean database
	func_deleteConfig($module_id, $cyclone_user_id, 'oauth_token');
	func_deleteConfig($module_id, $cyclone_user_id, 'oauth_token_secret');
	func_deleteConfig($module_id, $cyclone_user_id, 'user_id');
	func_deleteConfig($module_id, $cyclone_user_id, 'screen_name');
	
	//Show login url
	displayLogin();
	die();

	//--------------

	function displayLogout() {
		echo "Logged in";
    echo "<br><br>";
    echo "<a href='ext_logout_twitter.php'>Logout</a>";
	}
	
	function displayLogin() {
		echo "Not logged in";
    echo "<br><br>";
    echo "<a href='ext_auth_twitter.php'>Sign in to Twitter</a>";
	}


?>