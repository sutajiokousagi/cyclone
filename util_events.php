<?php
/*
- Created by Torin
- Helper functions to retrieve values in Events table
*/

	require_once("db_config.php");

	function func_getEventByID($id)
	{
		$condition = "event_id = '" . $id . "'";
		return func_getSingleRowFromTable("events", $condition);
	}
	
	/*
	 * Returns all events generated for a given user, returns raw SQL result
	 */
	function func_getEventsByUserID($id)
	{
		$condition = "user_id = '" . $id . "' AND event_status = 0";
		return func_getMultiRowFromTable("events", $condition);
	}
	
	/*
	 * Returns all async events generated for a given user, returns raw SQL result
	 */
	function func_getAsyncEventsByUserID($id)
	{
		$condition = "user_id = '" . $id . "' AND event_status = 0";
		return func_getMultiRowFromTable("events_async", $condition);
	}
	
	//---------------------------------------------------------------------
	// ADD
	//---------------------------------------------------------------------
	
	/*
	 * Add a new event to global events queue
	 * $trigger_id: specific pre-defined trigger in the system (unique to a $module_id)
	 * $user_id: mandatory
	 * $event_param: optional string parameter of the new trigger, eg. new value, new twit, new email, etc.
	 * $event_status of the trigger will be set to default 'unprocessed'
	 */
	function func_addEvent($trigger_id, $user_id, $event_param, $rule_id)
	{	
		$table_name = "events";
		$id_field_name = "event_id";
		$input_names_array = array("trigger_id", "user_id", "rule_id", "event_param");
		$input_values_array = array($trigger_id, $user_id, $rule_id, $event_param);
		$datetime_field_name = 'last_updated';
		
		$entry_ID = func_insertSingleRecordReturnID($table_name, $id_field_name, $input_names_array, $input_values_array, $datetime_field_name);
		return $entry_ID;
	}
	
	function func_addAsyncEvent($trigger_id, $user_id, $event_param, $rule_id)
	{	
		$table_name = "events_async";
		$id_field_name = "event_id";
		$input_names_array = array("trigger_id", "user_id", "rule_id", "event_param");
		$input_values_array = array($trigger_id, $user_id, $rule_id, $event_param);
		$datetime_field_name = 'last_updated';
		
		$entry_ID = func_insertSingleRecordReturnID($table_name, $id_field_name, $input_names_array, $input_values_array, $datetime_field_name);
		return $entry_ID;
	}
	
	//---------------------------------------------------------------------
	// UPDATE
	//---------------------------------------------------------------------
	
	function func_updateEventStatus($event_id, $event_status)
	{	
		$table_name = "events";
		$id_field_name = "event_id";
		$id_field_value = $event_id;
		$input_names_array = array("event_status");
		$input_values_array = array($event_status);
		$datetime_field_name = 'last_updated';
		
		$entry_ID = func_updateSingleRecordReturnNumAffected($table_name, $id_field_name, $id_field_value, $input_names_array, $input_values_array, $datetime_field_name);
		return $entry_ID;
	}
	
	function func_updateAsyncEventStatus($event_id, $event_status)
	{	
		$table_name = "events_async";
		$id_field_name = "event_id";
		$id_field_value = $event_id;
		$input_names_array = array("event_status");
		$input_values_array = array($event_status);
		$datetime_field_name = 'last_updated';
		
		$entry_ID = func_updateSingleRecordReturnNumAffected($table_name, $id_field_name, $id_field_value, $input_names_array, $input_values_array, $datetime_field_name);
		return $entry_ID;
	}
	
?>