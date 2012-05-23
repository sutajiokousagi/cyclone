<html>
<head>
	<title>Cyclone test facility</title>
	<link rel="stylesheet" href="./css/cyclone.css" type="text/css">
	<script type='text/javascript' src='./js/jquery-1.6.4.min.js'></script>
	<script type='text/javascript' src='./js/cyclone.js' charset="utf-8"></script>
</head>
<body onLoad="fOnBodyLoad();">

<?php
/*
- Created by Torin
- Test script to run through the entire system flow
*/

	require_once("util_users.php");
	require_once("util_modules.php");
	require_once("util_triggers.php");
	require_once("util_filters.php");
	
	//Global variables
	$user_id = 0;
	$modules_array = array();
	$triggers_array = array();
	$filters_array = array();

	//Fake authentication data for testing
	$input_user_name = "ACD49441-44BA-6D95-84B4-E6E3AF40CC84";
	$input_user_password = "";
	$md5_user_password = ($input_user_password == "") ? "" : md5($input_user_password);
	echo "Test data:<br/>\n";
	echo "user_name: $input_user_name (strlen=" . strlen($input_user_name) . ")<br/>\n";
	echo "user_password: $input_user_password <br/>\n";
	echo "md5_user_password: $md5_user_password <br/>\n";
	echo "<br/>";
		
	//-----------------------------------------------------------
	// Obtain a 'user_id' to be used throughout the session
	
	echo "Authorizing user...<br/>\n";
	$user_id = func_authorizeUser($input_user_name, $md5_user_password);
	echo "Got user_id: $user_id<br/>\n";
	if ($user_id <= 0) {
		echo "Authorization failed. Halt.<br/>\n";
		die();
	}
	echo "Authorization success. Continue...<br/>\n";
	echo "<br/>";
	
	//-----------------------------------------------------------
	// Display a list of modules
	
	$sql_result_modules = func_getAllModules();
	echo "Creating a new rule with trigger \n";
	echo "<div id='trigger_selector_wrapper'>\n";
	echo "<form action=''>\n";
	echo "	<select name='module' id='trigger_selector'>\n";
	while ($one_record = mysql_fetch_assoc($sql_result_modules))
	{
		$module_id = $one_record['module_id'];
		$module_name = $one_record['module_name'];
		$module_type = $one_record['module_type'];
		$module_description = $one_record['module_description'];
		
		$module_type_string = "";
		if (intval($module_type) == 0)			$module_type_string = "Standalone";
		else if (intval($module_type) == 1)		$module_type_string = "Hardware";
		else if (intval($module_type) == 2)		$module_type_string = "Software API";
		if ($module_name == "Twitter")			echo "		<option value='$module_id' selected='selected'>$module_name - $module_type_string</option>\n";
		else									echo "		<option value='$module_id'>$module_name - $module_type_string</option>\n";
		$modules_array[] = $one_record;
	}
	echo "	</select>\n";
	echo "</form>\n";
	echo "</div>\n";
	
	//-----------------------------------------------------------
	// For each module, display a list of possible trigger/triggers
	
	echo "with trigger \n";
	for ($module_idx=0; $module_idx<count($modules_array); $module_idx++)
	{
		$one_module = $modules_array[$module_idx];
		$module_id = $one_module['module_id'];
		$sql_result_triggers = func_getTriggersByModuleID( $module_id );
		
		echo "<div name='trigger_selector_wrapper' id='trigger_selector_wrapper_$module_id' style='display:none;'>\n";
		if ($sql_result_triggers == null)
		{
			echo "No triggers for this module. We're working hard on it...<br/>";
		}
		else
		{
			$num_triggers = mysql_num_rows($sql_result_triggers);
			
			echo "<form action=''>\n";
			echo "	<select name='trigger' id='trigger_selector'>\n";
							
			while ($one_record = mysql_fetch_assoc($sql_result_triggers))
			{
				$trigger_id = $one_record['trigger_id'];
				$trigger_name = func_getTriggerNameByID( $trigger_id );						//this is extremely ineffecient, for testing API only
				$trigger_description = func_getTriggerDescriptionByID( $trigger_id );		//this is extremely ineffecient, for testing API only
				echo "		<option value='$trigger_id'>$trigger_name - $trigger_description</option>\n";
				$triggers_array[] = $one_record;
			}
			
			echo "	</select>\n";
			echo "</form>\n";
		}
		echo "</div>\n";
	}
	
	//-----------------------------------------------------------
	// Place holder for filters
	
	echo "while new value \n";
	
	$sql_result_filters = func_getAllFilters();
	echo "<form action=''>\n";
	echo "	<select name='filter' id='filter_selector'>\n";
	while ($one_record = mysql_fetch_assoc($sql_result_filters))
	{
		$filter_id = $one_record['filter_id'];
		$filter_name = func_getFilterNameByID( $filter_id );					//this is extremely ineffecient, for testing API only
		$filter_type = func_getFilterTypeByID( $filter_id );					//this is extremely ineffecient, for testing API only
		$filter_description = func_getModuleDescriptionByID( $filter_id );		//this is extremely ineffecient, for testing API only
		
		$filter_type_string = "";
		if (intval($filter_type) == 0)			$filter_type_string = "String";
		else if (intval($filter_type) == 1)		$filter_type_string = "Number";
		else if (intval($filter_type) == 2)		$filter_type_string = "Date/Time";
		echo "		<option value='$filter_id'>$filter_name - $filter_type_string</option>\n";
		$filters_array[] = $one_record;
	}
	echo "	</select>\n";
	echo "	<input type='text' name='filter_param' id='filter_param_1' style='display:none;'>";
	echo "	<input type='text' name='filter_param' id='filter_param_2' style='display:none;'>";
	echo "</form>\n";
	echo "</div>\n";
	
	//-----------------------------------------------------------
	// Action
	
	echo "then perform action \n";
	
?>

</body>
</html>