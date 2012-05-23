<?php

	session_start();

	set_include_path('modules' . PATH_SEPARATOR . get_include_path());
	require_once ("Zend/Oauth/Consumer.php");
	require_once ("util_preferences.php");
	require_once ("util_configs.php");

	//Wrong format from Twitter
	if (!isset($_GET['oauth_token'])) {
	    die("oauth_token not set");
	}
	
	if (!isset($_GET['cyclone_user_id'])) {
	    die("cyclone_user_id not set");
	}
	
	$cyclone_user_id = 0;
	if (isset($_POST['cyclone_user_id']))		$cyclone_user_id = $_POST['cyclone_user_id'];
	if (isset($_GET['cyclone_user_id']))		$cyclone_user_id = $_GET['cyclone_user_id'];

	//OAuth consumer client module
	$oAuthConfig = array(
	    'callbackUrl'    => "" . func_getSystemPreference('system_ext_callbackUrl_twitter') . "?cyclone_user_id=$cyclone_user_id",
		'consumerKey' 	 => func_getSystemPreference('system_ext_consumerKey_twitter'),
		'consumerSecret' => func_getSystemPreference('system_ext_consumerSecret_twitter'),
	    'siteUrl'        => 'http://twitter.com/oauth'
	);
	$oAuthConsumer = new Zend_Oauth_Consumer($oAuthConfig);

	$response = array(
	    'oauth_token'    => $_GET['oauth_token'],
	    'oauth_verifier' => $_GET['oauth_verifier'],
	);
	$requestToken = unserialize($_SESSION['ext_requestToken_twitter']);				//from ext_auth_xxxxx.php
	$accessToken = $oAuthConsumer->getAccessToken($response, $requestToken);
	unset($_SESSION['ext_requestToken_twitter']);
	parse_str($accessToken->getResponse()->getBody(), $params);

	//Save the secrets into our database
	$module_id = 2;		//can be hardcoded
	func_setConfig($module_id, $cyclone_user_id, 'oauth_token', $params['oauth_token']);
	func_setConfig($module_id, $cyclone_user_id, 'oauth_token_secret', $params['oauth_token_secret']);
	func_setConfig($module_id, $cyclone_user_id, 'user_id', $params['user_id']);
	func_setConfig($module_id, $cyclone_user_id, 'screen_name', $params['screen_name']);
	
	//Prevent the secrets this to be shown in HTML
	$params['oauth_token'] = '----hidden value----';
	$params['oauth_token_secret'] = '----hidden value----';
	$_SESSION['ext_auth_twitter'] = $params;

	header('Location: ext_login_twitter.php');

?>