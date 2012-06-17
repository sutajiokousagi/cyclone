<?php
/*
- Created by Torin
- This is a Scheduler module that generate trigger/triggers on repeated schedule
*/

require_once("baseclass_trigger.php");

class trigger_scheduler extends baseclass_trigger
{
	const ONCE_TRIGGER_ALIAS = "once";
	const DAILY_TRIGGER_ALIAS = "daily";
	const WEEKLY_TRIGGER_ALIAS = "weekly";
	
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
		/*
		 * No headless trigger for this module
		 */
		
		/*
		 * Implement all headless triggers above this line
		 */
		if ($trigger_param_array == null) {
			echo "error: trigger_param_array is empty, trigger_alias: " . $trigger_alias;
			return array();
		}
		$rule_trigger_param = json_encode($trigger_param_array);

		/*
		   Parameter:
			$datetime in "2012-01-31 20:35" format
		 */
		if ($trigger_alias == self::ONCE_TRIGGER_ALIAS) {
			$datetime = null;
			foreach ($trigger_param_array as $key => $value) {
				if ($key == 'datetime')		$datetime = trim($value);
			}
			echo "trigger: $trigger_alias, rule input: $datetime, dynamic input: " . date("Y-m-d H:i") . "<br/>";
			if ($datetime == null || $datetime != date("Y-m-d H:i"))
				return array();
			$event_id = $this->add_event($trigger_alias, $rule_trigger_param, $rule_id);
			$this->prettyPrintout($trigger_alias, $event_id);
			return array($event_id);
		}

		/*
		   Parameter:
			$time in hh:mm (24 hours format, with leading zero)
		 */
		if ($trigger_alias == self::DAILY_TRIGGER_ALIAS) {
			$time = null;
			foreach ($trigger_param_array as $key => $value) {
				if ($key == 'time')		$time = preg_replace('/\s+/', '', $value);
			}
			echo "trigger: $trigger_alias, rule input: $time, dynamic input: " . date("H:i") . "<br/>";
			if ($time == null || $time != date("H:i"))
				return array();
			$event_id = $this->add_event($trigger_alias, $rule_trigger_param, $rule_id);
			$this->prettyPrintout($trigger_alias, $event_id);
			return array($event_id);
		}
		
		/*
		   Parameter:
			$days is a comma-seperated list of 3-letter day name in a week eg. mon,wed,fri,sun
			$time in hh:mm (24 hours format, with leading zero)
		 */
		if ($trigger_alias == self::WEEKLY_TRIGGER_ALIAS) {
			$day = null;
			$time = null;
			foreach ($trigger_param_array as $key => $value) {
				if ($key == 'days')		$days = preg_replace('/\s+/', '', $value);
				if ($key == 'time')		$time = preg_replace('/\s+/', '', $value);
			}
			echo "trigger: $trigger_alias, rule input: ".strtolower($days).", $time, dynamic input: " . strtolower(date("D")) . "," . date("H:i") . "<br/>";
			if ($days == null || $time == null)
				return array();
			if (!func_contains(strtolower($days), strtolower(date("D"))))
				return array();
			if ($time != date("H:i"))
				return array();
			$event_id = $this->add_event($trigger_alias, $rule_trigger_param, $rule_id);
			$this->prettyPrintout($trigger_alias, $event_id);
			return array($event_id);
		}
		
		return array();
	}

	/*
	 * Return JavaScript code for given trigger alias
	 */
	public function getUICodeForTriggerAlias($trigger_alias)
	{
		if ($trigger_alias == self::ONCE_TRIGGER_ALIAS)
		{

		}
		else if ($trigger_alias == self::DAILY_TRIGGER_ALIAS)
		{
			$html_string =
<<<END_OF_STRING_IDENTIFIER
				<link type="text/css" rel="stylesheet" href="./bootstrap/css/timepicker.css">
				<script type='text/javascript' src='./bootstrap/js/bootstrap-timepicker.js'></script>
				<input type="text" id="trigger_param_time_timepicker" class="dropdown-timepicker"/>
				<script>
					$('#trigger_param_time_timepicker').timepicker({
		                defaultTime: 'current',
		                minuteStep: 1,
		                disableFocus: true,
		                template: 'modal'
		            });
					$('#trigger_param_time_timepicker').change(function() {
						var newValue = $('#trigger_param_time_timepicker').val();

						//Convert timepicker's format to our format
						var hour = parseInt( newValue.split(":")[0] );
						var minute = parseInt( newValue.split(":")[1].split(" ")[0] );
						var ampm = newValue.split(" ")[1].toLowerCase();
						if (ampm == "pm")
							hour += 12;
						var time24hr = "" + hour + ":" + minute;
						console.log(time24hr);

  						$('#trigger_param_time').text(time24hr);
					});
					$('#trigger_param_time_timepicker').change();
				</script>
END_OF_STRING_IDENTIFIER;

			return $html_string;
		}
		else if ($trigger_alias == self::WEEKLY_TRIGGER_ALIAS)
		{
			
		}
		return "";
	}
}

?>