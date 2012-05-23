<?php
/*
- Created by Torin
- This is a simple HelloTrigger module that generate trigger/triggers on even minutes (ie. alternate minutes)
*/

require_once("baseclass_trigger.php");
require_once("util_preferences.php");

class trigger_ios extends baseclass_trigger
{
	const ENTER_LOCATION_TRIGGER_ALIAS = "enter_location";
	const LEAVE_LOCATION_TRIGGER_ALIAS = "leave_location";
	const WITHIN_LOCATION_TRIGGER_ALIAS = "within_location";
	const WITHIN_LOCATION_MINUTE_TRIGGER_ALIAS = "within_location_minute";
	
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
	 * Get value of 'current_location' variable
	 */
	private function get_current_location()
	{
		//Get current remembered coordinates & convert it to an associative array
		$pref_value = $this->get_preference_value("current_location");
		$param_array = null;
		if ($pref_value != null)
			$param_array = json_decode(func_json_clean_param_string($pref_value), true);
		return $param_array;
	}
	
	/*
	 * Get value of 'previous_location' variable
	 */
	private function get_previous_location()
	{
		//Get previous remembered coordinates & convert it to an associative array
		$pref_value = $this->get_preference_value("previous_location");
		$param_array = null;
		if ($pref_value != null)
			$param_array = json_decode(func_json_clean_param_string($pref_value), true);
		return $param_array;
	}

	/*
	 * Save new location into 'current_location' variable only
	 */
	private function save_new_location_only($trigger_param_array)
	{
		$pref_name = $this->get_preference_name("current_location");		
		func_setPreference($this->user_id, $pref_name, json_encode($trigger_param_array));
	}
	
	/*
	 * Save new location into 'current_location' variable while also saving/override previous location history
	 */
	private function save_new_location($trigger_param_array)
	{
		//Get previous remembered coordinates	
		$pref_name_previous = $this->get_preference_name("previous_location");
		$pref_name_current = $this->get_preference_name("current_location");
		$pref_current_value = $this->get_preference_value("current_location");
		if ($pref_current_value != null)
			func_setPreference($this->user_id, $pref_name_previous, $pref_current_value);
			
		func_setPreference($this->user_id, $pref_name_current, json_encode($trigger_param_array));
	}
	
	private function distance($lat1, $lon1, $lat2, $lon2)
	{
		if ($lat1 == $lat2 && $lon1 == $lon2)
			return 0;
			
		$R = 6371; 			// km, earth’s mean radius
		$dLat = deg2rad($lat2-$lat1);
		$dLon = deg2rad($lon2-$lon1);
		$lat1 = deg2rad($lat1);
		$lat2 = deg2rad($lat2);

		$a = sin($dLat/2) * sin($dLat/2) +
		     sin($dLon/2) * sin($dLon/2) * cos($lat1) * cos($lat2); 
		$c = 2 * atan2(sqrt($a), sqrt(1-$a)); 
		$d = $R * $c;
		return $d * 1000;	//meters
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
		$accuracy = null;
		$longitude = null;
		$latitude = null;
		foreach ($trigger_param_array as $key => $value) {
			if ($key == 'accuracy')		$accuracy = trim($value);
			if ($key == 'longitude')	$longitude = trim($value);
			if ($key == 'latitude')		$latitude = trim($value);
		}
		if ($accuracy === null || $longitude === null || $latitude === null) {
			echo "error: trigger_param_array is missing some parameters";
			return null;
		}

		//Update new/current value
		$this->save_new_location_only($trigger_param_array);
		
		//Same code as timer_events.php
		//Get all rules with this trigger_module, from all users
		//Trigger them
		
		//Trigger asynchronously
		/*
		$trigger_alias_array = $this->getNoneHeadlessTriggerAliasArray();
		foreach($trigger_alias_array as $alias)
			$this->trigger_by_alias($alias, $trigger_param_array, nil);
			
		//Yes, twice. After we have triggered all triggers asynchronously
		$this->save_new_location($trigger_param_array);
		*/
		
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
		 * All triggers need these parameters
		 */
		$distance = null;
		$longitude = null;
		$latitude = null;
		foreach ($trigger_param_array as $key => $value) {
			if ($key == 'distance')		$distance = floatval(trim($value));
			if ($key == 'longitude')	$longitude = floatval(trim($value));
			if ($key == 'latitude')		$latitude = floatval(trim($value));
		}
		if ($distance == null || $longitude === null || $latitude === null) {
			echo "error: trigger_param_array is missing some parameters";
			return null;
		}
		
		//external trigger has not been activated
		$current_location = $this->get_current_location();
		$previous_location = $this->get_previous_location();
		if ($current_location == null) {
			echo "warning: current location data is not available";
			return array();
		}
		$event_param_array = $current_location;

		//Within given location
		if ($trigger_alias == self::WITHIN_LOCATION_TRIGGER_ALIAS)
		{
			//distance too far
			$lat2_current = floatval( $current_location['latitude'] );
			$lon2_current = floatval( $current_location['longitude'] );
			$delta_distance_current = $this->distance($latitude, $longitude, $lat2_current, $lon2_current);
			if ($delta_distance_current > $distance) {
				echo "trigger: $trigger_alias, dynamic input: $delta_distance_current, $lat2_current, $lon2_current";
				return array();
			}

			echo "trigger: $trigger_alias, dynamic input: $delta_distance_current, $lat2_current, $lon2_current <br/>";			
			$event_param_array['distance'] = $delta_distance_current;
			$event_param_json = json_encode($event_param_array);

			//Add new event to global event queue
			$event_id = $this->add_event($trigger_alias, $event_param_json, $rule_id);
			$this->prettyPrintout($trigger_alias, $event_id);
			return array($event_id);
		}

		//Entering given location
		if ($trigger_alias == self::ENTER_LOCATION_TRIGGER_ALIAS)
		{
			//distance too far
			$lat2_current = floatval( $current_location['latitude'] );
			$lon2_current = floatval( $current_location['longitude'] );
			$delta_distance_current = $this->distance($latitude, $longitude, $lat2_current, $lon2_current);
			if ($delta_distance_current > $distance) {
				echo "trigger: $trigger_alias, current distance: $delta_distance_current";
				return array();
			}
				
			//previous distance too near
			$lat2_previous = floatval( $previous_location['latitude'] );
			$lon2_previous = floatval( $previous_location['longitude'] );
			$delta_distance_previous = $this->distance($latitude, $longitude, $lat2_previous, $lon2_previous);
			if ($delta_distance_previous <= $distance) {
				echo "trigger: $trigger_alias, current/previous distance: $delta_distance_current, $delta_distance_previous";
				return array();
			}

			//this is the condition here
			//if ($delta_distance_current <= $distance && $delta_distance_previous > $distance)
			
			//Avoid this trigger to be triggered twice if no further location data arrives
			//When not triggered asynchronously
			if ($rule_id != nil)
				$this->save_new_location($current_location);
			
			echo "trigger: $trigger_alias, current/previous distance: $delta_distance_current, $delta_distance_previous <br/>";
			$event_param_array['distance'] = $delta_distance_current;
			$event_param_array['previous distance'] = $delta_distance_previous;
			$event_param_json = json_encode($event_param_array);
			
			//Add new event to global event queue
			$event_id = $this->add_event($trigger_alias, $event_param_json, $rule_id);
			$this->prettyPrintout($trigger_alias, $event_id);
			return array($event_id);
		}
		
		//Leaving given location
		if ($trigger_alias == self::LEAVE_LOCATION_TRIGGER_ALIAS)
		{
			//distance too far				
			$lat2_current = floatval( $current_location['latitude'] );
			$lon2_current = floatval( $current_location['longitude'] );
			$delta_distance_current = $this->distance($latitude, $longitude, $lat2_current, $lon2_current);
			if ($delta_distance_current <= $distance) {
				echo "trigger: $trigger_alias, current distance: $delta_distance_current";
				return array();
			}
				
			//previous distance too near
			$lat2_previous = floatval( $previous_location['latitude'] );
			$lon2_previous = floatval( $previous_location['longitude'] );
			$delta_distance_previous = $this->distance($latitude, $longitude, $lat2_previous, $lon2_previous);
			if ($delta_distance_previous > $distance) {
				echo "trigger: $trigger_alias, current/previous distance: $delta_distance_current, $delta_distance_previous";
				return array();
			}

			//this is the condition here
			//if ($delta_distance_current > $distance && $delta_distance_previous <= $distance)
			
			//Avoid this trigger to be triggered twice if no further location data arrives
			//When not triggered asynchronously
			if ($rule_id != nil)
				$this->save_new_location($current_location);
			
			echo "trigger: $trigger_alias, current/previous distance: $delta_distance_current, $delta_distance_previous <br/>";
			$event_param_array['distance'] = $delta_distance_current;
			$event_param_array['previous distance'] = $delta_distance_previous;
			$event_param_json = json_encode($event_param_array);
			
			//Add new event to global event queue
			$event_id = $this->add_event($trigger_alias, $event_param_json, $rule_id);
			$this->prettyPrintout($trigger_alias, $event_id);
			return array($event_id);
		}

		//Within given location for xxx minutes
		if ($trigger_alias == self::WITHIN_LOCATION_MINUTE_TRIGGER_ALIAS)
		{
			//distance too far
			$lat2_current = floatval( $current_location['latitude'] );
			$lon2_current = floatval( $current_location['longitude'] );
			$delta_distance_current = $this->distance($latitude, $longitude, $lat2_current, $lon2_current);
			if ($delta_distance_current > $distance)
				return array();

			//Add more conditions here
			return array();
			
			echo "trigger: $trigger_alias, current distance: $delta_distance_current <br/>";
			$event_param_array['distance'] = $delta_distance_current;
			$event_param_json = json_encode($event_param_array);
			
			//Avoid this trigger to be triggered twice if no further location data arrives
			//When not triggered asynchronously
			if ($rule_id != nil)
				$this->save_new_location($current_location);
				
			//Add new event to global event queue
			$event_id = $this->add_event($trigger_alias, $event_param_json, $rule_id);
			$this->prettyPrintout($trigger_alias, $event_id);
			return array($event_id);
		}

		return array();
	}

}

?>