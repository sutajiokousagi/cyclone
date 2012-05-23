<?php
/*
- Created by Torin
- Helper functions to retrieve values in Queues table
*/

	require_once("db_config.php");
	
	/*
	 * Return a single value system-wide preference.
	 * This provides a simple key-value pair storage mechanism
	 */
	function func_getSystemPreference($preference_name)
	{
		$condition = "preference_name = '$preference_name'";
		$one_record = func_getSingleRowFromTable("preferences", $condition);
		if ($one_record == null)
			return null;
		return $one_record['preference_value'];
	}

	/*
	 * Return a single value user preference.
	 * This provides a simple key-value pair storage mechanism
	 */
	function func_getPreference($user_id, $preference_name)
	{
		$condition = "preference_name = '$preference_name' AND user_id = '$user_id' ";
		$one_record = func_getSingleRowFromTable("preferences", $condition);
		if ($one_record == null)
			return null;
		return $one_record['preference_value'];
	}
	
	/*
	 * Set a single value user preference.
	 * This provides a simple key-value pair storage mechanism
	 */
	function func_setPreference($user_id, $preference_name, $preference_value)
	{
		if ($preference_name == null || strlen($preference_name) < 2)
			return false;

		$table_name = "preferences";
		$id_field_name = "preference_name";
		$id_field_value = $preference_name;
		$input_names_array = array('preference_name', 'preference_value', 'user_id');
		$input_values_array = array($preference_name, $preference_value, $user_id);
		$datetime_field_name = 'last_updated';
		
		//check exists
		$condition = "$id_field_name = '$id_field_value' AND user_id = '$user_id' ";
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
	
?>