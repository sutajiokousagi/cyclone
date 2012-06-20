<?php

	set_include_path('modules' . PATH_SEPARATOR . get_include_path());

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
	$module_id = func_getModuleIDByAlias('github');
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
		
	//Make a test API call
	$user = null;
  try {
    // Proceed knowing you have a logged in user who's authenticated.
    $user = getUser($oauth_token);
		$user = json_decode($user, true);
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
    echo "<a href='ext_logout_github.php'>Logout</a>";
	}
	
	function displayLogin() {
		echo "Not logged in";
    echo "<br><br>";
    echo "<a href='ext_auth_github.php'>Sign in to Github</a>";
	}

	//--------------

 	function showPrettyUser($userInfoArray) {
		$name = $userInfoArray['name'];
		$avatar_url = $userInfoArray['avatar_url'];
		echo "Logged in as <strong>" . $name . "</strong><br/>";
		echo "<img src='" . $avatar_url . "'><br/>";
	}
	
	function getUser($oauth_token) {
		$url = "https://api.github.com/user?access_token=" . $oauth_token;
		
		$ch = curl_init(); 
		curl_setopt($ch, CURLOPT_URL, $url); 
    curl_setopt($ch, CURLOPT_HEADER, FALSE); 
    curl_setopt($ch, CURLOPT_NOBODY, FALSE);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE); 
    $response = curl_exec($ch); 
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE); 
    curl_close($ch);

		return $response;
	}
	
?>