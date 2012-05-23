<?php
	
	/*
	 * The core function to spit out user-friendly rule HTML string
	 */
	function display_one_rule($one_rule)
	{
		global $user_id;
		global $modules_array;
		
		//UI parameter
		$ui_show_trigger_module_name = true;
		$ui_show_filter_module_name = false;
		$ui_show_action_module_name = false;
		
		$rule_id = $one_rule['rule_id'];
		$trigger_id = intval( $one_rule['trigger_id'] );
		$trigger_param = $one_rule['trigger_param'];
		$filter_id = intval( $one_rule['filter_id'] );
		$filter_param = $one_rule['filter_param'];
		$action_id = intval( $one_rule['action_id'] );
		$action_param = $one_rule['action_param'];
		$rule_enabled = intval($one_rule['rule_enabled']);
		$rule_description = $one_rule['rule_description'];
		
		$pretty_end_string = "    </li>\n";
		echo "    <li>\n";
		
		//Trigger module
		$trigger_module = func_getModuleByTriggerID( $trigger_id );
		if ($trigger_module == null) {
			echo "		rule error: trigger module $trigger_id does not exist.<br/>\n";
			echo $pretty_end_string;
			return;
		}
		echo "      If";
		if ($ui_show_trigger_module_name)
			echo " (" . formatted_name_string($trigger_module['module_name']) . "):";
		echo display_param_string($trigger_module['trigger_name'], $trigger_param, ",");
		
		//Filter module
		if ($filter_id <= 0)
		{
			echo " ";
		}
		else
		{
			$filter_module = func_getModuleByFilterID( $filter_id );
			if ($filter_module == null) {
				echo "		rule error: filter module $filter_id does not exist.<br/>\n";
				echo $pretty_end_string;
				return;
			}
			
			echo "\n      while";
			if ($ui_show_filter_module_name)
				echo " (" . formatted_name_string($filter_module['module_name']) . "):";
			echo display_param_string($filter_module['filter_name'], $filter_param, ",");
		}
		
		//Action module
		$action_module = func_getModuleByActionID( $action_id );
		if ($action_module == null) {
			echo "		rule error: action module $action_id does not exist.<br/>\n";
			echo $pretty_end_string;
			return;
		}
		echo "\n      then";
		if ($ui_show_action_module_name)
			echo " (" . formatted_name_string($action_module['module_name']) . "):";
		echo display_param_string($action_module['action_name'], $action_param, ".<br/>\n");
		
		//Other configuration parameters
		if ($rule_enabled != 0)		echo "      Enabled: <strong>Yes</strong><br/>\n";
		else						echo "      Enabled: <strong>No</strong><br/>\n";
		if (strlen($rule_description) > 0)
			echo "      Description: $rule_description<br/>\n";
		
		echo $pretty_end_string;
	}
	
	function clean_param_json_string($params_json)
	{
		//return str_replace('\\"', '"', $params_json);
		return $params_json;
	}
	
	function formatted_name_string($name)
	{
		return '<span class="styled-module-name">' . $name . '</span>';
	}
	
	function formatted_param_value_string($name)
	{
		return '<span class="styled-param_value">' . $name . '</span>';
	}
	
	function display_param_string($name, $param, $end_string)
	{
		if ($param == "" || $param == null)
			return " " . $name . $end_string;
			
		$return_string = " " . formatted_name_string($name) . ' ';
		$parameters = json_decode(clean_param_json_string($param), true);
		if (gettype($parameters) != "array") {
			$return_string .= '"' . formatted_param_value_string($param). '"' . $end_string;
			return rtrim($return_string);
		}

		//display parameters one-by-one
		foreach ($parameters as $key => $value) {
			//hide sensitive information
			if (func_validEmail($value)) {
				$indexOfAt = strpos($value, "@")+1;
				$value = substr($value, 0, $indexOfAt) . "xxxxxxxxxxxx";
			}
			else if (strlen($value) > 50)
				$value = substr($value, 0, 50) . '...';
			$return_string .= ' <strong>' . $key . '</strong> ' . formatted_param_value_string($value) . ',';
		}
		if (func_endsWith($return_string, ','))
			$return_string = substr($return_string,0,-1);
		$return_string .= $end_string;
			
		return rtrim($return_string);
	}
?>