<?
/*
- Created by Torin
- Database configurations for Cyclone project
*/

global $DATABASE_HOST;
global $DATABASE_NAME;
global $DATABASE_USER;
global $DATABASE_PASS;
global $link;

$DATABASE_HOST = "localhost";
$DATABASE_NAME = "cyclone";
$DATABASE_USER = "cyclone";
$DATABASE_PASS = "cyclone";

require_once("util_output.php");
require_once("util_database.php");

$link = mysql_connect($DATABASE_HOST,$DATABASE_USER,$DATABASE_PASS) or func_dieWithMessage("Cannot connect to database");
mysql_select_db($DATABASE_NAME, $link) or func_dieWithMessage("Cannot select database");
mysql_set_charset('utf8',$link); 

function func_executeQueryOrDie($sqlQueryString)
{
	global $link;
	return mysql_query($sqlQueryString, $link) or func_dieWithMessage("Error in SQL syntax: $sqlQueryString");
}

?>