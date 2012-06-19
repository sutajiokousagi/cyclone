<?php

	set_include_path('modules' . PATH_SEPARATOR . get_include_path());

	require_once ("Zend/Oauth/Consumer.php");
	require_once ("util_preferences.php");
	require_once ("util_configs.php");
	
	session_start();

	//Hardcoded user or returning user (from logout or callback)
	$cyclone_user_id = 1;
	if (isset($_SESSION['cyclone_user_id']))
		$cyclone_user_id = $_SESSION['cyclone_user_id'];
	$_SESSION['cyclone_user_id'] = $cyclone_user_id;

	if (isset($_SESSION['ext_auth_twitter'])) {
	    echo "Logged in";
	    echo "<br><br><pre>";
	    print_r($_SESSION['ext_auth_twitter']);
	    echo "</pre>";
	    echo "<a href='ext_logout_twitter.php?cyclone_user_id=$cyclone_user_id'>Logout</a>";
	} else {
	    echo "Not logged in";
	    echo "<br><br>";
	    echo "<a href='ext_auth_twitter.php?cyclone_user_id=$cyclone_user_id'>Sign in to Twitter</a>";
	}

?>