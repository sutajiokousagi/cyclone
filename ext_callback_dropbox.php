<?php

	set_include_path('modules' . PATH_SEPARATOR . get_include_path());
	
	require_once ("dropbox/autoload.php");
	require_once ("util_preferences.php");
	require_once ("util_configs.php");
	require_once ("util_modules.php");

	//Wrong format from Dropbox
	//http://cyclone.torinnguyen.com/ext_callback_dropbox.php?uid=11295402&oauth_token=1nyuc2jjfe43kum
	if (!isset($_GET['oauth_token']))
	    die("oauth_token not set");
	
	session_start();
	
	$cyclone_user_id = 0;
	if (isset($_POST['cyclone_user_id']))			$cyclone_user_id = $_POST['cyclone_user_id'];
	if (isset($_GET['cyclone_user_id']))			$cyclone_user_id = $_GET['cyclone_user_id'];
	if (isset($_SESSION['cyclone_user_id']))	$cyclone_user_id = $_SESSION['cyclone_user_id'];
	if ($cyclone_user_id <= 0)
	    die("cyclone_user_id not set");
	
	// Set consumer key, secret and callback URL
	$consumerKey = func_getSystemPreference('system_ext_appkey_dropbox');
	$consumerSecret = func_getSystemPreference('system_ext_appsecret_dropbox');
	$oauth = new Dropbox_OAuth_Zend($consumerKey, $consumerSecret);
	
	//Get the access token
	$oauth->setToken($_SESSION['oauth_tokens_dropbox']);		//passed from auth
	$accessToken = $oauth->getAccessToken();
  $accessTokenJSON = json_encode($accessToken);

	if (!$accessTokenJSON) {	
		die();
	}

	//Save the secrets into our database
	$module_id = func_getModuleIDByAlias('dropbox');
	func_setConfig($module_id, $cyclone_user_id, 'oauth_token', $accessTokenJSON);

	header('Location: ext_login_dropbox.php');

?>