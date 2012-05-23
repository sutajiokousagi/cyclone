<?php
/*
- Created by Torin
- Helper functions to retrieve values in Users table
*/

	require_once("db_config.php");

	function func_getUserByID($id)
	{
		$condition = "user_id = '" . $id . "'";
		return func_getSingleRowFromTable("users", $condition);
	}
	
	function func_getUserByName($name)
	{
		$condition = "user_name = '" . $name . "'";
		return func_getSingleRowFromTable("users", $condition);
	}
	
	function func_getUsersByEmail($email)
	{
		$condition = "user_email = '" . $email . "'";
		return func_getSingleRowFromTable("users", $condition);
	}
	
	function func_getUserNameByID($id)
	{
		$condition = "user_id = '" . $id . "'";
		$one_record = func_getSingleRowFromTable("users", $condition);
		if ($one_record == null)
			return null;
		return $one_record['user_name'];
	}
	
	function func_getUserEmailByID($id)
	{
		$condition = "user_id = '" . $id . "'";
		$one_record = func_getSingleRowFromTable("users", $condition);
		if ($one_record == null)
			return null;
		return $one_record['user_email'];
	}
			
	function func_getUserPasswordID($id)
	{
		$condition = "user_id = '" . $id . "'";
		$one_record = func_getSingleRowFromTable("users", $condition);
		if ($one_record == null)
			return null;
		return $one_record['user_password'];
	}

	/*
	 * Check user_name & MD5-ed password and returns user_id if both are correct
	 * Returns 0 if user doesn't exist
	 * Returns -1 if password doesn't match
	 */
	function func_authorizeUser($user_name, $user_password)
	{
		$condition = "user_name = '" . mysql_real_escape_string($user_name) . "'";		
		$one_record = func_getSingleRowFromTable("users", $condition);
		if ($one_record == null)
			return 0;
		if (strlen($user_name) == 36 && substr_count($user_name, "-") == 4)		//password doesn't matter
			return intval($one_record['user_id']);
		if ($one_record['user_password'] != $user_password)
			return -1;
		return intval($one_record['user_id']);
	}
	
	//---------------------------------------------------------------------
	// ADD
	//---------------------------------------------------------------------
	
	/*
	 * Add a new user
	 * $user_name: mandatory, possibly device ID
	 * $user_email: optional
	 * $user_password: optional if $user_name is device ID
	 * $user_memo: optional description of this user
	 */
	function func_addUser($user_name, $user_email, $user_password, $user_memo)
	{	
		$table_name = "users";
		$id_field_name = "user_id";
		$input_names_array = array("user_name", "user_email", "user_password", "user_memo");
		$input_values_array = array($user_name, $user_email, $user_password, $user_memo);
		$datetime_field_name = 'last_updated';
		
		$entry_ID = func_insertSingleRecordReturnID($table_name, $id_field_name, $input_names_array, $input_values_array, $datetime_field_name);
		return $entry_ID;
	}
	
?>