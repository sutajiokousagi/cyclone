<?php

	session_start();
	$cyclone_user_id = 1;

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