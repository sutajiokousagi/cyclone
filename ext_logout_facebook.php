<?php

	set_include_path('modules' . PATH_SEPARATOR . get_include_path());
	require_once ("facebook_sdk/facebook.php");
	require_once ("util_preferences.php");
	require_once ("util_configs.php");
	
	session_start();
		
	$cyclone_user_id = 0;
	if (isset($_POST['cyclone_user_id']))		$cyclone_user_id = $_POST['cyclone_user_id'];
	if (isset($_GET['cyclone_user_id']))		$cyclone_user_id = $_GET['cyclone_user_id'];
	if (intval($cyclone_user_id <= 0))
		die("cyclone_user_id is invalid ($cyclone_user_id)");	
		
	//Clean database
	$module_id = 3;		//can be hardcoded
	func_deleteConfig($module_id, $cyclone_user_id, 'oauth_token');
	func_deleteConfig($module_id, $cyclone_user_id, 'oauth_token_secret');
	func_deleteConfig($module_id, $cyclone_user_id, 'user_id');
	func_deleteConfig($module_id, $cyclone_user_id, 'screen_name');

	//Clean session variable
	unset($_SESSION['ext_auth_facebook']);
	
	$facebook = new Facebook(array(
		'appId'  => func_getSystemPreference('system_ext_appid_facebook'),
		'secret' => func_getSystemPreference('system_ext_appsecret_facebook'),
	));
	
	$loginUrl = currentPageURL();
	$loginUrl = str_replace('ext_logout', 'ext_login', $loginUrl);
	$logoutParams = array( 'next' => $loginUrl );
	$url = $facebook->getLogoutUrl($logoutParams);
			
	header('Location: ' . $url);
	die();
	
	/*
	//Perform Facebook logout action
	$ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HEADER, FALSE);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
	
	header('Location: ext_login_facebook.php');
	*/

	function currentPageURL()
	{
		$pageURL = 'http';
		if ($_SERVER["HTTPS"] == "on") {$pageURL .= "s";}
		$pageURL .= "://";
		if ($_SERVER["SERVER_PORT"] != "80") {
			$pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
		} else {
			$pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
		}
		return $pageURL;
	}

?>