<!DOCTYPE html>
<html lang="en">
<head>
	<title>Cyclone - Rules</title>
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link type="text/css" rel="stylesheet" href="./bootstrap/css/bootstrap.min.css">
	<link type="text/css" rel="stylesheet" href="./css/cyclone_dark.css">
	<script type='text/javascript' src='./js/jquery.min.js'></script>
	<script type='text/javascript' src='./js/jquery.json.min.js'></script>
	<script type='text/javascript' src='./bootstrap/js/bootstrap.min.js'></script>
	<script type='text/javascript' src='./bootstrap/js/bootstrap-button.js'></script>
	<script type='text/javascript' src='./bootstrap/js/bootstrap-tooltip.js'></script>
	<script type='text/javascript' src='./js/util_general.js'></script>
	<script type='text/javascript' src='./js/rules.js' charset="utf-8"></script>
</head>
<body onLoad="fOnBodyLoad();">

<?php
/*
- Created by Torin
- Script to display user-friendly rules
*/

	require_once("util_users.php");
	require_once("util_modules.php");
	require_once("util_rules.php");
	require_once("util_preferences.php");
	require_once("ui_rules_helper.php");
		
	//Global variables
	$user_id = 0;
	$modules_array = array();
	
	//Fake authentication data for testing
	$input_user_name = "ACD49441-44BA-6D95-84B4-E6E3AF40CC84";
	$input_user_password = "";
	$md5_user_password = ($input_user_password == "") ? "" : md5($input_user_password);
	echo "  User: $input_user_name<br/><br/>\n\n";
	
	//-----------------------------------------------------------
	// Obtain a 'user_id' to be used throughout the session
	
	$user_id = func_authorizeUser($input_user_name, $md5_user_password);
	if ($user_id <= 0) {
		echo "  Authorization failed. Halt.<br/>\n";
		die();
	}
	
	//Testing user preference
	$refresh_interval = func_getPreference($user_id ,"refresh_interval");
	echo "  Testing key-value pair: refresh_interval = $refresh_interval<br/><br/>\n\n";
	
	//-----------------------------------------------------------
	// Retrieve user's rules
	
	$sql_result_rules = func_getAllRulesByUserID($user_id);
	while ($sql_result_rules != null && $one_record = mysql_fetch_assoc($sql_result_rules))
	{
		$rule_id = $one_record['rule_id'];
		$rule_array[$rule_id] = $one_record;
	}
	$sql_result_rules = null;
	$num_rules = count($rule_array);
	
	echo "  <!------------------------->\n";
	echo "  <!-- User's rule listing -->\n";
	echo "  <!------------------------->\n";
	if ($num_rules <= 0) {
		echo "  <strong>You have have no rule to process. Add one now!</strong><br/>\n";
	}
	else 
	{ 
		echo "  <img src='images/list.png' id='btn_show_rules' class='img_button'>You have <strong>$num_rules</strong> rules.<br/>\n";
		echo "  <div id='panel_all_rules' style='display:none;'>\n";
		foreach ($rule_array as $rule_id => $one_rule)
			display_one_rule($one_rule);
		echo "  </div><br/>\n\n";
	}
	
	//-----------------------------------------------------------
	// Add rule

	echo "  <!------------------------>\n";
	echo "  <!-- Add new rule panel -->\n";
	echo "  <!------------------------>\n";
	echo "  <img src='images/add.png' id='btn_add_rule' class='img_button'>Add a new rule<br/>\n";
	echo "  <div id='panel_add_rule' style='display:none;'>\n";
	echo "  <form id='add_rule_form' action=''>\n";
	echo "    <input type='hidden' name='user_id' id='user_id' value='$user_id'>\n\n";

	//-----------------------------------------------------------
	// Trigger modules listing
	
	$sql_result_modules = null;
	$sql_result_modules = func_getTriggerModules();
	echo "      WHEN \n";
	echo "      <div class='styled-select'>\n";
	echo "      <select name='trigger_module_id' id='trigger_module_id'>\n";
	while ($one_record = mysql_fetch_assoc($sql_result_modules))
	{
		$module_id = $one_record['module_id'];
		$module_name = $one_record['module_name'];
		$module_type = $one_record['module_type'];
		$module_description = $one_record['module_description'];

		echo "        <option value='$module_id'>$module_name</option>\n";
		$modules_array[] = $one_record;
	}
	echo "      </select>\n";
	echo "      </div>\n\n";
		
	// Triggers listing
	echo "    <div id='trigger_selector_wrapper' style='display:none;'>\n";
	echo "      <div class='styled-select'>\n";
	echo "      <select name='trigger_id' id='trigger_id'>\n";
	// AJAX load & inject a list of <option> tags to be done by JavaScript
	echo "        <!-- triggers will be AJAX-loaded here -->\n";
	echo "      </select>\n";
	echo "      </div>\n";
	echo "    </div>\n\n";
	
	// Trigger parameter
	echo "    <div id='trigger_parameter_wrapper' style='display:none;'>\n";
	echo "      <div class='styled-select'>\n";
	echo "        <input type='text' name='trigger_param' id='trigger_param' class='styled-text-input'>\n";
	echo "      </div>\n";
	echo "    </div>\n\n";
	
	//-----------------------------------------------------------
	// Filter modules listing
	
	$sql_result_modules = null;
	$sql_result_modules = func_getFilterModules();
	echo "    <br/>WHILE \n";
	echo "    <div id='filter_module_selector_wrapper' style='display:none;'>\n";
	echo "      <div class='styled-select'>\n";
	echo "      <select name='filter_module_id' id='filter_module_id'>\n";
	echo "        <option value='0'>No filter</option>\n";
	while ($one_record = mysql_fetch_assoc($sql_result_modules))
	{
		$module_id = $one_record['module_id'];
		$module_name = $one_record['module_name'];
		$module_type = $one_record['module_type'];
		$module_description = $one_record['module_description'];

		echo "        <option value='$module_id'>$module_name</option>\n";
		$modules_array[] = $one_record;
	}
	echo "        <!-- filters will be AJAX-loaded here -->\n";
	echo "      </select>\n";
	echo "      </div>\n";
	echo "    </div>\n\n";
	
	// Filters listing
	echo "    <div id='filter_selector_wrapper' style='display:none;'>\n";
	echo "      <div class='styled-select'>\n";
	echo "      <select name='filter_id' id='filter_id'>\n";
	// AJAX load & inject a list of <option> tags to be done by JavaScript
	echo "        <!-- triggers will be AJAX-loaded here -->\n";
	echo "      </select>\n";
	echo "      </div>\n";
	echo "    </div>\n\n";
	
	// Filters parameter
	echo "    <div id='filter_parameter_wrapper' style='display:none;'>\n";
	echo "      <div class='styled-select'>\n";
	echo "        <input type='text' name='filter_param' id='filter_param' class='styled-text-input'>\n";
	echo "      </div>\n";
	echo "    </div>\n\n";
	
	//-----------------------------------------------------------
	// Action modules listing
	
	$sql_result_modules = null;
	$sql_result_modules = func_getActionModules();
	echo "    <br/>THEN \n";
	echo "    <div id='action_module_selector_wrapper' style='display:none;'>\n";
	echo "      <div class='styled-select'>\n";
	echo "      <select name='action_module_id' id='action_module_id'>\n";
	while ($one_record = mysql_fetch_assoc($sql_result_modules))
	{
		$module_id = $one_record['module_id'];
		$module_name = $one_record['module_name'];
		$module_type = $one_record['module_type'];
		$module_description = $one_record['module_description'];

		echo "        <option value='$module_id'>$module_name</option>\n";
		$modules_array[] = $one_record;
	}
	echo "        <!-- filters will be AJAX-loaded here -->\n";
	echo "      </select>\n";
	echo "      </div>\n";
	echo "    </div>\n\n";

	// Actions listing
	echo "    <div id='action_selector_wrapper' style='display:none;'>\n";
	echo "      <div class='styled-select'>\n";
	echo "      <select name='action_id' id='action_id'>\n";
	// AJAX load & inject a list of <option> tags to be done by JavaScript
	echo "        <!-- filters will be AJAX-loaded here -->\n";
	echo "      </select>\n";
	echo "      </div>\n";
	echo "    </div>\n\n";
	
	// Actions parameter
	echo "    <div id='action_parameter_wrapper' style='display:none;'>\n";
	echo "      <input type='text' name='action_param' id='action_param' class='styled-text-input'>\n";
	echo "    </div>\n\n";
	
	
	// Submit button
	echo "    <div id='add_rule_submit_wrapper' style='display:none;'>\n";
	echo "      <div>\n";
	echo "        <img src='images/tick.png' id='btn_rule_submit' class='img_button'>ADD<br/>\n";
	echo "      </div>\n";
	echo "    </div>\n\n";
	
	echo "  </form>\n";
	echo "  </div><br/>\n\n";
	
?>
</body>
</html>
