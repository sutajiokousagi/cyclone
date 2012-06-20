<?php

	set_include_path('modules' . PATH_SEPARATOR . get_include_path());
	
	require_once ("dropbox/autoload.php");
	require_once ("util_preferences.php");

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
	$callbackUrl = getCallbackURL();

	$oauth = new Dropbox_OAuth_Zend($consumerKey, $consumerSecret);
	
	$tokens = $oauth->getRequestToken();
	$authorizeUrl = $oauth->getAuthorizeUrl( $callbackUrl );
  $_SESSION['oauth_tokens_dropbox'] = $tokens;

	header('Location: ' . $authorizeUrl);
	
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