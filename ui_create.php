<!DOCTYPE html>
<html lang="en">
<head>
	<title>Cyclone - Rules</title>
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link type="text/css" rel="stylesheet" href="./bootstrap/css/bootstrap.min.css">
	<link type="text/css" rel="stylesheet" href="./css/cyclone_bright.css">
	<script type='text/javascript' src='./js/jquery.min.js'></script>
	<script type='text/javascript' src='./js/jquery.json.min.js'></script>
	<script type='text/javascript' src='./bootstrap/js/bootstrap.min.js'></script>
	<script type='text/javascript' src='./bootstrap/js/bootstrap-alert.js'></script>
	<script type='text/javascript' src='./bootstrap/js/bootstrap-button.js'></script>
	<script type='text/javascript' src='./bootstrap/js/bootstrap-collapse.js'></script>
	<script type='text/javascript' src='./bootstrap/js/bootstrap-dropdown.js'></script>
	<script type='text/javascript' src='./bootstrap/js/bootstrap-modal.js'></script>
	<script type='text/javascript' src='./bootstrap/js/bootstrap-tooltip.js'></script>
	<script type='text/javascript' src='./js/util_general.js'></script>
	<script type='text/javascript' src='./js/create.js' charset="utf-8"></script>
</head>

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
	
	//-----------------------------------------------------------
	// Obtain a 'user_id' to be used throughout the session
	
	$user_id = func_authorizeUser($input_user_name, $md5_user_password);
	if ($user_id <= 0) {
		echo "  User: $input_user_name<br/><br/>\n\n";
		echo "  Authorization failed. Halt.<br/>\n";
		die();
	}
?>

<body onLoad='fOnBodyLoad( <?php echo $user_id; ?> );'>

<?php
	
	echo "  User: $input_user_name<br/><br/>\n\n";
	
	//Testing user preference
	$refresh_interval = func_getPreference($user_id ,"refresh_interval");
	//echo "  Testing key-value pair: refresh_interval = $refresh_interval<br/><br/>\n\n";
	

	//-----------------------------------------------------------
	// Add rule

	echo "    <input type='hidden' name='user_id' id='user_id' value='$user_id'>\n";
	echo "    <input type='hidden' name='trigger_param' id='trigger_param'>\n";	
	echo "    <input type='hidden' name='action_param' id='action_param'>\n";	

	//-----------------------------------------------------------
	// Trigger modules listing
	
	$sql_result_modules = null;
	$sql_result_modules = func_getTriggerModules();
	echo "      WHEN<br/><br/>\n";
	echo "      <div class='row-fluid'>\n";
	while ($one_record = mysql_fetch_assoc($sql_result_modules))
	{
		$module_id = $one_record['module_id'];
		$module_name = $one_record['module_name'];
		$module_type = $one_record['module_type'];
		$module_alias = $one_record['module_alias'];
		$module_description = $one_record['module_description'];

		echo "        <div class='span2 trigger_module_button' alt='$module_alias' id='trigger_module_button_$module_id' onClick='fSelectTriggerModuleID($module_id);'>";
		echo "			<img src='images/module_$module_alias.png'><br/>\n";
		echo "			$module_name\n";
		echo "		  </div>\n";
		$modules_array[] = $one_record;
	}
	echo "      </div>\n\n";

	// Triggers listing
	// AJAX load & inject a list of <option> tags to be done by JavaScript
	echo "      <div class='row-fluid'>\n";
	echo "        <div id='trigger_selector_wrapper' style='display:none;'>\n";
	echo "        </div>\n\n";
	echo "      </div>\n\n";
	
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
	echo "    <div class='row-fluid'>\n";
	while ($one_record = mysql_fetch_assoc($sql_result_modules))
	{
		$module_id = $one_record['module_id'];
		$module_name = $one_record['module_name'];
		$module_alias = $one_record['module_alias'];
		$module_type = $one_record['module_type'];
		$module_description = $one_record['module_description'];

		echo "        <div class='span2 action_module_button' alt='$module_alias' id='action_module_button_$module_id' onClick='fSelectActionModuleID($module_id);'>";
		echo "			<img src='images/module_$module_alias.png'><br/>\n";
		echo "			$module_name\n";
		echo "		  </div>\n";

		$modules_array[] = $one_record;
	}
	echo "    </div>\n\n";

	// Actions listing
	// AJAX load & inject a list of <option> tags to be done by JavaScript
	echo "    <div class='row-fluid'>\n";
	echo "      <div id='action_selector_wrapper' style='display:none;'>\n";
	echo "      </div>\n\n";
	echo "    </div>\n\n";

	//-----------------------------------------------------------
	// Last but not least

	// Submit button
	echo "    <div id='add_rule_submit_wrapper' style='display:none;'>\n";
	echo "      <button class='btn btn-large btn-primary'>Save</button>\n";
	echo "    </div>\n\n";
		
?>
</body>
</html>
