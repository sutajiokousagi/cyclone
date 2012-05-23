<?php
/*
- Created by Torin
- This is a simple HelloTrigger module that generate trigger/triggers on even minutes (ie. alternate minutes)
*/

require_once("baseclass_trigger.php");

class trigger_hellotrigger extends baseclass_trigger
{
	const EVEN_MINUTE_TRIGGER_ALIAS = "even_minute";
	const ODD_MINUTE_TRIGGER_ALIAS = "odd_minute";
	
	/*
	 * Override superclass to return my own version number
	 */
	public function getModuleVersion()
	{
		return "1.0";
	}
	
	/*
	 * Perform a trigger by its alias
	 * We use alias instead of ID for portability & coder-friendly purposes
	 * Subclass to override this function and perform their magic
	 * $rule_trigger_param is optional depending on the trigger implementation
	 */
	protected function trigger_by_alias($trigger_alias, $trigger_param_array, $rule_id)
	{
		//Temporarily disable
		return null;
		
		/*
		 * This module contains only headless triggers, no param required
		 */
				
		//Key factors for triggering this module
		$current_minute = intval( date('i') );
		$trigger_param = date('c');				//ISO 8601 formatted date
		
		$trigger_param_array = array();
		$trigger_param_array['datetime'] = $trigger_param;
		$trigger_param_json = json_encode($trigger_param_array);
		
		//EvenMinuteTrigger
		if ($trigger_alias == self::EVEN_MINUTE_TRIGGER_ALIAS) {
			if ($current_minute % 2 == 0) {
				$event_id = $this->add_event($trigger_alias, $trigger_param_json, $rule_id);
				$this->prettyPrintout($trigger_alias, $event_id);
				return array($event_id);
			}
		}
		
		//OddMinuteTrigger
		if ($trigger_alias == self::ODD_MINUTE_TRIGGER_ALIAS) {
			if ($current_minute % 2 == 1) {
				$event_id = $this->add_event($trigger_alias, $trigger_param_json, $rule_id);
				$this->prettyPrintout($trigger_alias, $event_id);
				return array($event_id);
			}
		}

		return null;
	}
	
}

?>