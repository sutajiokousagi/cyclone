<?

/*
- Created by Torin
*/
	
	require_once("util_triggers.php");
	
	//--------------------------------------------------------
	
	// Generic single value input
	$key_id_names_array = array('module_id', 'module_alias');
	
	$input_value = null;
	$input_name = null;
	foreach ($key_id_names_array as $key_name)
	{
		$input_value = $_POST[$key_name];
		if ($input_value == "")
			$input_value = $_GET[$key_name];
			
		if ($input_value == "")
			continue;
		$input_name = $key_name;
		break;
	}
	
	if ($input_value == null || $input_name == null)
		func_dieWithMessage("input parameter is required");
		
	//--------------------------------------------------------
	
	if ($input_name == 'module_id')
		$sql_result = func_getTriggersByModuleID( $input_value );

	else if ($input_name == 'module_alias')
		$sql_result = func_getTriggersByModuleAlias( $input_value );

	func_outputSqlArrayResult($sql_result);	

?>