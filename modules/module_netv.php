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
	const DIGITAL_CHANGE_TRIGGER_NAME = "digital_input_change";
	const ANALOG_CHANGE_TRIGGER_NAME = "analog_input_change";
	
	const NUM_DIGITAL_INPUT = 8;
	const NUM_ANALOG_INPUT = 8;
	
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
	protected function trigger_by_alias_external($trigger_alias, $trigger_param_array)
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
					
		if ($trigger_alias == self::DIGITAL_CHANGE_TRIGGER_NAME)
			return $this->trigger_group_digital_input_change($trigger_alias, $trigger_param_array);
		if ($trigger_alias == self::DIGITAL_ON_TRIGGER_NAME)
			return $this->trigger_single_digital_input($trigger_alias, $trigger_param_array, true);
		if ($trigger_alias == self::DIGITAL_OFF_TRIGGER_NAME)
			return $this->trigger_single_digital_input($trigger_alias, $trigger_param_array, false);
		if ($trigger_alias == self::ANALOG_CHANGE_TRIGGER_NAME)
			return $this->trigger_group_analog_input($trigger_alias, $trigger_param_array);
			
		return null;
	}
	
	protected function trigger_group_digital_input_change($trigger_alias, $trigger_param_array)
	{

		//Validate parameters
		$current = null;
		$previous = null;
		foreach ($trigger_param_array as $key => $value) {
			if ($key == 'previous')		$previous = intval($value);
			if ($key == 'current')		$current = intval($value);
		}
		if ($current === null || $previous === null)
			return null;
		
		//Add new event to global async event queue
		$rule_id = 0;	//global
		$event_ids = array();
		
		for ($index=0; $index<self::NUM_DIGITAL_INPUT; $index++)
		{
			$mask = 1 << $index;
			$temp_previous = $previous & $mask;
			$temp_current = $current & $mask;
			if ($temp_previous == $temp_current)
				continue;

			$event_param_array = array();
			$event_param_array['channel'] = $index;
			$event_param_array['current'] = $temp_current;
			$event_param_array['previous'] = $temp_previous;
			$event_param_json = json_encode($event_param_array);
			
			//generic change event
			$event_ids[] = $this->add_event_async($trigger_alias, $event_param_json, $rule_id);
			
			//specific change event
			if ($temp_current == 0)
				$event_ids[] = $this->add_event_async(self::DIGITAL_OFF_TRIGGER_NAME, $event_param_json, $rule_id);
			else
				$event_ids[] = $this->add_event_async(self::DIGITAL_ON_TRIGGER_NAME, $event_param_json, $rule_id);
		}
		return $event_ids;
	}

	protected function trigger_single_digital_input($trigger_alias, $trigger_param_array, $isOn)
	{
		//Validate parameters
		$channel = null;
		foreach ($trigger_param_array as $key => $value) {
			if ($key == 'channel')		$channel = intval($value);
		}
		if ($channel === null)
			return null;
			
		//Add new event to global async event queue
		$rule_id = 0;	//global
		$trigger_param_json = json_encode($trigger_param_array);
		$event_id = $this->add_event_async($trigger_alias, $trigger_param_json, $rule_id);
		return array($event_id);
	}
	
	protected function trigger_group_analog_input($trigger_alias, $trigger_param_array)
	{
		//Validate parameters
		$current = null;
		$previous = null;
		foreach ($trigger_param_array as $key => $value) {
			if ($key == 'previous')		$previous = $value;
			if ($key == 'current')		$current = $value;
		}
		if ($current === null || $previous === null)
			return null;
			
		//Add new event to global async event queue
		$rule_id = 0;	//global
		$event_ids = array();
		$previous_array = explode("-", $previous);
		$current_array = explode("-", $current);
		
		for ($index=0; $index<self::NUM_ANALOG_INPUT; $index++)
		{
			if ($previous_array[$index] == $current_array[$index])
				continue;
			$temp_current = hexdec($current_array[$index]);
			$temp_previous = hexdec($previous_array[$index]);
			$diff = abs($temp_current - $temp_previous);
			if ($diff <= 13)		//%5 of 255
				continue;
				
			$event_param_array = array();
			$event_param_array['channel'] = $index;
			$event_param_array['current'] = $temp_current;
			$event_param_array['previous'] = $temp_previous;
			$event_param_array['difference'] = $diff;
			$event_param_json = json_encode($event_param_array);
			
			//generic change event
			$event_ids[] = $this->add_event_async($trigger_alias, $event_param_json, $rule_id);
		}
		
		return $event_ids;
	}
	
	/*
	 * Return JavaScript code for given trigger alias
	 */
	public function getUICodeForTriggerAlias($trigger_alias)
	{
		if ($trigger_alias == self::DIGITAL_CHANGE_TRIGGER_NAME
			|| $trigger_alias == self::DIGITAL_ON_TRIGGER_NAME
			|| $trigger_alias == self::DIGITAL_OFF_TRIGGER_NAME
			|| $trigger_alias == self::ANALOG_CHANGE_TRIGGER_NAME)
		{
			$html_string =
<<<END_OF_STRING_IDENTIFIER
				Channel <div class='btn-group' data-toggle='buttons-radio'>
					<button onclick='fOnTriggerParamChannel(0);' class='btn btn-primary' id='trigger_param_channel_0'>0</button>
					<button onclick='fOnTriggerParamChannel(1);' class='btn btn-primary' id='trigger_param_channel_1'>1</button>
					<button onclick='fOnTriggerParamChannel(2);' class='btn btn-primary' id='trigger_param_channel_2'>2</button>
					<button onclick='fOnTriggerParamChannel(3);' class='btn btn-primary' id='trigger_param_channel_3'>3</button>
					<button onclick='fOnTriggerParamChannel(4);' class='btn btn-primary' id='trigger_param_channel_4'>4</button>
					<button onclick='fOnTriggerParamChannel(5);' class='btn btn-primary' id='trigger_param_channel_5'>5</button>
					<button onclick='fOnTriggerParamChannel(6);' class='btn btn-primary' id='trigger_param_channel_6'>6</button>
					<button onclick='fOnTriggerParamChannel(7);' class='btn btn-primary' id='trigger_param_channel_7'>7</button>
				</div>
				<script>
					fOnTriggerParamChannel( 0 );
					$('#trigger_param_channel_0').button('toggle');
					function fOnTriggerParamChannel(newValue) {
						$('#trigger_param_channel').val( newValue );
						console.log('channel ' + newValue + ' selected');
					}
				</script>
END_OF_STRING_IDENTIFIER;

			return $html_string;
		}
		
		return "";
	}
	
	/*
	 * Return JavaScript code for given action alias
	 */
	public function getUICodeForActionAlias($action_alias)
	{
		if ($action_alias == self::DIGITAL_OUT_ON_ACTION_NAME
			|| $action_alias == self::DIGITAL_OUT_OFF_ACTION_NAME)
		{
			$html_string =
<<<END_OF_STRING_IDENTIFIER
				Channel <div class='btn-group' data-toggle='buttons-radio'>
					<button onclick='fOnActionParamChannel(0);' class='btn btn-primary' id='action_param_channel_0'>0</button>
					<button onclick='fOnActionParamChannel(1);' class='btn btn-primary' id='action_param_channel_1'>1</button>
					<button onclick='fOnActionParamChannel(2);' class='btn btn-primary' id='action_param_channel_2'>2</button>
					<button onclick='fOnActionParamChannel(3);' class='btn btn-primary' id='action_param_channel_3'>3</button>
					<button onclick='fOnActionParamChannel(4);' class='btn btn-primary' id='action_param_channel_4'>4</button>
					<button onclick='fOnActionParamChannel(5);' class='btn btn-primary' id='action_param_channel_5'>5</button>
					<button onclick='fOnActionParamChannel(6);' class='btn btn-primary' id='action_param_channel_6'>6</button>
					<button onclick='fOnActionParamChannel(7);' class='btn btn-primary' id='action_param_channel_7'>7</button>
				</div>
				<script>
					fOnActionParamChannel( 0 );
					$('#action_param_channel_0').button('toggle');
					function fOnActionParamChannel(newValue) {
						$('#trigger_param_channel').val( newValue );
						console.log('channel ' + newValue + ' selected');
					}
				</script>
END_OF_STRING_IDENTIFIER;

			return $html_string;
		}

		if ($action_alias == self::MOTOR_STOP_ACTION_NAME
			|| $action_alias == self::MOTOR_FORWARD_ACTION_NAME
			|| $action_alias == self::MOTOR_BACKWARD_ACTION_NAME)
		{
			$html_string =
<<<END_OF_STRING_IDENTIFIER
				Channel <div class='btn-group' data-toggle='buttons-radio'>
					<button onclick='fOnActionParamChannel(1);' class='btn btn-primary' id='action_param_channel_1'>1</button>
					<button onclick='fOnActionParamChannel(2);' class='btn btn-primary' id='action_param_channel_2'>2</button>
					<button onclick='fOnActionParamChannel(3);' class='btn btn-primary' id='action_param_channel_3'>3</button>
					<button onclick='fOnActionParamChannel(4);' class='btn btn-primary' id='action_param_channel_4'>4</button>
				</div>
				<script>
					fOnActionParamChannel( 1 );
					$('#trigger_param_channel_1').button('toggle');
					function fOnActionParamChannel(newValue) {
						$('#trigger_param_channel').val( newValue );
						console.log('motor channel ' + newValue + ' selected');
					}
				</script>
END_OF_STRING_IDENTIFIER;

			return $html_string;
		}
		
		return "";
	}
}

?>