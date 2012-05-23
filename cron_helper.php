<?php

	/*
	 * Return true if this module is already loaded
	 */
	function func_isModuleLoaded($module_id)
	{
		global $modules_array;
		return (isset($modules_array[$module_id]) &&
				isset($modules_array[$module_id]['module_object']) &&
				$modules_array[$module_id]['module_object'] != null);
	}
	
	/*
	 * Load a module class instance into our global module pool
	 */
	function load_module($module_data_dictionary, $show_output)
	{	
		global $user_id;
		global $modules_array;
		global $configs_array;
			
		//Already loaded
		$module_id = $module_data_dictionary['module_id'];
		if (func_isModuleLoaded($module_id))
			return true;
		
		$module_name = $module_data_dictionary['module_name'];
		$module_alias = $module_data_dictionary['module_alias'];
		$module_exist = $module_data_dictionary['module_source_exist'];
		$module_role = intval( $module_data_dictionary['module_role'] );
		
		$module_prefix = "module_";
		if ($module_role == 0)			$module_prefix = "trigger_";
		else if ($module_role == 1)		$module_prefix = "filter_";
		else if ($module_role == 2)		$module_prefix = "action_";
				
		//check whether module source file exists
		$module_class_name = $module_prefix . $module_alias;
		$module_source_file = $module_prefix . $module_alias . ".php";
		$module_exist = file_exists("./modules/" . $module_source_file);
		$module_loaded = false;

		if ($show_output) {
			echo "                <strong>$module_name</strong><br/>\n";
			echo "                module source file: $module_source_file - exists: " . ($module_exist ? "yes" : "no") . "<br/>\n";
			echo "                module class name: $module_class_name<br/>\n";
		}
		
		//class file doesn't exist
		if ($module_exist == false)
		{
			if ($show_output)
				echo "                does not exist. Skipping...<br/>\n";
			return false;
		}
		
		//load the class file and verify it
		include_once( "./modules/" . $module_source_file );
		$module_loaded = class_exists( $module_class_name, false );	
		if ($module_loaded == false)
		{
			if ($show_output)
				echo "                failed to load module class. Skipping...<br/>\n";
			return false;
		}
		if ($show_output)
			echo "                module class loaded<br/>\n";
					
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
				echo "                failed to create module class instance. Skipping...<br/>\n";
			return false;
		}
		$module_version = $module_object->getModuleVersion();
		if ($show_output)
			echo "                module class instance created (module version = $module_version)<br/>\n";
		
		//Get user configuration for this module (eg. account configs, access token, app id, etc)
		$hasConfig = isset($configs_array[$module_id]) && $configs_array[$module_id] != null && count($configs_array[$module_id]) > 0;
		if (!$hasConfig)
		{
			if ($show_output)
				echo "                module has no user-config data <br/>\n";
		}
		else
		{
			if ($show_output)
				echo "                module has " . count($configs_array[$module_id]) . " user-config(s)<br/>\n";
			$module_object->setConfigs( $configs_array[$module_id] );
		}
		
		//Inject more data to modules_array
		$module_data_dictionary['module_loaded'] = $module_loaded;
		$module_data_dictionary['module_object'] = $module_object;
		$module_data_dictionary['module_version'] = $module_version;
		$module_data_dictionary['hasConfig'] = $hasConfig;
		$modules_array[$module_id] = $module_data_dictionary;
		return true;
	}
	
?>