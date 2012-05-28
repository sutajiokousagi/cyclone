<?php
/*
- Created by Torin
- Helper functions to retrieve values in Modules table
*/

	require_once("db_config.php");
	
	define("MODULE_ROLE_TRIGGER", 0);
	define("MODULE_ROLE_FILTER", 1);
	define("MODULE_ROLE_ACTION", 2);
	define("MODULE_ROLE_HYBRID", 3);

	function func_getAllModules()
	{
		$condition = "module_enabled = 1";
		return func_getMultiRowFromTable("modules", $condition);
	}
	
	/*
	 * 0: triggers, 1: filters, 2: actions, 3: hybrid
	 */
	function func_getModulesByRole($role)
	{
		$condition = "module_role = '" . $role . "' AND module_enabled = 1";
		return func_getMultiRowFromTable("modules", $condition);
	}
	
	function func_getTriggerModules()
	{
		$condition = "module_role IN (0,3) AND module_enabled = 1";
		return func_getMultiRowFromTable("modules", $condition);
	}
	
	function func_getActionModules()
	{
		$condition = "module_role IN (2,3) AND module_enabled = 1";
		return func_getMultiRowFromTable("modules", $condition);
	}
	
	function func_getFilterModules()
	{
		return func_getModulesByRole(1);
	}
	
	function func_getModuleByAlias($module_alias)
	{
		$condition = "module_alias = '" . $module_alias . "' AND module_enabled = 1";
		return func_getSingleRowFromTable("modules", $condition);
	}
	
	function func_getModuleByTriggerID($trigger_id)
	{
		$sql = "SELECT * FROM triggers ";
		$sql .= " LEFT JOIN modules on triggers.module_id = modules.module_id ";
		$sql .= " WHERE trigger_id = '" . $trigger_id . "'";
		//$sql .= " AND module_enabled = 1";
		//$sql .= " AND trigger_enabled = 1";
		return func_getSingleRowResultWithSQL($sql);
	}
	
	function func_getModuleByFilterID($filter_id)
	{
		$sql = "SELECT * FROM filters ";
		$sql .= " LEFT JOIN modules on filters.module_id = modules.module_id ";
		$sql .= " WHERE filter_id = '" . $filter_id . "'";
		//$sql .= " AND module_enabled = 1";
		//$sql .= " AND filter_enabled = 1";
		return func_getSingleRowResultWithSQL($sql);
	}
	
	function func_getModuleByActionID($action_id)
	{
		$sql = "SELECT * FROM actions ";
		$sql .= " LEFT JOIN modules on actions.module_id = modules.module_id ";
		$sql .= " WHERE action_id = '" . $action_id . "'";
		//$sql .= " AND module_enabled = 1";
		//$sql .= " AND action_enabled = 1";
		return func_getSingleRowResultWithSQL($sql);
	}
	
	//---------------------------------------------------------------------
	// ADD
	//---------------------------------------------------------------------
	
	/*
	 * Add a new module
	 * This function will be rarely used because module addition is usually done manually
	 * $module_type: mandatory. 0: standalone, 1: hardware, 2: software api
	 * $module_name: a short name of this module to be displayed to user, eg. HDMI, RSS, Facebook, Twitter, etc.
	 * $module_description: optional description of this module, will be displayed to user as tips
	 */
	function func_addModule($module_name, $module_type, $module_role, $module_description)
	{	
		$table_name = "modules";
		$id_field_name = "module_id";
		$input_names_array = array("module_name", "module_type", "$module_role", "module_description");
		$input_values_array = array($module_name, $module_type, $module_role, $module_description);
		$datetime_field_name = '';
		
		$entry_ID = func_insertSingleRecordReturnID($table_name, $id_field_name, $input_names_array, $input_values_array, $datetime_field_name);
		return $entry_ID;
	}
	
	//---------------------------------------------------------------------
	// MODULE IMPLEMENTATION
	//---------------------------------------------------------------------
	
	/*
	 * Load a module class instance, create a new instance everytime
	 * This should be optimized later
	 */
	function func_loadModule($user_id, $module_data_dictionary, $show_output)
	{	
		$module_name = $module_data_dictionary['module_name'];
		$module_alias = $module_data_dictionary['module_alias'];
		$module_exist = $module_data_dictionary['module_source_exist'];
		$module_role = intval( $module_data_dictionary['module_role'] );
		
		$module_prefix = "module_";
		if ($module_role == MODULE_ROLE_TRIGGER)		$module_prefix = "trigger_";
		else if ($module_role == MODULE_ROLE_FILTER)	$module_prefix = "filter_";
		else if ($module_role == MODULE_ROLE_ACTION)	$module_prefix = "action_";
				
		//check whether module source file exists
		$module_class_name = $module_prefix . $module_alias;
		$module_source_file = $module_prefix . $module_alias . ".php";
		$module_exist = file_exists("./modules/" . $module_source_file);
		$module_loaded = false;

		if ($show_output) {
			echo "			<strong>$module_name</strong><br/>\n";
			echo "			module source file: $module_source_file - exists: " . ($module_exist ? "yes" : "no") . "<br/>\n";
			echo "			module class name: $module_class_name<br/>\n";
		}
		
		//class file doesn't exist
		if ($module_exist == false)
		{
			if ($show_output)
				echo "			does not exist. Skipping...<br/>\n";
			return null;
		}
		
		//load the class file and verify it
		include_once( "./modules/" . $module_source_file );
		$module_loaded = class_exists( $module_class_name, false );	
		if ($module_loaded == false)
		{
			if ($show_output)
				echo "			failed to load module class. Skipping...<br/>\n";
			return null;
		}
		if ($show_output)
			echo "			module class loaded<br/>\n";
					
		//try to create module class instance
		$module_object = null;
		try {
			$module_object = new $module_class_name( $user_id, $module_data_dictionary );
		} catch (Exception $e) {
			$module_object = null;
		}
		
		//failed to create class instance
		if ($module_object == null)
		{
			if ($show_output)
				echo "			failed to create module class instance. Skipping...<br/>\n";
			return null;
		}
		$module_version = $module_object->getModuleVersion();
		if ($show_output)
			echo "			module class instance created (module version = $module_version)<br/>\n";
		
		//Get user configuration for this module (eg. account configs, access token, app id, etc)
		$hasConfig = isset($configs_array) && $configs_array != null && count($configs_array) > 0;
		if (!$hasConfig)
		{
			if ($show_output)
				echo "			module has no user-config data <br/>\n";
		}
		else
		{
			if ($show_output)
				echo "			module has " . count($configs_array[$module_id]) . " user-config(s)<br/>\n";
			$module_object->setConfig( $configs_array[$module_id] );
		}
		
		//Inject more data to module_data_dictionary
		$module_data_dictionary['module_loaded'] = $module_loaded;
		$module_data_dictionary['module_object'] = $module_object;
		$module_data_dictionary['module_version'] = $module_version;
		$module_data_dictionary['hasConfig'] = $hasConfig;
		return $module_object;
	}

?>