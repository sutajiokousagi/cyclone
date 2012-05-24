<?
/*
- Created by Torin
- Database connection utility
*/

require_once("db_config.inc");
require_once("util_output.php");
require_once("util_database.php");

global $link;

$link = mysql_connect($DATABASE_HOST,$DATABASE_USER,$DATABASE_PASS) or func_dieWithMessage("Cannot connect to database");
mysql_select_db($DATABASE_NAME, $link) or func_dieWithMessage("Cannot select database");
mysql_set_charset('utf8',$link); 

function func_executeQueryOrDie($sqlQueryString)
{
	global $link;
	return mysql_query($sqlQueryString, $link) or func_dieWithMessage("Error in SQL syntax: $sqlQueryString");
}

?>