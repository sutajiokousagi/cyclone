<?php

	set_include_path('modules' . PATH_SEPARATOR . get_include_path());

	require_once ("google_api/apiClient.php");
	require_once ("google_api/contrib/apiGanService.php");
	require_once ("util_preferences.php");
	
	session_start();
	
	$cyclone_user_id = 0;
	if (isset($_POST['cyclone_user_id']))			$cyclone_user_id = $_POST['cyclone_user_id'];
	if (isset($_GET['cyclone_user_id']))			$cyclone_user_id = $_GET['cyclone_user_id'];
	if (isset($_SESSION['cyclone_user_id']))	$cyclone_user_id = $_SESSION['cyclone_user_id'];
	if ($cyclone_user_id <= 0)
	    die("cyclone_user_id not set");
		
	//Google API client object
	$client = new apiClient();
	$client->setApplicationName("Google Apps PHP Starter Application");
	$client->setScopes(array(
		'https://mail.google.com/mail/feed/atom/'
	));
	
	$callbackUrl = getCallbackURL();

	// Documentation: http://code.google.com/googleapps/domain/provisioning_API_v2_developers_guide.html
	// Visit https://code.google.com/apis/console to generate your
	// oauth2_client_id, oauth2_client_secret, and to register your oauth2_redirect_uri.
	$client->setClientId( func_getSystemPreference('system_ext_clientid_google') );
	$client->setClientSecret( func_getSystemPreference('system_ext_clientsecret_google') );
	$client->setDeveloperKey( func_getSystemPreference('system_ext_devkey_google') );
	$client->setRedirectUri( $callbackUrl );

	//Redirect to Google auth page
  $url = $client->createAuthUrl();
  header('Location: ' . $url);


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