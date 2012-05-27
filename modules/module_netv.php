<?php
/*
- Created by Torin
- This is a NeTV module that provides triggers and actions for NeTV motor controller board
- See documentation here: http://www.kosagi.com/blog/motor-controller-board/
*/

require_once("baseclass_hybrid.php");
require_once("util_preferences.php");

class module_netv extends baseclass_hybrid
{
	const DIGITAL_OUT_ON_ACTION_NAME = "digital_out_on";
	const DIGITAL_OUT_OFF_ACTION_NAME = "digital_out_off";
	const MOTOR_STOP_ACTION_NAME = "motor_stop";
	const MOTOR_FORWARD_ACTION_NAME = "motor_forward";
	const MOTOR_BACKWARD_ACTION_NAME = "motor_backward";
	
	const DIGITAL_ON_TRIGGER_NAME = "digital_input_on";
	const DIGITAL_OFF_TRIGGER_NAME = "digital_input_off";
	const ANALOG_CHANGE_TRIGGER_NAME = "analog_input_change";
	
	/*
	 * Override superclass to return my own version number
	 */
	public function getModuleVersion()
	{
		return "1.0";
	}
	
	protected function setup_firmware()
	{
		//New: let the daemon perform firmware setup,
		//otherwise we have a lag for every trigger/action here
		return true;
		
		//See documentation here: http://www.kosagi.com/blog/motor-controller-board/
		
		//Check already setup
		$firmware_version = $this->get_shell_output("mot_ctl V");
		if (strlen($firmware_version) < 10)
			return true;
					
		/* If success, board is connected, it should output these (268 characters)
		// If failed, it will show more error messages
		Setting 0xd420b1a8: 0x90000001 -> 0x90000001 ok
		installing chumby xilinx driver
		configuring FPGA
		Note configuration: motor driver on input side, 720p video on output side
		Setting 0xd420b1c8: 0x00000000 -> 0x00000000 ok
		Setting 0xd420b1b8: 0x210ff003 -> 0x210ff003 ok
		1
		*/
		
		//Board is not connected
		$netv_service = $this->get_shell_output("/etc/init.d/netv_service motor");
		if (strlen($netv_service) > 300 || func_contains($netv_service, "error")) {
			echo "NeTV motor controller board is not connected <br/>\n";
			return false;
		}

		//Double check
		$firmware_version = $this->get_shell_output("mot_ctl V");
		if (strlen($firmware_version) < 10)
			return true;
		
		echo "Error initializing motor controller firmware<br/>\n";
		return false;
	}
	
	private function get_shell_output($cmd)
	{
		return shell_exec($cmd);
	}
	
	private function get_preference_name($prefix, $username)
	{
		$url = preg_replace("/[^a-zA-Z0-9s]/", "", $user);
		$pref_name = "" . $this->module_id . "_previous_" . $prefix . "_" . $username;
		return $pref_name;
	}
	
	private function get_previous_array($prefix, $username)
	{
		//Get previous remembered feed entries & convert it to an associative array
		$pref_name = $this->get_preference_name($prefix, $username);
		$pref_value = func_getPreference($this->user_id, $pref_name);
		$param_array = null;
		if ($pref_value != null)
			$param_array = json_decode(func_json_clean_param_string($pref_value), true);
		return $param_array;
	}
	
	private function save_new_array($prefix, $username, $current_array)
	{
		$pref_name = $this->get_preference_name($prefix, $username);
		func_setPreference($this->user_id, $pref_name, json_encode($current_array));
	}
	
	
	//---------------------------------------------------------------------------------------------------
	//---------------------------------------------------------------------------------------------------
	//---------------------------------------------------------------------------------------------------

	protected function action_by_name($action_alias, $event_params_array, $action_params_array)
	{
		/*
		 * This module has no headless action
		 */


		
		/*
		 * Implement all headless actions above this line
		 */
		if ($action_params_array == null) {
			echo "error: action_param is empty<br/>\n";
			return null;
		}
		
		//Setup NeTV motor firmware
		$firmware_ok = $this->setup_firmware();
		if (!$firmware_ok)
			return null;
		
		//Replace special tags with dynamic variables
		if ($event_params_array != null) {
			foreach ($event_params_array as $dyn_key => $dyn_value) {
				$dyn_key = "[" . $dyn_key . "]";
				foreach ($action_params_array as $key => $value) {
					$action_params_array[$key] = str_replace($dyn_key, $dyn_value, $value);
				}
			}
		}
				
		if ($action_alias == self::DIGITAL_OUT_ON_ACTION_NAME)
			return $this->action_digital_output($event_params_array, $action_params_array, true);
		if ($action_alias == self::DIGITAL_OUT_OFF_ACTION_NAME)
			return $this->action_digital_output($event_params_array, $action_params_array, false);
		if ($action_alias == self::MOTOR_STOP_ACTION_NAME)
			return $this->action_motor_control($event_params_array, $action_params_array, 0);
		if ($action_alias == self::MOTOR_FORWARD_ACTION_NAME)
			return $this->action_motor_control($event_params_array, $action_params_array, null);
		if ($action_alias == self::MOTOR_BACKWARD_ACTION_NAME)
			return $this->action_motor_control($event_params_array, $action_params_array, null);

		echo "error: action_alias $action_alias is not supported<br/>\n";
		return null;
	}

	/*
	   Parameter:
		$channel is 0 to 7
	 */
	protected function action_digital_output($event_params_array, $action_params_array, $on)
	{
		$channel = null;
		foreach ($action_params_array as $key => $value) {
			if ($key == 'channel')		$channel = intval($value);
		}
		if ($channel === null)
			return null;
		if ($channel < 0 || $channel > 7)
			return null;
		
		//u [ch] [val] set digital output bit [ch] to value [val]
		$set_cmd = "mot_ctl u " . $channel . " " . ($on ? "1" : "0");	//active low
		$output = $this->get_shell_output($set_cmd);					//no output for this command
		
		//i [ch]  print the value of channel [ch]
		$get_cmd = "mot_ctl i " . $channel;
		$output = $this->get_shell_output($get_cmd);					//0 or 1, this command doesn't seem to work
		
		//Verify failed
		//if (intval($output) != ($on ? 1 : 0))
		//	return false;

		return true;
	}
	
	/*
	   Parameter:
		$channel is 0 to 7
		$speed is -100 to +100 percent
	 */
	protected function action_motor_control($event_params_array, $action_params_array, $speed_override)
	{
		$channel = null;
		foreach ($action_params_array as $key => $value) {
			if ($key == 'channel')		$channel = intval($value);
			if ($key == 'speed')		$speed = intval($value);
		}
		if ($channel === null)
			return null;
		if (isset($speed_override) && $speed_override !== null)
			$speed = $speed_override;
		if ($speed === null)
			return null;
		if ($channel < 1 || $channel > 4)
			return null;
		if ($speed < -100 || $speed > 100)
			return null;
		
		//Stop: m [ch] [cmd] motor command for channel ch1-4. cmd = [f,r,s] -> forward, reverse, stop
		if ($speed == 0 || $stop == true)
		{
			$set_cmd = "mot_ctl m " . $channel . " s";
			$output = $this->get_shell_output($set_cmd);		//no output for this command
			
			//Just to be sure
			$set_cmd = "mot_ctl p " . $channel . " 0";
			$output = $this->get_shell_output($set_cmd);		//no output for this command
			return true;
		}
				
		//Direction: m [ch] [cmd] motor command for channel ch1-4. cmd = [f,r,s] -> forward, reverse, stop
		$set_cmd = "mot_ctl m " . $channel . " " . ($speed > 0 ? "f" : "r");
		$output = $this->get_shell_output($set_cmd);			//no output for this command
		
		//Speed: p [ch] [dc] PWM duty cycle for channel ch1-4 and duty cycle dc0-255
		$duty_cycle = round( abs($speed / 100.0 * 255) );
		$set_cmd = "mot_ctl p " . $channel . " " . $duty_cycle;
		$output = $this->get_shell_output($set_cmd);			//no output for this command
	
		return true;
	}
	
	//---------------------------------------------------------------------------------------------------
	//---------------------------------------------------------------------------------------------------
	//---------------------------------------------------------------------------------------------------
	
	/*
	 * Perform a trigger by its alias
	 * We use alias instead of ID for portability & coder-friendly purposes
	 * Subclass to override this function and perform their magic
	 * $rule_trigger_param is optional depending on the trigger implementation
	 */
	protected function trigger_by_alias($trigger_alias, $trigger_param_array, $rule_id)
	{	
		//Setup NeTV motor firmware
		$firmware_ok = $this->setup_firmware();
		if (!$firmware_ok)
			return null;
			
		/*
		 * Implement all headless triggers above this line
		 */
		if ($trigger_param_array == null) {
			echo "error: trigger_param is empty<br/>\n";
			return null;
		}
		
		if ($action_alias == self::DIGITAL_ON_TRIGGER_NAME)
			return $this->trigger_digital_input($event_params_array, $action_params_array, true);
		if ($action_alias == self::DIGITAL_OFF_TRIGGER_NAME)
			return $this->trigger_digital_input($event_params_array, $action_params_array, false);
		if ($action_alias == self::ANALOG_CHANGE_TRIGGER_NAME)
			return $this->trigger_analog_input($event_params_array, $action_params_array);
			
		return null;
	}
	
	protected function trigger_digital_input($trigger_alias, $trigger_param_array, $isOn)
	{
		$channel = null;
		foreach ($trigger_param_array as $key => $value) {
			if ($key == 'channel')		$channel = intval($value);
		}
		if ($channel === null)
			return null;
			
		return null;
	}
	
	protected function trigger_analog_input($trigger_alias, $trigger_param_array)
	{
		$channel = null;
		$previous = null;
		$current = null;
		foreach ($trigger_param_array as $key => $value) {
			if ($key == 'channel')		$channel = intval($value);
			if ($key == 'previous')		$previous = intval($value);
			if ($key == 'current')		$current = intval($value);
		}
		if ($channel === null || $previous === null || $current === null)
			return null;
		
		return null;
	}
}

?>