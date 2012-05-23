<?php

require_once("baseclass_module.php");
require_once("util_events.php");
require_once("util_triggers.php");
require_once("util_actions.php");

class baseclass_hybrid extends baseclass_module
{
	protected $event_id;
	protected $actions_array;
	private $triggers_array;
	private $param_array;

	public function __construct($user_id, $module_data_dictionary)
	{
		parent::__construct($user_id, $module_data_dictionary);
		
		$this->event_id = 0;	
		$this->triggers_array = array();
		$this->actions_array = array();
		$this->retrieve_actions();
		$this->retrieve_triggers();
	}
	
	/*
	 * Handle non-headless trigger
	 * Convert trigger_id to trigger_alias & call trigger_by_alias(...) function
	 * Subclass need not override this function
	 */
	public function trigger($trigger_id, $trigger_param_json, $rule_id)
	{
		//Sanity check, param is optional for some triggers
		if (!isset($trigger_id)) {
			echo "error: trigger_alias is empty";
			return array();
		}
			
		//Trigger by alias & param
		$trigger_alias = $this->getTriggerAliasByID($trigger_id);
		
		//Critical error
		if (!isset($trigger_alias) || $trigger_alias == null || $trigger_alias == "") {
			echo "error: trigger_alias is empty";
			return array();
		}
		
		//Parse parameters JSON string into an associative array, if any
		$trigger_param_array = null;
		if (isset($trigger_param_json)) {
			$trigger_param_array = json_decode(func_json_clean_param_string($trigger_param_json), true);
			if (gettype($trigger_param_array) != "array" || count($trigger_param_array) <= 0)
				$trigger_param_array = null;
		}
		
		//Critical error
		if ($trigger_param_array == null) {
			echo "error: trigger_param_array is empty";
			return array();
		}
		
		return $this->trigger_by_alias($trigger_alias, $trigger_param_array, $rule_id);
	}
		
	/*
	 * Subclass need not override this function
	 */
	public function trigger_headless()
	{
		$event_ids = array();
		foreach ($this->triggers_array as $trigger_id => $trigger_object)
		{
			if ($trigger_object == null)
				continue;
			if (!isset($trigger_object['trigger_id']))						//verify valid trigger object
				continue;
			$params = $trigger_object['trigger_params'];
			if ($params != null && $params != "")
				continue;
			$trigger_alias = $trigger_object['trigger_alias'];
			$temp_event_ids = $this->trigger_by_alias($trigger_alias, null, null);
			if (count($temp_event_ids) > 0)
				$event_ids = array_merge($event_ids, $temp_event_ids);
		}
		return $event_ids;
	}
	
	/*
	 * Handle external trigger by external systems
	 * Convert trigger_id to trigger_alias & call trigger_by_alias_external(...) function
	 * Subclass need not override this function
	 */
	public function trigger_external($trigger_id, $trigger_param_array)
	{
		//Sanity check, param is optional for some triggers
		if (!isset($trigger_id)) {
			echo "error: trigger_alias is empty";
			return null;
		}
			
		//Trigger by alias & param
		$trigger_alias = $this->getTriggerAliasByID($trigger_id);
		
		//Critical error
		if (!isset($trigger_alias) || $trigger_alias == null || $trigger_alias == "") {
			echo "error: trigger_alias is empty";
			return null;
		}
		
		/* Param might be optional
		if ($trigger_param_array == null) {
			echo "error: trigger_param_array is empty";
			return array();
		}
		*/
				
		return $this->trigger_by_alias_external($trigger_alias, $trigger_param_array);
	}
	
	/*
	 * Perform a trigger by its alias
	 * We use alias instead of ID for portability & coder-friendly purposes
	 * Subclass to override this function and perform their magic
	 * $trigger_param_json is optional depending on the trigger implementation
	 */
	protected function trigger_by_alias($trigger_alias, $trigger_param_json, $rule_id)
	{
		return null;
	}
	
	/*
	 * Handle external trigger by its alias
	 * We use alias instead of ID for portability & coder-friendly purposes
	 * Subclass to override this function and perform their magic
	 * 	- Return null to indicate this module does not expect external trigger (default)
	 *	- Return empty array() to indicate nothing happens for this external trigger
	 *	- Return non-empty array containing list of event_id caused by this external trigger
	 */
	protected function trigger_by_alias_external($trigger_alias, $trigger_param_array)
	{
		return null;
	}
	
	/*
	 * Add an event to global events queue
	 */
	protected function add_event($trigger_alias, $trigger_param, $rule_id)
	{
		//Gather parameters for this particular trigger, or set to null if no parameters
		$event_id = $this->addEventByTriggerAliasAndParam($trigger_alias, $trigger_param, $rule_id, false);
		
		//Failed to add to global event queue, do not trigger
		if ($event_id <= 0)
			return 0;

		return $event_id;
	}
	
	/*
	 * Add an async event to global events queue
	 */
	protected function add_event_async($trigger_alias, $trigger_param, $rule_id)
	{
		//Gather parameters for this particular trigger, or set to null if no parameters
		$event_id = $this->addEventByTriggerAliasAndParam($trigger_alias, $trigger_param, $rule_id, true);
		
		//Failed to add to global async event queue, do not trigger
		if ($event_id <= 0)
			return 0;

		return $event_id;
	}
		
	/*
	 * Get number of possible triggers/triggers for this module
	 */
	public function getNumberOfTriggers()
	{
		return count($this->triggers_array);
	}
	
	/*
	 * Get number of possible param-less triggers/triggers for this module
	 */
	public function getNumberOfHeadlessTriggers()
	{
		$count = 0;
		foreach ($this->triggers_array as $trigger_id => $trigger_object)
		{
			if ($trigger_object == null)
				continue;
			$params = $trigger_object['trigger_params'];
			if ($params == null || $params == "")
				$count++;
		}
		return $count;
	}
	
	/*
	 * Retrieve an array of trigger IDs
	 */
	public function getTriggerIDArray()
	{
		$trigger_id_array = array();
		foreach ($this->triggers_array as $trigger_id => $trigger_object)
		{
			if ($trigger_object == null)
				continue;
			if (!isset($trigger_object['trigger_id']))						//verify valid trigger object
				continue;
			$trigger_id_array[] = $trigger_object['trigger_id'];
		}
		return $trigger_id_array;
	}
	
	/*
	 * Retrieve an array of headless trigger IDs
	 */
	public function getHeadlessTriggerIDArray()
	{
		$trigger_id_array = array();
		foreach ($this->triggers_array as $trigger_id => $trigger_object)
		{
			if ($trigger_object == null)
				continue;
			if (!isset($trigger_object['trigger_id']))						//verify valid trigger object
				continue;
			$alias = $trigger_object['trigger_alias'];						//verify valid trigger object
			if ($alias == null || $alias == "")
				continue;
			$trigger_id_array[] = $trigger_object['trigger_id'];
		}
		return $trigger_id_array;
	}
	
	/*
	 * Retrieve an array of non-headless trigger IDs
	 */
	public function getNoneHeadlessTriggerIDArray()
	{
		$trigger_id_array = array();
		foreach ($this->triggers_array as $trigger_id => $trigger_object)
		{
			if ($trigger_object == null)
				continue;
			if (!isset($trigger_object['trigger_id']))						//verify valid trigger object
				continue;
			if (!isset($trigger_object['trigger_alias']))					//verify valid trigger object
				continue;
			$alias = $trigger_object['trigger_alias'];
			if ($alias == null || $alias == "")
				continue;
			$trigger_id_array[] = $trigger_object['trigger_id'];
		}
		return $trigger_id_array;
	}
	
	/*
	 * Retrieve an array of non-headless trigger alias
	 */
	public function getNoneHeadlessTriggerAliasArray()
	{
		$trigger_alias_array = array();
		foreach ($this->triggers_array as $trigger_id => $trigger_object)
		{
			if ($trigger_object == null)
				continue;
			if (!isset($trigger_object['trigger_id']))						//verify valid trigger object
				continue;
			if (!isset($trigger_object['trigger_alias']))					//verify valid trigger object
				continue;
			$alias = $trigger_object['trigger_alias'];
			if ($alias == null || $alias == "")
				continue;
			$trigger_alias_array[] = $alias;
		}
		return $trigger_alias_array;
	}
	
	/*
	 * Retrieve all possible triggers/triggers for this module from database
	 */
	private function retrieve_triggers()
	{
		$sql_result_triggers = func_getTriggersByModuleID( $this->module_id );
		if ($sql_result_triggers == null)
			return;

		//transfer to private array, hash by trigger_id for convenient access later
		while ($one_record = mysql_fetch_assoc($sql_result_triggers))
		{
			$trigger_id = $one_record['trigger_id'];
			$this->triggers_array[$trigger_id] = $one_record;
		}
		$sql_result_triggers = null;
	}
	
	/*
	 * Convenient function to get an action object by its ID (case insensitive)
	 * Return null if not found
	 */
	protected function getTriggerByID($trigger_id)
	{
		if ($trigger_id == null || $trigger_id == 0 || $trigger_id == "")
			return null;
		if ($this->triggers_array == null || !isset($this->triggers_array[$trigger_id]))
			return null;
		if (!isset($this->triggers_array[$trigger_id]['trigger_id']))		//verify valid trigger object
			return null;
		return $this->triggers_array[$trigger_id];
	}
	
	/*
	 * Convenient function to get an action object by its alias (case insensitive)
	 * Return null if not found
	 */
	protected function getTriggerByAlias($trigger_alias)
	{
		if ($trigger_alias == null || $trigger_alias == "")
			return null;
		if ($this->triggers_array == null)
			return null;
		$trigger_alias = strtolower($trigger_alias);
		foreach ($this->triggers_array as $trigger_id => $trigger_object)
		{
			if ($trigger_object == null)
				continue;
			if (!isset($trigger_object['trigger_id']))						//verify valid trigger object
				continue;
			if (!isset($trigger_object['trigger_alias']))					//verify valid trigger object
				continue;
			$alias = strtolower( $trigger_object['trigger_alias'] );
			if ($alias == null || $alias != $trigger_alias)
				continue;
			return $trigger_object;
		}
		return null;
	}
	
	/*
	 * Convenient function to get an trigger alias by its ID (case insensitive)
	 * Return null if not found
	 */
	protected function getTriggerAliasByID($trigger_id)
	{
		if ($trigger_id == null || $trigger_id == 0 || $trigger_id == "")
			return null;
		if ($this->triggers_array == null || !isset($this->triggers_array[$trigger_id]))
			return null;
		if (!isset($this->triggers_array[$trigger_id]['trigger_id']))		//verify valid trigger object
			return null;
		if (!isset($this->triggers_array[$trigger_id]['trigger_alias']))	//verify valid trigger object
			return null;
		return $this->triggers_array[$trigger_id]['trigger_alias'];
	}
	
	/*
	 * Convenient function to get an trigger ID by its alias (case insensitive)
	 * Return null if not found
	 */
	protected function getTriggerIDByAlias($trigger_alias)
	{
		if ($trigger_alias == null || $trigger_alias == "")
			return null;
		$trigger_object = $this->getTriggerByAlias($trigger_alias);
		if ($trigger_object == null)
			return null;
		return $trigger_object['trigger_id'];
	}
	
	/*
	 * Convenient function to get an trigger description by its alias (case insensitive)
	 * Return null if not found
	 */
	protected function getTriggerDescriptionByAlias($trigger_alias)
	{
		if ($trigger_alias == null || $trigger_alias == "")
			return null;
		$trigger_object = $this->getTriggerByAlias($trigger_alias);
		if ($trigger_object == null)
			return null;
		return $trigger_object['trigger_description'];
	}

	/*
	 * Add an event to global event queue, with optional parameters
	 * $trigger_id: mandatory
	 * $event_param: optional
	 * $rule_id: optional if event is a global event
	 * Return event_id if succeed, return 0 otherwise
	 */
	protected function addEventByTriggerAliasAndParam($trigger_alias, $event_param_json, $rule_id)
	{
		if (!isset($this->user_id) || $this->user_id == null || $this->user_id <= 0) {
			echo "<strong>ERROR:</strong> cannot add new trigger to queue because user_id is invalid";
			return 0;
		}
		
		$trigger_id = $this->getTriggerIDByAlias($trigger_alias);
		if ($trigger_id == null || $trigger_id <= 0) {
			echo "<strong>WARNING:</strong> cannot add new trigger to queue because trigger_id is invalid";
			return 0;
		}
		
		if (!isset($event_param_json))
			$event_param_json = null;
			
		$event_id = func_addEvent($trigger_id, $this->user_id, $event_param_json, $rule_id);
		if ($event_id == null || $event_id <= 0) {
			echo "<strong>WARNING:</strong> failed to add new trigger to queue (event_id = $event_id)";
			return 0;
		}
		
		$this->event_id = $event_id;
		return $event_id;
	}


	/*
	 * Totally optional: print out trigger alias, id & description for debugging
	 */
	protected function prettyPrintout($trigger_alias, $event_id)
	{
		$trigger_description = $this->getTriggerDescriptionByAlias($trigger_alias);
		$trigger_id = $this->getTriggerIDByAlias($trigger_alias);
		if ($trigger_description == null)
			return;
		echo "triggered by $trigger_alias (Id: $trigger_id, $trigger_description) <br/>\n";
		echo "added to trigger queue with event_id = $event_id <br/>\n";
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