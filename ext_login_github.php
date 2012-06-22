<?php

	set_include_path('modules' . PATH_SEPARATOR . get_include_path());

	require_once ("github/github.php");
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
	$githubApi = new GitHubApi();
	$githubApi->setAcessToken($oauth_token);
  $user = $githubApi->getUser();
		
	//Valid response
	if ($user) {
		displayLogout();
		showPrettyUser($user);
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
    echo "<a href='ext_logout_github.php'>Logout</a>";
    echo "<br><br>";
	}
	
	function displayLogin() {
		echo "Not logged in";
    echo "<br><br>";
    echo "<a href='ext_auth_github.php'>Sign in to Github</a>";
	}

	//--------------

 	function showPrettyUser($userInfoObj) {
		$name = $userInfoObj->name;
		$avatar_url = $userInfoObj->avatar_url;
		echo "Logged in as <strong>" . $name . "</strong><br/>";
		echo "<img src='" . $avatar_url . "'><br/>";
		
		global $githubApi;
	  $repos = $githubApi->getUserRepos(null, null, null);
		//$repos = $githubApi->getOrganizationRepos('2359media', null);
	
		if ($repos != null) {
			foreach ($repos as $repo) {
				echo $repo->private ? "[Private] " : "[Public] ";
				echo $repo->fork ? "[Watching] " : "";
				echo $repo->name;
				echo "<br/>\n";
			}
		}
	}
	
?>