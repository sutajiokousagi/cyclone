<?php

	set_include_path('modules' . PATH_SEPARATOR . get_include_path());
	require_once ("facebook_sdk/facebook.php");
	require_once ("util_preferences.php");

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
	$callbackUrl = func_getSystemPreference('system_ext_callbackUrl_facebook');
	$callbackUrl = $callbackUrl . "?cyclone_user_id=" . $cyclone_user_id;

	//Redirect to Facebook auth page
	$loginParams = array('scope' => 'offline_access,publish_stream,user_birthday,user_location,user_about_me',
			             'redirect_uri' => $callbackUrl);
	$url = $facebook->getLoginUrl($loginParams);			

	header('Location: ' . $url);
?>