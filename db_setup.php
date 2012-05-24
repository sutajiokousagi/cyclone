<?php
/*
- Created by Torin
- Force reload database from cyclone.sql
*/

	require_once("db_config.inc");
	require_once("util_output.php");
	require_once("util_database.php");
	
	//Try connect as normal user, if fail, perform first time setup sequence
	$firstTime = false;
	$link = @mysql_connect($DATABASE_HOST,$DATABASE_USER,$DATABASE_PASS);
	if (!$link)
	{
		//Changing root password must be done through console
		echo "Performing 1st time setup <br/>\n";
		$firstTime = true;

		//Connect to mysql
		$link = @mysql_connect($DATABASE_HOST,"root",$DATABASE_PASS);
		if (!$link) {
			$link = @mysql_connect($DATABASE_HOST,"root");
			mysql_select_db("mysql", $link);
			mysql_query("UPDATE user SET password=PASSWORD('" . $DATABASE_PASS . "') WHERE user='root' ", $link);
			mysql_query("FLUSH PRIVILEGES", $link);
			
			$link = @mysql_connect($DATABASE_HOST,"root",$DATABASE_PASS);
			if (!$link) {			
				passthru("ps ax | grep sql");
				func_dieWithMessage("Cannot connect to mysql. Check mysql is running, check mysql root password");
			}
			echo "Password for root mysql user is set <br/>\n";
		}
		else
		{
			echo "Password for root mysql user is already set <br/>\n";
		}

		//Try select database, if fails, create it
		$selectDatabaseOK = mysql_select_db($DATABASE_NAME, $link);
		if (!$selectDatabaseOK)
		{
			$createDatabaseOK = mysql_query("CREATE DATABASE " . $DATABASE_NAME, $link);
			if (!$createDatabaseOK)
				func_dieWithMessage("Cannot create database");
			echo "Database '" . $DATABASE_NAME . "' created <br/>\n";
		}
		else
		{
			echo "Database '" . $DATABASE_NAME . "' already exists <br/>\n";
		}
	
		//Try select again, then fail
		$selectDatabaseOK = mysql_select_db($DATABASE_NAME, $link);
		if (!$selectDatabaseOK)
			func_dieWithMessage("Cannot select database '" . $DATABASE_NAME . "'");

		//Check user exists
		$result = mysql_query("SELECT EXISTS(SELECT 1 FROM mysql.user WHERE user = '" . $DATABASE_USER . "')", $link);
		$userExist = false;
		if (!$result) {
			$one_record = mysql_fetch_assoc($result);
			if ($one_record != null && $one_record != NULL && $one_record != "null" && $one_record != "NULL")
				$userExist = true;
		}

		//Try create 1 user			
		if (!$userExist)
		{
			$createUserOK = mysql_query("CREATE USER " . $DATABASE_USER . "@'localhost'  IDENTIFIED BY '" . $DATABASE_PASS . "' ", $link);
			if (!$createUserOK)
				echo "Warning: failed to create user '" . $DATABASE_USER . "' <br/>\n";
			else
				echo "User " . $DATABASE_USER . " created <br/>\n";
				
			$grantUserOK = mysql_query("GRANT ALL PRIVILEGES ON " . $DATABASE_NAME . ".* TO " . $DATABASE_USER . "@'localhost' ", $link);
			if (!$grantUserOK)
				echo "Warning: failed to grant privileges for user '" . $DATABASE_USER . "' on database '" . $DATABASE_NAME . "' <br/>\n";
			else
				echo "Privileges for user " . $DATABASE_USER . " granted <br/>\n";
			mysql_query("FLUSH PRIVILEGES", $link);
		}
		else
		{
			echo "User '" . $DATABASE_USER . "' already exists <br/>\n";
		}
				
		//Disconnnect with root user
		mysql_close($link);
		$link = null;
		
		//Import data
		passthru("nohup mysql -uroot -p" . $DATABASE_PASS . " " . $DATABASE_NAME . " < cyclone.sql");
		echo "Database reloaded <br/>\n<br/>\n";
	}
	
	if (!$firstTime)
		echo("Not first time setup. <br/>\n");
	
	//Reconnect using new user
	if (!$link)	{
		$link = mysql_connect($DATABASE_HOST,$DATABASE_USER,$DATABASE_PASS);
		if (!$link)
			func_dieWithMessage("Cannot connect to mysql with user '" . $DATABASE_USER . "'");
	}
	
	$selectDatabaseOK = mysql_select_db($DATABASE_NAME, $link);
	if (!$selectDatabaseOK)
		func_dieWithMessage("Cannot select database " . $DATABASE_NAME . " with user '" . $DATABASE_USER . "'");
	
	//List the tables
	$result = mysql_query("SHOW TABLES", $link);
	if(!$result) {
		echo "Warning: database contains no table <br/>\n";
	}
	else {
		echo("Test query with user '" . $DATABASE_USER . "'. Table list: <br/>\n");
		while ($one_record = mysql_fetch_assoc($result))
			echo( " - " . $one_record["Tables_in_" . $DATABASE_NAME] . " <br/>\n");
	}
	
	mysql_close($link);
	
 ?>
