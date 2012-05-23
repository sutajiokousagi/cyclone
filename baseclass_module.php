<?php

//additional include path
set_include_path('modules' . PATH_SEPARATOR . get_include_path());

require_once("util_configs.php");
require_once("util_json.php");

class baseclass_module
{
	protected $user_id;
	protected $module_id;
	protected $module_name;
	protected $module_alias;
	protected $module_role;
	protected $module_class_name;
	protected $module_source_file;
	protected $configs_array;
	
	/*
	 * Constructor
	 */
	public function __construct($user_id, $module_data_dictionary)
	{
		$this->user_id = $user_id;
		$this->module_id = $module_data_dictionary["module_id"];
		$this->module_name = $module_data_dictionary["module_name"];
		$this->module_alias = $module_data_dictionary["module_alias"];
		$this->module_class_name = $module_data_dictionary["module_class_name"];
		$this->module_source_file = $module_data_dictionary["module_source_file"];
		
		$configs_array = array();
	}

	/*
	 * Subclass to override this function and return its own version number
	 */
	public function getModuleVersion()
	{
		return "0.0";
	}
		
	//----------------------------------------------------------------------------------
		
	/*
	 * Set configuration data for this module
	 * Convert from raw SQL array into dictionary by config_name
	 */
	public function setConfigs($raw_configs_array)
	{
		foreach ($raw_configs_array as $config_data_dictionary)
		{
			$config_name = $config_data_dictionary['config_name'];
			if ($config_name != null)
				$this->configs_array[$config_name] = $config_data_dictionary;
		}
	}
	
	/*
	 * Convenient function to get a configuration value from a key name
	 * Return null if not found
	 */
	protected function getConfigByName($config_name)
	{
		if ($config_name == null)
			return null;
		if ($this->configs_array == null || !isset($this->configs_array[$config_name]))
			return null;
		$config_data_dictionary = $this->configs_array[$config_name];
		return $config_data_dictionary['config_param'];
	}
	
	/*
	 * Convenient function to set a configuration name/value pair
	 * Note that this only changes the configuration of the current class instance, and does not have any effects on database
	 */
	protected function setConfigWithNameAndParam($config_name, $config_param)
	{
		if ($config_name == null)
			return;
		if ($this->configs_array == null)
			$this->configs_array = array();
		if ($this->configs_array[$config_name] == null)
			$this->configs_array[$config_name] = array();
		$this->configs_array[$config_name]['config_param'] = $config_param;
	}
	
	//---------
	
	protected function php_string_escape($theString)
	{
		$theString = str_replace("'", "\'", $theString);
		$theString = str_replace("\"", "\"", $theString);
		return $theString;
	}
}

?>