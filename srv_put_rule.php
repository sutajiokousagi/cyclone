<?php

/*
- Created by Torin
*/
	
	require_once("util_rules.php");
	
	$input_parameters = array('user_id','trigger_id','trigger_param','filter_id','filter_param','action_id','action_param');
		
	$error_input_parameters = array();
	foreach ($input_parameters as $input_name)
	{
		if ($input_name == 'trigger_param')			//optional, this should depends on the trigger_id actually
			continue;
		if ($input_name == 'filter_param')			//optional
			continue;
		$input_value = null;
		if (isset($_POST[$input_name]))
			$input_value = $_POST[$input_name];
		if (isset($_GET[$input_name]))
			$input_value = $_GET[$input_name];
		if ($input_value == null)
			$error_input_parameters[] = $input_name;
	}
	
	if (count($error_input_parameters) > 0)
		func_dieWithMessage( join(", ", $error_input_parameters) . " is/are required");
		
	//--------------------------------------------------------
	
	$user_id = $_POST['user_id'];
	$trigger_id = $_POST['trigger_id'];
	$trigger_param = null;
	if ( isset($_POST['trigger_param']) )
		$trigger_param = clean_param_json_string( $_POST['trigger_param'] );

	$filter_id = $_POST['filter_id'];
	$filter_param = null;
	if ( isset($_POST['filter_param']) )
		$trigger_param = clean_param_json_string( $_POST['filter_param'] );

	$action_id = $_POST['action_id'];
	$action_param = null;
	if ( isset($_POST['action_param']) )
		$action_param = clean_param_json_string( $_POST['action_param'] );
		
	$rule_description = "";
	
	$rule_id = func_addRule($user_id, $trigger_id, $trigger_param, $filter_id, $filter_param, $action_id, $action_param, $rule_description);
	
	if ($rule_id == null || $rule_id <= 0)
		func_dieWithMessage("error adding new rule");
		
	$output_array = array();
	$output_array['rule_id'] = $rule_id;
	func_outputArray($output_array);
	
	
	
	
	function clean_param_json_string($params_json)
	{
		return str_replace('\\"', '"', $params_json);
	}
?>