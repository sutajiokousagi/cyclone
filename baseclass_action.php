<?php

require_once("baseclass_module.php");
require_once("util_actions.php");

class baseclass_action extends baseclass_module
{
	protected $actions_array;

	public function __construct($user_id, $module_data_dictionary)
	{
		parent::__construct($user_id, $module_data_dictionary);
		
		$this->actions_array = array();
		$this->retrieve_actions();
	}

	/*
	 * Subclass to override this function and perform their magic
	 * Return null to indicate that no action was performed
	 * Otherwise, return an associated array with result parameters
	 */
	public function action($action_id, $event_param, $action_param)
	{
		//critical errors
		if (!isset($event_param) || $event_param == null || $event_param == "") {
			echo "                <span class='error'>ERROR</span>: event_param is empty<br/>\n";
			return null;
		}
		/* $action_param can be optional for hardware action (?)
		if (!isset($action_param) || $action_param == null || $action_param == "") {
			echo "error: action_param is empty";
			return array();
		}
		*/
		
		//no such action, let it pass through
		$action_alias = $this->getActionAliasByID($action_id);
		if ($action_alias == null) {
			echo "                <span class='error'>ERROR</span>: no action_alias for action_id $action_id <br/>\n";
			return null;
		}
						
		//parse event parameters string, if any
		$event_params_array = null;
		if (isset($event_param)) {
			$event_params_array = json_decode(func_json_clean_param_string($event_param), true);
			if (gettype($event_params_array) != "array")
				$event_params_array = null;
		}
			
		//parse action parameters string, if any
		$action_params_array = null;
		if (isset($action_param)) {
			$action_params_array = json_decode(func_json_clean_param_string($action_param), true);
			if (gettype($action_params_array) != "array")
				$action_params_array = null;
		}
		
		return $this->action_by_name($action_alias, $event_params_array, $action_params_array);
	}
	
	/*
	 * Subclass to override this function and perform their magic
	 */
	protected function action_by_name($action_alias, $event_params_array, $action_params_array)
	{
		echo "                <span class='error'>ERROR</span>: subclass need to override 'action_by_name(...)' function<br/>\n";
		return null;
	}
		
	/*
	 * Get number of possible actions for this module
	 */
	public function getNumberOfActions()
	{
		return count($this->actions_array);
	}

	/*
	 * Retrieve all possible actions for this module from database
	 */
	private function retrieve_actions()
	{
		$sql_result_actions = func_getActionsByModuleID( $this->module_id );
		if ($sql_result_actions == null)
			return;

		//transfer to private array, hash by action_name for convenient access later
		while ($one_record = mysql_fetch_assoc($sql_result_actions)) {
			$action_id = $one_record['action_id'];
			$this->actions_array[$action_id] = $one_record;
		}
		$sql_result_actions = null;
	}

	/*
	 * Convenient function to get an action object by its name (case insensitive)
	 * Return null if not found
	 */
	protected function getActionByID($action_id)
	{
		if ($action_id == null || $action_id == 0 || $action_id == "")
			return null;
		if ($this->actions_array == null || !isset($this->actions_array[$action_id]))
			return null;
		if (!isset($this->actions_array[$action_id]['action_id']))		//verify valid action object
			return null;
		return $this->actions_array[$action_id];
	}
	
	/*
	 * Convenient function to get an action object by its name (case insensitive)
	 * Return null if not found
	 */
	protected function getActionAliasByID($action_id)
	{
		if ($action_id == null || $action_id == 0 || $action_id == "")
			return null;
		if ($this->actions_array == null || !isset($this->actions_array[$action_id]))
			return null;
		if (!isset($this->actions_array[$action_id]['action_id']))		//verify valid action object
			return null;
		if (!isset($this->actions_array[$action_id]['action_alias']))	//verify valid action object
			return null;
		return $this->actions_array[$action_id]['action_alias'];
	}
}

?>