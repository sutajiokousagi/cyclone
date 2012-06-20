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
	
	$callbackUrl = getCallbackURL();
	
	//Generic OAuth consumer client module
	$oAuthConfig = array(
	    'callbackUrl'    => $callbackUrl,
			'consumerKey' 	 => func_getSystemPreference('system_ext_consumerKey_twitter'),
			'consumerSecret' => func_getSystemPreference('system_ext_consumerSecret_twitter'),
	    'siteUrl'        => 'http://twitter.com/oauth'
	);
	$oAuthConsumer = new Zend_Oauth_Consumer($oAuthConfig);

	//Redirect to Twitter auth page
	$token = $oAuthConsumer->getRequestToken();
	$_SESSION['ext_requestToken_twitter'] = serialize($token);       
	$oAuthConsumer->redirect(); 


	//---------------------------

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
	
	function getCallbackURL()
	{
		$url = currentPageURL();
		$url = str_replace("auth", "callback", $url);
		return $url;
	}
	
?>