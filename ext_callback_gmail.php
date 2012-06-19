<?php

	set_include_path('modules' . PATH_SEPARATOR . get_include_path());
	
	require_once ("google_api/apiClient.php");
	require_once ("google_api/contrib/apiGanService.php");
	require_once ("util_preferences.php");
	require_once ("util_configs.php");
	require_once ("util_modules.php");

	//Wrong format from Google
	if (!isset($_GET['code']))
	    die("code not set");
	
	session_start();
	
	$cyclone_user_id = 0;
	if (isset($_POST['cyclone_user_id']))			$cyclone_user_id = $_POST['cyclone_user_id'];
	if (isset($_GET['cyclone_user_id']))			$cyclone_user_id = $_GET['cyclone_user_id'];
	if (isset($_SESSION['cyclone_user_id']))	$cyclone_user_id = $_SESSION['cyclone_user_id'];
	if ($cyclone_user_id <= 0)
	    die("cyclone_user_id not set");
	
	//Google API client object
	$client = new apiClient();
	$client->setApplicationName("Cyclone");
	$client->setScopes(array(
		'https://mail.google.com/mail/feed/atom/'
	));

	// Documentation: http://code.google.com/googleapps/domain/provisioning_API_v2_developers_guide.html
	// Visit https://code.google.com/apis/console to generate your
	// oauth2_client_id, oauth2_client_secret, and to register your oauth2_redirect_uri.
	$client->setClientId( func_getSystemPreference('system_ext_clientid_google') );
	$client->setClientSecret( func_getSystemPreference('system_ext_clientsecret_google') );
	$client->setRedirectUri( func_getSystemPreference('system_ext_callbackUrl_google') );
	$client->setDeveloperKey( func_getSystemPreference('system_ext_devkey_google') );
	
	//Get the access token
	$client->authenticate();
  $accessToken = $client->getAccessToken();
	
	//Save the secrets into our database
	$module_id = func_getModuleIDByAlias('gmail');
	func_setConfig($module_id, $cyclone_user_id, 'oauth_token', $accessToken);
	
	header('Location: ext_login_gmail.php');

?>