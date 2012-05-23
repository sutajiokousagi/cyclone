<?php
/*
- Created by Torin
- A convenient utility for getting entries from database
*/
	function func_getSingleRowFromTable($table_name, $condition)
	{
		global $link;
		
		$has_condition = isset($condition) && strlen($condition) > 5;
		if (!$has_condition)		$sql = "SELECT * from $table_name LIMIT 0,1";
		else						$sql = "SELECT * from $table_name WHERE $condition LIMIT 0,1";
			
		$result = mysql_query($sql, $link) or func_dieWithMessage("error in SQL syntax: $sql");
		if(!$result)
			return null;
		$exists = mysql_num_rows($result);
		if ($exists <= 0)
			return null;
		$one_record = mysql_fetch_assoc($result);
		return $one_record;
	}
	
	function func_getMultiRowFromTable($table_name, $condition)
	{
		global $link;
		
		$has_condition = isset($condition) && strlen($condition) > 5;
		if (!$has_condition)		$sql = "SELECT * from $table_name";
		else						$sql = "SELECT * from $table_name WHERE $condition";

		return func_getMultiRowResultWithSQL($sql);
	}
	
	function func_getMultiRowResultWithSQL($sql)
	{
		global $link;

		$result = mysql_query($sql, $link) or func_dieWithMessage("error in SQL syntax: $sql");
		if(!$result)
			return null;
		$exists = mysql_num_rows($result);
		if ($exists <= 0)
			return null;
		return $result;
	}
	
	function func_getSingleRowResultWithSQL($sql)
	{
		global $link;

		$sql .= " LIMIT 0,1";
		$result = mysql_query($sql, $link) or func_dieWithMessage("error in SQL syntax: $sql");
		if(!$result)
			return null;
		$exists = mysql_num_rows($result);
		if ($exists <= 0)
			return null;			
		$one_record = mysql_fetch_assoc($result);
		return $one_record;
	}
	
	function func_insertSingleRecordReturnID($table_name, $id_field_name, $input_names_array, $input_values_array, $datetime_field_name)
	{
		global $link;
		
		$new_record = func_insertSingleRecordReturnRecord($table_name, $input_names_array, $input_values_array, $datetime_field_name);
		if ($new_record == null)
			return -1;
		return intval( $new_record[ $id_field_name ] );
	}
	
	function func_insertSingleRecordReturnRecord($table_name, $input_names_array, $input_values_array, $datetime_field_name)
	{
		global $link;
		
		$currentDateTime = date('Y-m-d H:i:s');
		$sql = "INSERT INTO $table_name (";
		$sql2 = " VALUES (";
		
		if ($datetime_field_name != "")
		{
			$tempArray = array($datetime_field_name);
			$tempArray2 = array($currentDateTime);
			$input_names_array = array_merge($tempArray, $input_names_array);
			$input_values_array = array_merge($tempArray2, $input_values_array);
		}
		
		//Input values
		for($i=0; $i<count($input_names_array); $i++)
		{ 
			$key_name = $input_names_array[$i];
			$input_value = $input_values_array[$i];
			if ($input_value == "" || $input_value == null)
				continue;
			$input_value = mysql_real_escape_string($input_value);
				
			if ($i < count($input_names_array)-1)	{	$sql = $sql . "$key_name,";		$sql2 = $sql2 . "'$input_value',";		}
			else									{	$sql = $sql . "$key_name)";		$sql2 = $sql2 . "'$input_value')";		}
		}
		
		//some cleaning
		if (func_endsWith($sql, ','))	$sql = substr($sql,0,-1) . ')';
		if (func_endsWith($sql2, ','))	$sql2 = substr($sql2,0,-1) . ')';
		$sql = $sql . $sql2;
		
		//--------------------------------------------------------
		// SQL query
		
		$result = mysql_query($sql, $link) or func_dieWithMessage("error in SQL syntax: $sql");
		if(!$result)
			func_dieWithMessage("error adding into $table_name");

		//--------------------------------------------------------
		// Get back new entry ID just inserted
			
		$sql = "SELECT * FROM $table_name WHERE ";
		
		//Input values
		for($i=0; $i<count($input_names_array); $i++)
		{ 
			$key_name = $input_names_array[$i];
			$input_value = $input_values_array[$i];
			if ($input_value == "" || $input_value == null)
				continue;
			$input_value = mysql_real_escape_string($input_value);
				
			if ($i < count($input_names_array)-1)		$sql = $sql . "$key_name = '$input_value' AND ";
			else										$sql = $sql . "$key_name = '$input_value' ";
		}
		
		//some cleaning
		if (func_endsWith($sql, 'AND '))	$sql = substr($sql,0,-4);
		
		//SQL query
		$result = mysql_query($sql, $link) or func_dieWithMessage("error in SQL syntax: $sql");
		if(!$result)
			func_dieWithMessage("error adding into $table_name");

		$exists = mysql_num_rows($result);
		if ($exists <= 0)
			func_dieWithMessage("error adding into $table_name");
		
		$existing_record = mysql_fetch_assoc($result);
		return $existing_record;
	}
		
	function func_updateSingleRecordReturnNumAffected($table_name, $id_field_name, $id_field_value, $input_names_array, $input_values_array, $datetime_field_name)
	{
		global $link;
		
		$currentDateTime = date('Y-m-d H:i:s');
		$sql = "UPDATE $table_name SET ";
		$conditional_string = "WHERE $id_field_name = '$id_field_value' ";
				
		if ($datetime_field_name != "")
		{
			$tempArray = array($datetime_field_name);
			$tempArray2 = array($currentDateTime);
			$input_names_array = array_merge($tempArray, $input_names_array);
			$input_values_array = array_merge($tempArray2, $input_values_array);
		}
		
		$key_name = "";
		$input_value = "";
		
		//Input values
		for($i=0; $i<count($input_names_array); $i++)
		{ 
			$key_name = $input_names_array[$i];
			$input_value = $input_values_array[$i];
			if ($input_value == "" || $input_value == null)
				continue;
			$input_value = mysql_real_escape_string($input_value);
				
			if ($i < count($input_names_array)-1)	$sql = $sql . "$key_name = '$input_value', ";
			else									$sql = $sql . "$key_name = '$input_value' ";
		}
		
		//some cleaning
		if (func_endsWith($sql, ', '))	$sql = substr($sql,0,-2) . ' ';
		
		//Conditional values
		if ($conditional_string == "WHERE " || $conditional_string == "")
			func_dieWithMessage("conditional input value(s) is required");
			
		$sql = $sql . $conditional_string;
		
		//--------------------------------------------------------
		// SQL query
		
		$result = mysql_query($sql, $link) or func_dieWithMessage("error in SQL syntax: $sql");
		if(!$result)
			func_dieWithMessage("error updating $table_name");
		$num_affected = mysql_affected_rows($link);
		if($num_affected != 1)
			return -2;

		//--------------------------------------------------------
		// Get back new entry ID just inserted
			
		$sql = "SELECT * FROM $table_name " . $conditional_string;
		$result = mysql_query($sql, $link) or func_dieWithMessage("error in SQL syntax: $sql");
		if(!$result)
			func_dieWithMessage("error updating $table_name");

		$exists = mysql_num_rows($result);
		if ($exists <= 0)
			return -1;
		
		$updated_record = mysql_fetch_assoc($result);
		
		//Verify the updated fields
		$match = true;
		for($i=0; $i<count($input_names_array); $i++)
		{ 
			$key_name = $input_names_array[$i];
			$input_value = $input_values_array[$i];
			if ($input_value == "" || $input_value == null)
				continue;
			$new_value = $updated_record[$key_name];
							
			if ( trim($new_value) == trim($input_value) )
				continue;
			$match = false;
		}
		if (!$match)
			return 0;
			
		return $num_affected;
	}
	
	function func_deleteRecordsReturnNumAffected($table_name, $input_names_array, $input_values_array)
	{
		global $link;
		
		$sql = "DELETE FROM $table_name ";
		$conditional_string = "WHERE ";

		$key_name = "";
		$input_value = "";
		
		//Input values
		for($i=0; $i<count($input_names_array); $i++)
		{ 
			$key_name = $input_names_array[$i];
			$input_value = $input_values_array[$i];
			if ($input_value == "" || $input_value == null)
				continue;
			$input_value = mysql_real_escape_string($input_value);
				
			if ($i < count($input_names_array)-1)	$conditional_string = $conditional_string . "$key_name = '$input_value' AND ";
			else									$conditional_string = $conditional_string . "$key_name = '$input_value' ";
		}
		
		//some cleaning
		if (func_endsWith($conditional_string, 'AND '))	$conditional_string = substr($conditional_string,0,-4) . ' ';
		
		//Conditional values
		if ($conditional_string == "WHERE " || $conditional_string == "")
			func_dieWithMessage("conditional input value(s) is required");
			
		$sql = $sql . $conditional_string;
		
		//--------------------------------------------------------
		// SQL query
		
		$result = mysql_query($sql, $link) or func_dieWithMessage("error in SQL syntax: $sql");
		if(!$result)
			func_dieWithMessage("error updating $table_name");
		$num_affected = mysql_affected_rows($link);
		if($num_affected != 1)
			return -2;
			
		return $num_affected;
	}
?>