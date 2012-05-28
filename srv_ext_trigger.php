<?php

/*
* Created by Torin
* This webservice is designed to accept 2 different form of input parameters
*    user_id,trigger_id are explicitly passed as GET/POST variables
*    all other GET/POST variables will be automatically combined into an array & passed to handler function
*/
	
	require_once("util_events.php");
	require_once("util_modules.php");
	require_once("util_rules.php");

	$input_parameters = array('user_id','trigger_id');
	$trigger_param_array = array();
	
	//--------------------------------------------------------
	// Basic validation
	
	$invalid_input_parameters = array();
	foreach ($input_parameters as $input_name)
	{
		$input_value = null;
		if (isset($_POST[$input_name]))
			$input_value = $_POST[$input_name];
		else if (isset($_GET[$input_name]))
			$input_value = $_GET[$input_name];
		if ($input_value == null || $input_value == "")
			$invalid_input_parameters[] = $input_name;
	}
	
	if (count($invalid_input_parameters) > 0)
		func_dieWithMessage( join(", ", $invalid_input_parameters) . " is/are required");
	
	//--------------------------------------------------------
	// Gather all other variables into an associative array

	if (!isset($_POST[$compound_param_name]) && !isset($_GET[$compound_param_name]))
	{
		$postOrGetArray = null;
		if (count($_POST) > 0)			$postOrGetArray = $_POST;
		else if (count($_GET) > 0)		$postOrGetArray = $_GET;
		foreach($postOrGetArray as $input_name => $input_value)
		{
			//Skip if already present in $input_parameters (effectively skipping user_id & trigger_id)
			$skip = false;
			foreach($input_parameters as $predefined_input_name)
				if ($input_name == $predefined_input_name)
					$skip = true;
					
			if ($skip)
				continue;
			$trigger_param_array[$input_name] = $input_value;
		}
		if (count($trigger_param_array) <= 0)
			$trigger_param_array = null;
	}

	//--------------------------------------------------------
	// Convert _POST or _GET parameters to individually named variables
	
	$user_id = null;
	$trigger_id = null;
	
	foreach ($input_parameters as $input_name)
	{
		$input_value = null;
		if (isset($_POST[$input_name]))
			$input_value = $_POST[$input_name];
		else if (isset($_GET[$input_name]))
			$input_value = $_GET[$input_name];
		
		if ($input_name == 'user_id')				$user_id = $input_value;
		else if ($input_name == 'trigger_id')		$trigger_id = $input_value;
	}
	
	//--------------------------------------------------------
	// Load the module to handle this external trigger
	
	$module_data_dictionary = func_getModuleByTriggerID($trigger_id);
	if ($module_data_dictionary == null)
		func_dieWithMessage("no module to handle this external trigger");
		
	$module_object = func_loadModule($user_id, $module_data_dictionary, false);
	if ($module_object == null)
		func_dieWithMessage("error loading module to handle this external trigger");

	$queue_id_array = $module_object->trigger_external($trigger_id, $trigger_param_array);
	if ($queue_id_array === null)
		func_dieWithMessage("module does not expect this external trigger");
	
	//--------------------------------------------------------
	// Since this is most likely an async/hardware/global event, we should process the event immediately
	
	$cron_async_url = currentPageURL();
	$index = strrpos($cron_async_url, '/') + 1;
	$path = substr($cron_async_url, 0, $index) ;
	$cron_async_url = $path . 'cron_async.php';
	
	//This doesn't seem to work as normal, it returns raw PHP file instead
	//$response = do_post_request($cron_async_url, null, null);		//correct path but gives error with url
	//$response = do_post_request('cron_async.php', null, null);
	
	$response = get_shell_output("curl $cron_async_url");
				
	//--------------------------------------------------------
	// Output
	
	$output_array = array();
	$count = count($queue_id_array);
	$output_array['queue_count'] = $count;
	$output_array['response'] = $response;
	if ($count > 0)
		$output_array['queue_ids'] = join(",", $queue_id_array);
	func_outputArray($output_array);
	
	


	function currentPageURL()
	{
		$pageURL = 'http';
		if ($_SERVER["HTTPS"] == "on") {$pageURL .= "s";}
		$pageURL .= "://";
		if ($_SERVER["SERVER_PORT"] != "80") {
			$pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
		} else {
			$pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
		}
		return $pageURL;
	}
	
	// Replacement for curl because we don't have cURL extension installed
	function do_post_request($url, $data, $optional_headers = null)
	{
  	  if ($data == null)
		$data = array();
	  $data = http_build_query($data);
	
	  $params = array('http' => array('method' => 'POST', 'content' => $data));
	  if ($optional_headers !== null)
	    $params['http']['header'] = $optional_headers;

	  $ctx = stream_context_create($params);
	  $fp = @fopen($url, 'rb', false, $ctx);
	  if (!$fp)
	    throw new Exception("Problem with $url, $php_errormsg");

	  $response = @stream_get_contents($fp);
	  if ($response === false)
	    throw new Exception("Problem reading data from $url, $php_errormsg");

	  return $response;
	}
	
	function get_shell_output($cmd)
	{
		return shell_exec($cmd);
	}
?>