<?php

	set_include_path('modules' . PATH_SEPARATOR . get_include_path());
	
	require_once ("facebook_sdk/facebook.php");
	require_once ("util_preferences.php");
	require_once ("util_configs.php");
	
	session_start();

	//Hardcoded user or returning user (from logout or callback)
	$cyclone_user_id = 1;
	if (isset($_SESSION['cyclone_user_id']))
		$cyclone_user_id = $_SESSION['cyclone_user_id'];
	$_SESSION['cyclone_user_id'] = $cyclone_user_id;
	
	//Facebook SDK client object
	$facebook = new Facebook(array(
		'appId'  => func_getSystemPreference('system_ext_appid_facebook'),
		'secret' => func_getSystemPreference('system_ext_appsecret_facebook'),
	));

	// Get User ID
	$user = $facebook->getUser();
	
	if ($user) {
	  try {
	    // Proceed knowing you have a logged in user who's authenticated.
	    $user_profile = $facebook->api('/me');
	  } catch (FacebookApiException $e) {
	    error_log($e);
	    $user = null;
	  }
	}
	

	
	if ($user) {
	    echo "Logged in";
	    echo "<br><br>";
	    echo "<a href='ext_logout_facebook.php?cyclone_user_id=$cyclone_user_id'>Logout</a>";
	} else {
	    echo "Not logged in";
	    echo "<br><br>";
	    echo "<a href='ext_auth_facebook.php?cyclone_user_id=$cyclone_user_id'>Sign in to Facebook</a>";
	}

?>