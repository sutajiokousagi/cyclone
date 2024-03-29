<?php

	set_include_path('modules' . PATH_SEPARATOR . get_include_path());
	
	require_once ("facebook_sdk/facebook.php");
	require_once ("util_preferences.php");
	require_once ("util_configs.php");
	require_once ("util_modules.php");

	//Wrong format from Facebook
	if (!isset($_GET['code']))
	    die("code not set");
	
	session_start();
	
	$cyclone_user_id = 0;
	if (isset($_POST['cyclone_user_id']))			$cyclone_user_id = $_POST['cyclone_user_id'];
	if (isset($_GET['cyclone_user_id']))			$cyclone_user_id = $_GET['cyclone_user_id'];
	if (isset($_SESSION['cyclone_user_id']))	$cyclone_user_id = $_SESSION['cyclone_user_id'];
	if ($cyclone_user_id <= 0)
	    die("cyclone_user_id not set");
	
	//Facebook SDK client object
	$facebook = new Facebook(array(
		'appId'  => func_getSystemPreference('system_ext_appid_facebook'),
		'secret' => func_getSystemPreference('system_ext_appsecret_facebook'),
	));

	//Get the access token
	$accessToken = $facebook->getAccessToken();

	//Save the secrets into our database
	$module_id = func_getModuleIDByAlias('facebook');
	func_setConfig($module_id, $cyclone_user_id, 'oauth_token', $accessToken);

	header('Location: ext_login_facebook.php');

?>