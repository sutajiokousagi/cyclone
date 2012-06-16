<?

/*
- Created by Torin
*/
	
	require_once("util_actions.php");
	require_once("util_modules.php");
	require_once("util_configs.php");
	require_once("cron_helper.php");

	//--------------------------------------------------------
	// Validation

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
	// Load module code

	$module_sql = null;
	$config_sql = null;
	$modules_array = array();
	$configs_array = array();

	if ($module_id != null)
		$module_sql = func_getModuleByID( $module_id );
	else if ($module_alias != null)
		$module_sql = func_getModuleByAlias( $module_alias );
	
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

	//--------------------------------------------------------
	// Get action UI code

	$outputArray = array();
	$actions_sql = func_getActionsByModuleID( $module_id );
	
	//For each action, get its UI code
	while ($one_action = mysql_fetch_assoc($actions_sql))
	{
		$action_alias = $one_action['action_alias'];
		$action_ui = $module_object->getUICodeForActionAlias($action_alias);
		if ($action_ui != null && strlen($action_ui) > 5)
			$one_action['action_ui'] = $action_ui;
		$outputArray[] = $one_action;		
	}
	
	//--------------------------------------------------------
	// Output

	func_outputArray( $outputArray );

?>