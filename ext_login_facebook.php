<?php

	set_include_path('modules' . PATH_SEPARATOR . get_include_path());
	
	require_once ("facebook_sdk/facebook.php");
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
	$module_id = func_getModuleIDByAlias('facebook');
	$sql_result_configs = func_getConfigsByModuleIDAndUserID($module_id, $cyclone_user_id);
	while ($sql_result_configs != null && $one_record = mysql_fetch_assoc($sql_result_configs))
	{
		$config_name = $one_record['config_name'];
		$config_value = $one_record['config_param'];
		if ($config_name == 'oauth_token')
			$oauth_token = $config_value;
	}
	
	//No secrets in database
	if ($oauth_token == null) {
		displayLogin();
		die();
	}
	
	//Validate the secrets
	
	//Facebook SDK client object
	$facebook = new Facebook(array(
		'appId'  => func_getSystemPreference('system_ext_appid_facebook'),
		'secret' => func_getSystemPreference('system_ext_appsecret_facebook'),
	));
	$facebook->setAccessToken($oauth_token);

	//Make a test API call
	$user = $facebook->getUser();
	if ($user) {
	  try {
	    // Proceed knowing you have a logged in user who's authenticated.
	    $user_profile = $facebook->api('/me');
	  } catch (FacebookApiException $e) {
	    error_log($e);
			$user_profile = null;
	    $user = null;
	  }
	}

	//Valid response
	if ($user) {
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
    echo "<a href='ext_logout_facebook.php'>Logout</a>";
	}
	
	function displayLogin() {
		echo "Not logged in";
    echo "<br><br>";
    echo "<a href='ext_auth_facebook.php'>Sign in to Facebook</a>";
	}

?>