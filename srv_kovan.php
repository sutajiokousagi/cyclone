<?php

/*
- Created by Torin
*/
	
	require_once("util_kovan.php");
	
	/*
	$input_parameters = array('param');
		
	$error_input_parameters = array();
	foreach ($input_parameters as $input_name)
	{
		$input_value = null;
		if (isset($_POST[$input_name]))
			$input_value = $_POST[$input_name];
		if (isset($_GET[$input_name]))
			$input_value = $_GET[$input_name];
		if ($input_value == null)
			$error_input_parameters[] = $input_name;
	}
	
	if (count($error_input_parameters) > 0)
		func_dieWithMessage( join(", ", $error_input_parameters) . " is required");
	*/
		
	//--------------------------------------------------------
	
	$param = null;
	if ( isset($_POST['param']) )
		$param = $_POST['param'];
	if ( isset($_GET['param']) )
		$param = $_GET['param'];

	//Print out all instructions by default
	if ($param == null)
		$param = "--help";

	$output_array = func_execKovanCommandWithParam( $param );
	func_outputArray($output_array);

?>