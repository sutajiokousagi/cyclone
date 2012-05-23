<?php

require_once("baseclass_module.php");
require_once("util_filters.php");

class baseclass_filter extends baseclass_module
{
	protected $filters_array;
	protected $filter_result;

	public function __construct($user_id, $module_data_dictionary)
	{
		parent::__construct($user_id, $module_data_dictionary);
		
		$this->filters_array = array();
		$this->retrieve_filters();
	}

	/*
	 * Subclass to override this function and perform their magic
	 * Return 'true' to indicate that the filter passes through this filter
	 * Return 'false' to indicate that the filter is blocked/filtered by this filter
	 */
	public function filter($event_param, $filter_id, $filter_param)
	{
		$this->filter_result = null;
		
		//no such filter, let it pass through
		$filter_alias = $this->getFilterAliasByID($filter_id);
		if ($filter_alias == null)
			return true;
		
		//we use filter_alias to relate to our function call
		//because $filter_id will change when we copy the filter to another system
		$function_name = "filter_" . $filter_alias;
		$has_method = method_exists($this, $function_name);
		if ($has_method == false)
			return true;

		//execute the function call
		$function_string = "\$this->".$function_name."('" . $this->php_string_escape($event_param) . "','" . $this->php_string_escape($filter_param) . "');";
		eval( $function_string );
				
		return $this->filter_result;
	}
		
	/*
	 * Get number of possible filters for this module
	 */
	public function getNumberOfFilters()
	{
		return count($this->filters_array);
	}
	
	/*
	 * Retrieve all possible filters for this module from database
	 */
	private function retrieve_filters()
	{
		$sql_result_filters = func_getFiltersByModuleID( $this->module_id );
		if ($sql_result_filters == null)
			return;

		//transfer to private array, hash by filter_name for convenient access later
		while ($one_record = mysql_fetch_assoc($sql_result_filters)) {
			$filter_id = $one_record['filter_id'];
			$this->filters_array[$filter_id] = $one_record;
		}
		$sql_result_filters = null;
	}
		
	/*
	 * Convenient function to get an filter object by its name (case insensitive)
	 * Return null if not found
	 */
	protected function getFilterByID($filter_id)
	{
		if ($filter_id == null || $filter_id == 0 || $filter_id == "")
			return null;
		if ($this->filters_array == null || !isset($this->filters_array[$filter_id]))
			return null;
		if (!isset($this->filters_array[$filter_id]['filter_id']))		//verify valid filter object
			return null;
		return $this->filters_array[$filter_id];
	}
	
	/*
	 * Convenient function to get an filter object by its name (case insensitive)
	 * Return null if not found
	 */
	protected function getFilterAliasByID($filter_id)
	{
		if ($filter_id == null || $filter_id == 0 || $filter_id == "")
			return null;
		if ($this->filters_array == null || !isset($this->filters_array[$filter_id]))
			return null;
		if (!isset($this->filters_array[$filter_id]['filter_id']))		//verify valid filter object
			return null;
		if (!isset($this->filters_array[$filter_id]['filter_alias']))
			return null;
		return $this->filters_array[$filter_id]['filter_alias'];
	}
	
	/*
	 * Convenient function to get an filter object by its name (case insensitive)
	 * Return null if not found
	 */
	protected function getFilterByName($filter_name)
	{
		if ($filter_name == null || $filter_name == "")
			return null;
		$filter_name = strtolower($filter_name);
		if ($this->filters_array == null || !isset($this->filters_array[$filter_name]))
			return null;
		if (!isset($this->filters_array[$filter_name]['filter_id']))		//verify valid filter object
			return null;
		return $this->filters_array[$filter_name];
	}

	/*
	 * Convenient function to get an filter_id by its name (case insensitive)
	 * Return null if not found
	 */
	protected function getFilterIDByName($filter_name)
	{
		$filter_object = $this->getFilterByName($filter_name);
		if ($filter_object == null)
			return null;
		$filter_id = intval( $filter_object['filter_id'] );
		if ($filter_id <= 0)												//verify valid filter_id
			return null;
		return $filter_id;
	}
}

?>