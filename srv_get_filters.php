<?

/*
- Created by Torin
*/
	
	require_once("util_filters.php");
	
	$module_id = null;
	if (isset($_POST['module_id']))
		$module_id = $_POST['module_id'];
	if (isset($_GET['module_id']))
		$module_id = $_GET['module_id'];
		
	if ($module_id == null || $module_id == "")
		func_dieWithMessage("module_id is required");
		
	//--------------------------------------------------------
	
	$sql_result = func_getFiltersByModuleID( $module_id );
	func_outputSqlArrayResult($sql_result);	

?>