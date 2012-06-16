<?php

/*
- Created by Torin
*/
	
	require_once("util_rules.php");
	
	$input_parameters = array('user_id','rule_id', 'rule_enabled');

	//--------------------------------------------------------
	//Validation

	$error_input_parameters = array();
	foreach ($input_parameters as $input_name)
	{
		$input_value = null;
		if (isset($_POST[$input_name]))
			$input_value = $_POST[$input_name];
		if (isset($_GET[$input_name]))
			$input_value = $_GET[$input_name];
		if ($input_value === null)
			$error_input_parameters[] = $input_name;
	}
	
	if (count($error_input_parameters) > 0)
		func_dieWithMessage( join(", ", $error_input_parameters) . " is/are required");
		
	//--------------------------------------------------------
	// Variables

	$user_id = null;
	$rule_id = null;
	$rule_enabled = null;
	if (isset($_POST['user_id']))		$user_id = $_POST['user_id'];
	if (isset($_GET['user_id']))		$user_id = $_GET['user_id'];
	if (isset($_POST['rule_id']))		$rule_id = $_POST['rule_id'];
	if (isset($_GET['rule_id']))		$rule_id = $_GET['rule_id'];
	if (isset($_POST['rule_enabled']))	$rule_enabled = $_POST['rule_enabled'];
	if (isset($_GET['rule_enabled']))	$rule_enabled = $_GET['rule_enabled'];

	if (intval($rule_enabled) == 1)		$rule_enabled = true;
	else 								$rule_enabled = false;
	
	//--------------------------------------------------------
	
	$success = func_enableRule($user_id, $rule_id, $rule_enabled);
	
	if (!$success)
		func_dieWithMessage("error enabling rule");
	
	$output_array = array();
	$output_array['rule_id'] = $rule_id;
	$output_array['rule_enabled'] = $rule_enabled;
	func_outputArray($output_array);
	
?>