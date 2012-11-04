<?php

	require_once("db_config.php");

	function func_getFullCommandLine($param)
	{
		return "/usr/sbin/kovan-demo.py " . $param;
	}

	function func_execKovanCommandWithParam($param)
	{
		//Construct the command line string
		$cmd_string = func_getFullCommandLine( $param );

		$output_array = array();
		$output = exec($cmd_string, $output_array);
		return $output_array;
	}
?>