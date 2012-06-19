<?php

	set_include_path('modules' . PATH_SEPARATOR . get_include_path());
	require_once ("Zend/Oauth/Consumer.php");
	require_once ("util_preferences.php");

	session_start();
	
	$cyclone_user_id = 0;
	if (isset($_POST['cyclone_user_id']))			$cyclone_user_id = $_POST['cyclone_user_id'];
	if (isset($_GET['cyclone_user_id']))			$cyclone_user_id = $_GET['cyclone_user_id'];
	if (isset($_SESSION['cyclone_user_id']))	$cyclone_user_id = $_SESSION['cyclone_user_id'];
	if ($cyclone_user_id <= 0)
	    die("cyclone_user_id not set");
	
	//Generic OAuth consumer client module
	$oAuthConfig = array(
	    'callbackUrl'    => "" . func_getSystemPreference('system_ext_callbackUrl_twitter') . "?cyclone_user_id=$cyclone_user_id",
			'consumerKey' 	 => func_getSystemPreference('system_ext_consumerKey_twitter'),
			'consumerSecret' => func_getSystemPreference('system_ext_consumerSecret_twitter'),
	    'siteUrl'        => 'http://twitter.com/oauth'
	);
	$oAuthConsumer = new Zend_Oauth_Consumer($oAuthConfig);

	//Redirect to Twitter auth page
	$token = $oAuthConsumer->getRequestToken();
	$_SESSION['ext_requestToken_twitter'] = serialize($token);       
	$oAuthConsumer->redirect(); 

?>