<?php

	set_include_path('modules' . PATH_SEPARATOR . get_include_path());
	
	require_once ("util_preferences.php");
	require_once ("util_configs.php");
	require_once ("util_modules.php");

	//Wrong format from Github
	if (!isset($_GET['code']))
	    die("code not set");
	
	session_start();
	
	$cyclone_user_id = 0;
	if (isset($_POST['cyclone_user_id']))			$cyclone_user_id = $_POST['cyclone_user_id'];
	if (isset($_GET['cyclone_user_id']))			$cyclone_user_id = $_GET['cyclone_user_id'];
	if (isset($_SESSION['cyclone_user_id']))	$cyclone_user_id = $_SESSION['cyclone_user_id'];
	if ($cyclone_user_id <= 0)
	    die("cyclone_user_id not set");
	
	//Get the access token
	$code = $_GET['code'];
	$accessToken = getAccessToken($code);
	
	if ($accessToken === null) {	
		//header('Location: ext_login_github.php');
		die();
	}

	//Save the secrets into our database
	$module_id = func_getModuleIDByAlias('github');
	func_setConfig($module_id, $cyclone_user_id, 'oauth_token', $accessToken);

	header('Location: ext_login_github.php');



	function getAccessToken($code)
	{
		$client_id = func_getSystemPreference('system_ext_clientid_github');
		$client_secret = func_getSystemPreference('system_ext_clientsecret_github');

		//Build the OAuth url
		$url = "https://github.com/login/oauth/access_token";
		$url .= "?client_id=" . $client_id;
		$url .= "&client_secret=" . $client_secret;
		$url .= "&code=" . $code;

		$ch = curl_init(); 
		curl_setopt($ch, CURLOPT_URL, $url); 
    curl_setopt($ch, CURLOPT_HEADER, FALSE); 
    curl_setopt($ch, CURLOPT_NOBODY, FALSE);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE); 
    $response = curl_exec($ch); 
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE); 
    curl_close($ch);

		$error = ($httpCode != 200) || (strpos($response, "error") !== false);
		if ($error) {
			var_dump($response);
			return null;
		}
		
		/* Example response
	  access_token=e72e16c7e42f292c6912e7710c838347ae178b4a&token_type=bearer
		*/

		$tempArray = explode("&token_type", $response);
		$access_token = $tempArray[0];

		$tempArray = explode("access_token=", $access_token);
		$access_token = $tempArray[1];

		return $access_token;
	}

?>