<?php
/*
- Created by Torin
- Helper functions to retrieve values in Configs table
*/

	require_once("db_config.php");

	function func_getConfigByID($id)
	{
		$condition = "config_id = '" . $id . "'";
		return func_getSingleRowFromTable("configs", $condition);
	}
	
	/*
	 * Returns all configs for a given user, returns raw SQL result
	 */
	function func_getConfigsByUserID($id)
	{
		$condition = "user_id = '" . $id . "'";
		return func_getMultiRowFromTable("configs", $condition);
	}
	
	/*
	 * Returns all configs for a given module, returns raw SQL result
	 */
	function func_getConfigsByModuleID($id)
	{
		$condition = "module_id = '" . $id . "'";
		return func_getMultiRowFromTable("configs", $condition);
	}
	
	/*
	 * Returns all configs for a given module of a given user, returns raw SQL result
	 */
	function func_getConfigsByModuleIDAndUserID($module_id, $user_id)
	{
		$condition = "module_id = '" . $module_id . "' AND user_id = '" . $user_id . "'";
		return func_getMultiRowFromTable("configs", $condition);
	}
	
	
	function func_getConfigNameByID($id)
	{
		$condition = "config_id = '" . $id . "'";
		$one_record = func_getSingleRowFromTable("configs", $condition);
		if ($one_record == null)
			return null;
		return $one_record['config_name'];
	}
	
	function func_getConfigParamByID($id)
	{
		$condition = "config_id = '" . $id . "'";
		$one_record = func_getSingleRowFromTable("configs", $condition);
		if ($one_record == null)
			return null;
		return $one_record['config_param'];
	}
		
	//---------------------------------------------------------------------
	// ADD
	//---------------------------------------------------------------------
	
	/*
	 * Add a new config
	 * $module_id: mandatory
	 * $user_id: mandatory
	 * $config_name: a short name of this config eg. username, password, access_token, etc.
	 * $config_param: value for this config
	 */
	function func_setConfig($module_id, $user_id, $config_name, $config_value)
	{	
		$table_name = "configs";
		$id_field_name = "config_name";
		$id_field_value = $config_name;
		$input_names_array = array("module_id", "user_id", "config_name", "config_param");
		$input_values_array = array($module_id, $user_id, $config_name, $config_value);
		$datetime_field_name = 'last_updated';
		
		//check exists
		$condition = "$id_field_name = '$id_field_value' AND user_id = '$user_id' AND module_id = '$module_id' ";
		$one_record = func_getSingleRowFromTable($table_name, $condition);
		
		//already exists
		if ($one_record != null) {
			$num_affected = func_updateSingleRecordReturnNumAffected($table_name, $id_field_name, $id_field_value, $input_names_array, $input_values_array, $datetime_field_name);
			return ($num_affected > 0) ? true : false;
		}
		
		//not exists, insert new entry
		$one_record = func_insertSingleRecordReturnRecord($table_name, $input_names_array, $input_values_array, $datetime_field_name);
		return ($one_record != null) ? true : false;
	}
	
	//---------------------------------------------------------------------
	// DELETE
	//---------------------------------------------------------------------

	/*
	 * Delete a config
	 * $module_id: mandatory
	 * $user_id: mandatory
	 * $config_name: a short name of this config eg. username, password, access_token, etc.
	 * $config_param: value for this config
	 */
	function func_deleteConfig($module_id, $user_id, $config_name)
	{	
		$table_name = "configs";
		$input_names_array = array("module_id", "user_id", "config_name");
		$input_values_array = array($module_id, $user_id, $config_name);
		
		$entry_ID = func_deleteRecordsReturnNumAffected($table_name, $input_names_array, $input_values_array);
		return $entry_ID;
	}
	
?>