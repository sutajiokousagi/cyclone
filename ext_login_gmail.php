<?php

	set_include_path('modules' . PATH_SEPARATOR . get_include_path());
	
	require_once ("google_api/apiClient.php");
	require_once ("google_api/contrib/apiGanService.php");
	require_once ("util_preferences.php");
	require_once ("util_modules.php");
	require_once ("util_configs.php");
	
	session_start();
	
	//Hardcoded user or returning user (from logout or callback)
	$cyclone_user_id = 1;
	if (isset($_SESSION['cyclone_user_id']))
		$cyclone_user_id = $_SESSION['cyclone_user_id'];
	$_SESSION['cyclone_user_id'] = $cyclone_user_id;

	//Retrieve the secrets from database
	$oauth_token = null;
	$module_id = func_getModuleIDByAlias('gmail');
	$sql_result_configs = func_getConfigsByModuleIDAndUserID($module_id, $cyclone_user_id);
	while ($sql_result_configs != null && $one_record = mysql_fetch_assoc($sql_result_configs))
	{
		$config_name = $one_record['config_name'];
		$config_value = $one_record['config_param'];
		if ($config_name == 'oauth_token')
			$oauth_token = $config_value;
	}

	//No secrets in database
	if ($oauth_token == null) {
		displayLogin();
		die();
	}
	
	//Validate the secrets
	
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
  $client->setAccessToken($oauth_token);

	//Make a test API call
  $req = new apiHttpRequest("https://mail.google.com/mail/feed/atom/");
  $resp = $client->getIo()->authenticatedRequest($req);
	$responseString = $resp->getResponseBody();
	$responseStringLower = strtolower($responseString);
	$valid = !func_contains($responseStringLower, "unauthorized") && !func_contains($responseStringLower, "error");

	//Valid response
	if ($valid) {
		displayLogout();
		die();
	}
	
	//Invalid response
	
	//Clean database
	func_deleteConfig($module_id, $cyclone_user_id, 'oauth_token');
	func_deleteConfig($module_id, $cyclone_user_id, 'oauth_token_secret');
	func_deleteConfig($module_id, $cyclone_user_id, 'user_id');
	func_deleteConfig($module_id, $cyclone_user_id, 'screen_name');
	
	//Show login url
	displayLogin();
	die();

	//--------------

	function displayLogout() {
		echo "Logged in";
    echo "<br><br>";
    echo "<a href='ext_logout_gmail.php'>Logout</a>";
	}
	
	function displayLogin() {
		echo "Not logged in";
    echo "<br><br>";
    echo "<a href='ext_auth_gmail.php'>Sign in to GMail</a>";
	}

?>