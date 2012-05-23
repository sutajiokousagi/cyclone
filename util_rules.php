<?php
/*
- Created by Torin
- Helper functions to retrieve values in Rules table
*/

	require_once("db_config.php");

	function func_getRuleByID($id)
	{
		$condition = "rule_id = '" . $id . "'";
		return func_getSingleRowFromTable("rules", $condition);
	}
	
	/*
	 * Returns all rules can be generated by a given trigger (trigger_id is 1-to-many with module_id), returns raw SQL result
	 */
	function func_getRulesByTriggerID($id)
	{
		$condition = "trigger_id = '" . $id . "' AND rule_enabled = 1";
		return func_getMultiRowFromTable("rules", $condition);
	}
	
	/*
	 * Returns all rules created by a given user, returns raw SQL result
	 */
	function func_getAllRulesByUserID($id)
	{
		$condition = "user_id = '" . $id . "'";
		return func_getMultiRowFromTable("rules", $condition);
	}
	
	/*
	 * Returns all rules created by a given user, returns raw SQL result
	 */
	function func_getRulesByUserID($id)
	{
		$condition = "user_id = '" . $id . "' AND rule_enabled = 1";
		return func_getMultiRowFromTable("rules", $condition);
	}
		
	/*
	 * Returns all rules created by a given user and matching given triggers list, returns raw SQL result
	 */
	function func_getRulesByUserIDAndTriggerIDs($user_id, $trigger_id_array)
	{
		$sql = "SELECT * FROM rules ";
		$sql .= " LEFT JOIN actions on rules.action_id = actions.action_id ";
		$sql .= " WHERE user_id = '" . $user_id . "' AND trigger_id IN (" . join(",", $trigger_id_array) . ") ";
		$sql .= " AND rule_enabled = 1";
		return func_getMultiRowResultWithSQL($sql);
	}
	
	/*
	 * Returns all rules that needs to be applied with a given filter, returns raw SQL result
	 */
	function func_getRulesByFilterID($id)
	{
		$condition = "filter_id = '" . $id . "'";
		return func_getMultiRowFromTable("rules", $condition);
	}
	
	/*
	 * Returns all rules that needs to be applied with a given action, returns raw SQL result
	 */
	function func_getRulesByActionID($id)
	{
		$condition = "action_id = '" . $id . "'";
		return func_getMultiRowFromTable("rules", $condition);
	}
	
	//---------------------------------------------------------------------
	// ADD
	//---------------------------------------------------------------------
	
	/*
	 * Add a new rule
	 * $trigger_id: specific pre-defined trigger in the system (unique to a $module_id)
	 * $user_id: mandatory
	 * $filter_id: specific pre-defined filter in the system (unique to a $module_id)
	 * $filter_param: optional string parameter of the new trigger, eg. threshold, range, matching string, etc.
	 * $action_id: specific pre-defined action in the system (unique to a $module_id)
	 * $action_param: optional string parameter of the new trigger, eg. twit string, email body, motor value, etc.
	 * $rule_description: optional user-defined description of this rule
	 */
	function func_addRule($user_id, $trigger_id, $trigger_param, $filter_id, $filter_param, $action_id, $action_param, $rule_description)
	{	
		$table_name = "rules";
		$id_field_name = "rule_id";
		$input_names_array = array("user_id", "trigger_id", "trigger_param", "filter_id", "filter_param", "action_id", "action_param", "rule_description");
		$input_values_array = array($user_id, $trigger_id, $trigger_param, $filter_id, $filter_param, $action_id, $action_param, $rule_description);
		$datetime_field_name = 'last_updated';
		
		$entry_ID = func_insertSingleRecordReturnID($table_name, $id_field_name, $input_names_array, $input_values_array, $datetime_field_name);
		return $entry_ID;
	}
	
?>