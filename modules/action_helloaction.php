<?php
/*
- Created by Torin
- This is a simple HelloAction module that contains simple echo & default mail function
*/

require_once("baseclass_action.php");
require_once("util_smtp.php");

class action_helloaction extends baseclass_action
{
	const ECHO_ACTION_NAME = "echo";
	const MAIL_ACTION_NAME = "email";
		
	/*
	 * Override superclass to return my own version number
	 */
	public function getModuleVersion()
	{
		return "1.0";
	}
	
	protected function action_by_name($action_alias, $event_params_array, $action_params_array)
	{
		/*
		 * This module has no headless action
		 */
		
		
		
		/*
		 * Implement all headless actions above this line
		 */
		if ($action_params_array == null) {
			echo "error: event_param is empty<br/>\n";
			return null;
		}
		
		//Replace special tags with dynamic variables
		if ($event_params_array != null) {
			foreach ($event_params_array as $dyn_key => $dyn_value) {
				$dyn_key = "[" . $dyn_key . "]";
				foreach ($action_params_array as $key => $value) {
					$action_params_array[$key] = str_replace($dyn_key, $dyn_value, $value);
				}
			}
		}
		
		if ($action_alias == self::ECHO_ACTION_NAME)
			return $this->action_echo($event_params_array, $action_params_array);
			
		if ($action_alias == self::MAIL_ACTION_NAME)
			return $this->action_mail($event_params_array, $action_params_array);
		
		echo "error: action_alias '$action_alias' is not supported<br/>\n";
		return null;
	}

	/*
	   Parameter:
		$text in free format
	 */
	protected function action_echo($event_params_array, $action_params_array)
	{
		//Action parameters
		$text = null;
		foreach ($action_params_array as $key => $value)
			if ($key == 'text')
				$text = $value;
		
		//Safety
		if ($text == null)
			return null;
			
		echo "$text<br/>\n";
		
		$action_params_array['success'] = true;
		return $action_params_array;
	}
	
	/*
	   Parameter:
		$to in free email format
		$subject is a 1 line short string
		$message is a long text paragraph(s)
	 */
	protected function action_mail($event_params_array, $action_params_array)
	{
		$to = null;
		$subject = null;
		$message = null;
		
		//Action parameters
		foreach ($action_params_array as $key => $value) {
			if ($key == 'to')			$to = preg_replace('/\s+/', '', $value);
			if ($key == 'subject')		$subject = trim($value);
			if ($key == 'message')		$message = $value;
		}
		
		//Safety
		if ($to == null || ($subject == null && $message == null))
			return null;
		
		//$success = func_mail($to, $subject, $message);
		$success = func_customMail($to, $subject, $message);
		
		$action_params_array['success'] = $success;
		return $action_params_array;
	}
}

?>