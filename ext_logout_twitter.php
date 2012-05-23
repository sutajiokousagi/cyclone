<?php

	require_once ("util_configs.php");
	
	session_start();
	
	if (!isset($_SESSION['ext_auth_twitter'])) {
	    echo "already logged out";
	    die();
	}
	
	$cyclone_user_id = 0;
	if (isset($_POST['cyclone_user_id']))		$cyclone_user_id = $_POST['cyclone_user_id'];
	if (isset($_GET['cyclone_user_id']))		$cyclone_user_id = $_GET['cyclone_user_id'];
	if (intval($cyclone_user_id <= 0))
		die("cyclone_user_id is invalid ($cyclone_user_id)");	
		
	//Clean database
	$module_id = 2;		//can be hardcoded
	func_deleteConfig($module_id, $cyclone_user_id, 'oauth_token');
	func_deleteConfig($module_id, $cyclone_user_id, 'oauth_token_secret');
	func_deleteConfig($module_id, $cyclone_user_id, 'user_id');
	func_deleteConfig($module_id, $cyclone_user_id, 'screen_name');

	//Clean session variable
	unset($_SESSION['ext_auth_twitter']);

	header('Location: ext_login_twitter.php');

?>