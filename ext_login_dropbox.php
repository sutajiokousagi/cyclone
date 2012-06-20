<?php

	set_include_path('modules' . PATH_SEPARATOR . get_include_path());

	require_once ("util_preferences.php");
	require_once ("util_modules.php");
	require_once ("util_configs.php");
	
	require_once ("dropbox/autoload.php");
	
	session_start();
	
	//Hardcoded user or returning user (from logout or callback)
	$cyclone_user_id = 1;
	if (isset($_SESSION['cyclone_user_id']))
		$cyclone_user_id = $_SESSION['cyclone_user_id'];
	$_SESSION['cyclone_user_id'] = $cyclone_user_id;

	//Retrieve the secrets from database
	$oauth_token = null;
	$module_id = func_getModuleIDByAlias('dropbox');
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
		
	//Dropbox is different, storing everything
	$oauth_tokens_array = json_decode($oauth_token, true);
	
	//Set consumer key, secret and callback URL
	$consumerKey = func_getSystemPreference('system_ext_appkey_dropbox');
	$consumerSecret = func_getSystemPreference('system_ext_appsecret_dropbox');
	$oauth = new Dropbox_OAuth_Zend($consumerKey, $consumerSecret);
	$oauth->setToken($oauth_tokens_array);
	
	//Dropbox API client object
	$dropbox = new Dropbox_API($oauth);
			
	//Make a test API call
	$user = null;
  try {
    // Proceed knowing you have a logged in user who's authenticated.
		$user = $dropbox->getAccountInfo();
		$valid = isset($user['uid']);
  } catch (Exception $e) {
    error_log($e);
		$user = null;
  }
		
	//Valid response
	if ($user) {
		showPrettyUser($user);
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
    echo "<br><br>";
    echo "<a href='ext_logout_dropbox.php'>Logout</a>";
	}
	
	function displayLogin() {
		echo "Not logged in";
    echo "<br><br>";
    echo "<a href='ext_auth_dropbox.php'>Sign in to Dropbox</a>";
	}

 	function showPrettyUser($userInfoArray) {
	
		$name = $userInfoArray['display_name'];
		$quota = $userInfoArray['quota_info']['quota'] / 1024 / 1024 / 1024;
		$shared = $userInfoArray['quota_info']['shared'] / 1024 / 1024 / 1024;
		$normal = $userInfoArray['quota_info']['normal'] / 1024 / 1024 / 1024;
		$percent = ($normal + $shared) / $quota * 100;
		
		$percent = round($percent, 1);
		$quota = round($quota, 1);
		
		echo "Logged in as <strong>" . $name . "</strong><br/>";
		echo "<strong>" . $percent . "%</strong> of <strong>" . $quota . "GB</strong> used";
	}
	
?>