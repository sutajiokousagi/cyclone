<?php
/*
- Created by Torin
- Force reload database from cyclone.sql
*/

	require_once("db_config.inc");
	passthru("nohup mysql -u " . $DATABASE_USER . " -p" . $DATABASE_PASS . " " . $DATABASE_NAME . " < cyclone.sql");
	echo "Database reloaded <br/>\n";
	
 ?>
