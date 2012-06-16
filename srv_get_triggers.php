<?php

/*
- Created by Torin
*/
	
	require_once("util_triggers.php");
	require_once("util_modules.php");
	require_once("util_configs.php");
	require_once("cron_helper.php");

	//--------------------------------------------------------
	
	$user_id = null;
	$module_id = null;
	$module_alias = null;
	if (isset($_POST['user_id']))		$user_id = $_POST['user_id'];
	if (isset($_GET['user_id']))		$user_id = $_GET['user_id'];
	if (isset($_POST['module_id']))		$module_id = $_POST['module_id'];
	if (isset($_GET['module_id']))		$module_id = $_GET['module_id'];
	if (isset($_POST['module_alias']))	$module_alias = $_POST['module_alias'];
	if (isset($_GET['module_alias']))	$module_alias = $_GET['module_alias'];

	if ($user_id == null)
		func_dieWithMessage("user_id is required");
	if ($module_id == null && $module_alias == null)
		func_dieWithMessage("module_id or module_alias is required");

	//--------------------------------------------------------

	$module_sql = null;
	$config_sql = null;
	$modules_array = array();
	$configs_array = array();

	if ($module_id != null) {
		$triggers_sql = func_getTriggersByModuleID( $module_id );
		$module_sql = func_getModuleByID( $module_id );
	}

	else if ($module_alias != null) {
		$triggers_sql = func_getTriggersByModuleAlias( $module_alias );
		$module_sql = func_getModuleByAlias( $module_alias );
	}

	//Basic module data	
	$module_id = $module_sql['module_id'];
	$module_name = $module_sql['module_name'];
	$modules_array[$module_id] = $module_sql;

	//User configs
	$config_sql = func_getConfigsByModuleIDAndUserID( $module_id );
	$configs_array[$module_id] = $config_sql;


	//Load the module
	$module_loaded = load_module( $module_sql, false );
	if ($module_loaded == false) {
		echo "failed to load module";
		die();
	}
	$one_module = $modules_array[$module_id];
	$module_object = $one_module['module_object'];

	$outputArray = array();

	//For each trigger, get its UI code
	while ($one_trigger = mysql_fetch_assoc($triggers_sql))
	{
		$trigger_alias = $one_trigger['trigger_alias'];
		$trigger_ui = $module_object->getUICodeForTriggerAlias($trigger_alias);
		if ($trigger_ui != null && strlen($trigger_ui) > 5)
			$one_trigger['trigger_ui'] = $trigger_ui;
		$outputArray[] = $one_trigger;		
	}
	
	//--------------------------------------------------------

	func_outputArray( $outputArray );

?>