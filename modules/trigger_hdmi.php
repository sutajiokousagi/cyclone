<?php
/*
- Created by Torin
*/

require_once("baseclass_trigger.php");
require_once("util_preferences.php");

class trigger_hdmi extends baseclass_trigger
{
	const CONNECT_TRIGGER_ALIAS = "connect";
	const DISCONNECT_TRIGGER_ALIAS = "disconnect";
	const RESOLUTION_CHANGE_TRIGGER_ALIAS = "resolution_change";
	
	/*
	 * Override superclass to return my own version number
	 */
	public function getModuleVersion()
	{
		return "1.0";
	}
	
	/*
	 * Get raw preference name for this module
	 */
	private function get_preference_name($name)
	{
		$pref_name = "" . $this->module_id . "_" . $name;
		return $pref_name;
	}

	/*
	 * Get raw preference value given a high-level variable name
	 */
	private function get_preference_value($name)
	{
		$pref_value = func_getPreference($this->user_id, $this->get_preference_name($name));
		return $pref_value;
	}
	
	/*
	 * Get value of 'previous_state' variable
	 */
	private function get_previous_state()
	{
		//Get previous remembered coordinates & convert it to an associative array
		$pref_value = $this->get_preference_value("previous_state");
		$param_array = null;
		if ($pref_value != null)
			$param_array = json_decode(func_json_clean_param_string($pref_value), true);
		return $param_array;
	}

	/*
	 * Save new location into 'previous_state' variable while also saving/override previous location history
	 */
	private function save_new_state($trigger_param_array)
	{
		//Get previous remembered coordinates	
		$pref_name_previous = $this->get_preference_name("previous_state");		
		func_setPreference($this->user_id, $pref_name_previous, json_encode($trigger_param_array));
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
		$trigger_param_json = json_encode($trigger_param_array);
				
		if ($trigger_alias == self::CONNECT_TRIGGER_ALIAS || $trigger_alias == self::DISCONNECT_TRIGGER_ALIAS) {
			//Add new event to global async event queue
			$rule_id = 0;	//global
			$event_id = $this->add_event_async($trigger_alias, $trigger_param_json, $rule_id);
			return array($event_id);
		}
		
		/*
		 * No headless trigger for this module
		 */
		
		/*
		 * Implement all headless triggers above this line
		 */
		if ($trigger_param_array == null) {
			echo "error: trigger_param_array is empty";
			return null;
		}
		
		/*
		 * Check input format
		 */
		$old_resolution = null;
		$new_resolution = null;
		foreach ($trigger_param_array as $key => $value) {
			if ($key == 'old_resolution')		$old_resolution = trim($value);
			if ($key == 'new_resolution')		$new_resolution = trim($value);
		}
		if ($old_resolution === null || $new_resolution === null) {
			echo "error: trigger_param_array is missing some parameters";
			return null;
		}

		//Update new/current value
		$this->save_new_state($trigger_param_array);
		
		return array();
	}
	
	
	
	/*
	 * Perform a trigger by its alias
	 * We use alias instead of ID for portability & coder-friendly purposes
	 * Subclass to override this function and perform their magic
	 * $rule_trigger_param is optional depending on the trigger implementation
	 */
	protected function trigger_by_alias($trigger_alias, $trigger_param_array, $rule_id)
	{		
		/*
		 * No synchronous trigger for this module
		 */
		return null;
	}

}

?>