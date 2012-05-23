<html>
<head>
	<title>Cyclone - Cron job</title>
	<style>
		body {
			background-color: #222222;
			color: #00f000;
			font-family: Monospace;
			font-size: 12px;
		}
		strong {
			font-weight: bolder;
			color: #ffffff;
		}
		.load_module {
			color: #888888;
		}
		.warning {
			color: yellow;
		}
		.error {
			color: #f00000;
		}
		.rule_status {
			color: #888888;
		}
		.rule_trigger {
			color: #0D58A6;
		}
		.rule_filter {
			color: #4188D2;
		}
		.rule_action {
			color: #689CD2;
		}
	</style>
</head>
<body>

<?php
/*
- Created by Torin
- This script is to be executed whenever there is a async trigger
- Any text output from this script is completely optional for debugging purpose only

- How it works:
- This script goes through all async events
- The module code supposes to perform their magic and push new events to (global) events table
*/

	//phase 1
	require_once("util_users.php");
	require_once("util_modules.php");
	require_once("util_rules.php");
	require_once("util_configs.php");
	require_once("cron_helper.php");
	
	//Global variables
	$user_id = 0;
	$modules_array = array();
	$configs_array = array();
	
	//Fake authentication data for testing
	$input_user_name = "ACD49441-44BA-6D95-84B4-E6E3AF40CC84";
	$input_user_password = "";
	$md5_user_password = ($input_user_password == "") ? "" : md5($input_user_password);
	//echo "user_name: $input_user_name (strlen=" . strlen($input_user_name) . ")<br/>\n";
	
	//-----------------------------------------------------------
	// Obtain a 'user_id' to be used throughout the session
	
	$user_id = func_authorizeUser($input_user_name, $md5_user_password);
	/*
	echo "user_id: <strong>$user_id</strong><br/>\n";
	if ($user_id <= 0) {
		echo "<span class='error'>ERROR:</span>Authorization failed. Halt.<br/>\n";
		die();
	}
	echo "Authorization success. Continue...<br/>\n";
	echo "<br/>\n\n";
	*/
	
	//-----------------------------------------------------------
	// Retrieve configurations for all modules for this user
	// and hash them by module_id for convenient access later
	
	$sql_result_configs = func_getConfigsByUserID($user_id);
	while ($sql_result_configs != null && $one_record = mysql_fetch_assoc($sql_result_configs))
	{		
		$module_id = $one_record['module_id'];
		if (!isset($configs_array[$module_id]) || $configs_array[$module_id] == null)
			$configs_array[$module_id] = array();
		
		$configs_array[$module_id][] = $one_record;
	}
	$sql_result_configs = null;
	
	//-----------------------------------------------------------
	// Display a list of trigger modules, add metadata
	
	$sql_result_triggers = func_getTriggerModules();
	while ($one_record = mysql_fetch_assoc($sql_result_triggers))
	{
		$module_id = $one_record['module_id'];
		$module_name = $one_record['module_name'];
		$module_type = $one_record['module_type'];
		$module_description = $one_record['module_description'];
		
		$module_type_string = "";
		if (intval($module_type) == 0)			$module_type_string = "Standalone";
		else if (intval($module_type) == 1)		$module_type_string = "Hardware";
		else if (intval($module_type) == 2)		$module_type_string = "Software API";
				
		$modules_array[$module_id] = $one_record;
	}
	$sql_result_triggers = null;
	
	//-----------------------------------------------------------
	// Phase 2, retrieve all new events in the global async events queue & filter them
	//-----------------------------------------------------------
		
	require_once("util_events.php");
	require_once("util_rules.php");
	require_once("util_filters.php");
	require_once("util_actions.php");
	
	$event_array = array();
	$rule_array = array();
	$trigger_id_array = array();
	
	//constants
	$event_ignored_rule_status_status = 1;						//no rule to process this trigger
	$event_ignored_error_module_status = 2;						//module is not loaded to process this trigger
	$event_ignored_error_filter_module_status = 3;				//module is not loaded to filter this trigger
	$event_ignored_error_filter_module_no_filter_status = 4;	//module is loaded but no filter
	$event_ignored_error_action_module_status = 5;				//module is not loaded to take action for this trigger
	$event_ignored_error_action_error_status = 6;				//error occured while performing action
	$event_pending_action_status = 30;							//passed filters, waiting for action
	$event_filtered_status = 31;								//being blocked by filter, no action needed
	$event_done_action_status = 32;								//action has been taken
	
	//-----------------------------------------------------------
	// Retrieve new async events in queue for this user
	
	$sql_result_queue = func_getAsyncEventsByUserID($user_id);
	while ($sql_result_queue != null && $one_record = mysql_fetch_assoc($sql_result_queue))
	{		
		$event_id = $one_record['event_id'];
		$trigger_id = $one_record['trigger_id'];
		$event_param = $one_record['event_param'];
				
		$event_array[] = $one_record;
		$trigger_id_array[$trigger_id] = $trigger_id;
	}
	$sql_result_queue = null;
	
	$num_queue = count($event_array);
	if ($num_queue <= 0) {
		echo "<strong>No async events to process. Done!</strong><br/>\n";
		die();
	}
	
	echo "Number of events: <strong>$num_queue</strong><br/>\n";
	
	//-----------------------------------------------------------
	// Retrieve user's rules
	
	$sql_result_rules = func_getRulesByUserIDAndTriggerIDs($user_id, $trigger_id_array);
	while ($sql_result_rules != null && $one_record = mysql_fetch_assoc($sql_result_rules))
	{
		$rule_id = $one_record['rule_id'];
		$rule_array[$rule_id] = $one_record;
	}
	$sql_result_rules = null;
	
	$num_rules = count($rule_array);
	if ($num_rules <= 0) {
		echo "<strong>No rule to process. Done!</strong><br/>\n";
		
		//flag all events (of user_id) as ignored
		for ($event_index=0; $event_index < count($event_array); $event_index++)
			func_updateAsyncEventStatus($event_id, $event_ignored_rule_status_status);
		
		die();
	}
	 
	echo "Number of rules: <strong>$num_rules</strong><br/>\n";
	
	//-----------------------------------------------------------
	// Process the async events queue
	
	echo "    <ul>\n\n";
	for ($event_index=0; $event_index < count($event_array); $event_index++)
	{
		$one_queue = $event_array[$event_index];
		$event_id = $one_queue['event_id'];
		$trigger_id = $one_queue['trigger_id'];
		$trigger_rule_id = intval($one_queue['rule_id']);
		$event_param = $one_queue['event_param'];
		$event_status = $one_queue['event_status'];
		$last_updated = $one_queue['last_updated'];
		
		//indicate that this event is not a global event
		//and should only be processed by matching rule_id
		if ($trigger_rule_id == null || $trigger_rule_id == "null" || $trigger_rule_id <= 0)
			$trigger_rule_id = null;
		
		echo "        <li>\n";
		if ($trigger_rule_id == null)	echo "            <strong>event_id $event_id</strong> (global)<br/>\n";
		else							echo "            <strong>event_id $event_id</strong> (for rule $trigger_rule_id only)<br/>\n";
		echo "            <span class='rule_trigger'>\n";
		echo "                trigger_id $trigger_id - event_param $event_param <br/>\n";
		echo "            </span>\n";
				
		//Check if we have any rules set for this trigger
		$the_rule = null;
		foreach ($rule_array as $rule_id => $one_rule)
		{
			$rule_id = $one_rule['rule_id'];
			$rule_trigger_id = $one_rule['trigger_id'];
			if ($rule_trigger_id != $trigger_id)
				continue;
			$the_rule = $one_rule;
		}
		
		//No rules set for this trigger, flag status as 'ignored. no rules'
		if ($the_rule == null) {
			func_updateAsyncEventStatus($event_id, $event_ignored_rule_status_status);
			echo "            <span class='rule_status'>\n";
			echo "                no rule to process this event. ignored. <br/>\n";
			echo "            </span>\n";
			echo $pretty_end_string;
			continue;
		}
		
		//-----------------------------------------------------------
		// Process the rule
		
		//Again, for each rule, apply it if it matches the trigger_id & rule_id
		$the_rule = null;
		foreach ($rule_array as $rule_id => $one_rule)
		{
			$rule_id = $one_rule['rule_id'];
			$rule_trigger_id = $one_rule['trigger_id'];
			if ($rule_trigger_id != $trigger_id)
				continue;
			if ($trigger_rule_id != null && $trigger_rule_id != $rule_id)
				continue;
			$the_rule = $one_rule;
			echo "            <span class='rule_status'>\n";
			echo "                applying rule_id $rule_id... <br/>\n";
			echo "            </span>\n";

			//--------------------------
			//Filter section

			echo "            <span class='rule_filter'>\n";
			//Get the filter ID & filter param in this rule
			$filter_id = intval( $the_rule['filter_id'] );
			
			//No filter, just pass through
			$filter_passed = false;
			if ($filter_id <= 0) {
				$filter_passed = true;
				echo "                bypass filter<br/>\n";
			}
			$filter_param = $the_rule['filter_param'];
				
			//Has a filter, load filter module & execute it to find the result
			$sql_filter_module = null;
			$module_id = 0;
			$module_loaded = false;
			if ($filter_passed == false)
			{
				//get the module that handles this filter
				$sql_filter_module = func_getModuleByFilterID( $filter_id );

				//no module to handle this filter, flag status as 'ignored. no filter module'
				if ($sql_filter_module == null)
				{
					func_updateAsyncEventStatus($event_id, $event_ignored_error_filter_module_status);
					echo "            no module to filter this trigger. ignored. <br/>\n";
					echo "            </span>\n";
					echo $pretty_end_string;
					continue;
					//next rule
				}
				
				//Load the filter module
				$module_id = $sql_filter_module['module_id'];
				$module_already_loaded = func_isModuleLoaded($module_id);
				if ($module_already_loaded == false) {
					echo "            <span class='load_module'>\n";
					$module_loaded = load_module($sql_filter_module, true);
					echo "            </span>\n";
					if ($module_loaded == false) {
						func_updateAsyncEventStatus($event_id, $event_ignored_error_filter_module_status);
						echo "            no module to filter this trigger. ignored. <br/>\n";
						echo "            </span>\n";
						echo $pretty_end_string;
						continue;
						//next rule
					}
				}
				
				//Store the module object for reuse
				$one_module = $modules_array[$module_id];
				$module_object = $one_module['module_object'];
				
				//Print out number of filters for this module
				$number_of_filters = $module_object->getNumberOfFilters();
				if ($number_of_filters == null || $number_of_filters <= 0) {
					func_updateAsyncEventStatus($event_id, $event_ignored_error_filter_module_no_filter_status);
					echo "            filter module contains no filters. Skipping... <br/>\n";
					if ($module_already_loaded == false)
						echo "            <span class='warning'>WARNING:</span> Please create at least 1 filter for this module.<br/>\n";
					echo "            </span>\n";
					echo $pretty_end_string;
					continue;
					//next rule
				}
				//print once
				if ($module_already_loaded == false)
					echo "            filter module contains " . $number_of_filters . " possible filter(s)<br/>\n";
				
				//Get the result of filtering
				$filter_passed = $module_object->filter($event_param, $filter_id, $filter_param);
				echo "            filter result: " . ($filter_passed == true ? "passed" : "blocked") . "<br/>\n";
				if ($filter_passed == false) {
					func_updateAsyncEventStatus($event_id, $event_filtered_status);
					echo "            </span>\n";
					echo $pretty_end_string;
					continue;
					//next rule
				}
			} //end of filtering (if filter_id presents)

			echo "            </span>\n";
			
			//--------------------------
			// Action section
					
			//Get the action ID & action param in this rule
			$action_id = intval( $the_rule['action_id'] );
			
			//No action, just pass through
			$action_ok = false;
			if ($action_id <= 0)
				$action_ok = true;
			$action_param = $the_rule['action_param'];
				
			//Has an action, load action module & execute it to find the result
			$sql_action_module = null;
			$module_id = 0;
			$module_loaded = false;
			if ($action_ok == false)
			{
				//get the module that handles this action
				$sql_action_module = func_getModuleByActionID( $action_id );

				//no module to handle this action, flag status as 'ignored. no action module'
				if ($sql_action_module == null)
				{
					func_updateAsyncEventStatus($event_id, $event_ignored_error_action_module_status);
					echo "            no module to action this trigger. ignored. <br/>\n";
					echo $pretty_end_string;
					continue;
					//next rule
				}
				
				$module_id = $sql_action_module['module_id'];
				echo "            <span class='load_module'>\n";
				$module_loaded = load_module($sql_action_module, true);
			}
			
			//Load the action module
			if ($module_loaded == false) {
				//func_updateAsyncEventStatus($event_id, $event_ignored_error_action_module_status);
				echo "            <span class='warning'>WARNING:</span> no module to perform action. ignored. <br/>\n";
				echo "            </span>\n";
				echo $pretty_end_string;
				continue;
				//next rule
			}
			$one_module = $modules_array[$module_id];
			$module_object = $one_module['module_object'];
			
			//Print out number of actions for this module
			$number_of_actions = $module_object->getNumberOfActions();
			if ($number_of_actions == null || $number_of_actions <= 0) {
				echo "                module contains no actions. Skipping... <br/>\n";
				echo "                <span class='warning'>WARNING:</span> Please create at least 1 action for this module.<br/>\n";
				echo "            </span>\n";
				echo $pretty_end_string;
				continue;
			}
			echo "                module contains " . $number_of_actions . " possible action(s)<br/>\n";
			echo "            </span>\n";
			echo "            <span class='rule_action'>\n";
			
			//Get the result of action
			echo "                perform action_id $action_id with action params: $action_param<br/>\n";
			$action_result = $module_object->action($action_id, $event_param, $action_param);
			echo "                action result: " . ($action_result != null ? "ok" : "not-ok") . "<br/>\n";
			echo "            </span>\n";
				
			if ($action_result == null) {
				func_updateAsyncEventStatus($event_id, $event_ignored_error_action_error_status);
				continue;
				//next rule
			}
						
			//Action success, flag as done
			func_updateAsyncEventStatus($event_id, $event_done_action_status);
			echo $pretty_end_string;
					
		} //end of for-loop for each rule
			
	} //end of for-loop for event processing
	echo "    </ul>\n";
	
?>

</body>
</html>