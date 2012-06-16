// -------------------------------------------------------------------------------------------------
//	data storage
// -------------------------------------------------------------------------------------------------

var user_id = null;
var triggers_data = null;
var filters_data = null;
var actions_data = null;

// -------------------------------------------------------------------------------------------------
//	window.onload function
// -------------------------------------------------------------------------------------------------
function fOnBodyLoad(usr_id)  {
	user_id = usr_id;
	fConsoleLog("fOnBodyLoad(" + user_id + ");");
	
	$('#btn_show_rules').click( function(){ fOnBtnShowRules($(this)) } );
	$('#btn_add_rule').click( function(){ fOnBtnAddRule($(this)) } );
	$('#btn_rule_submit').click( function(){ fOnBtnRuleSubmit($(this)) } );
	
	fOnTriggerModuleSelectorChanged( $('#trigger_module_id') );
	fOnFilterModuleSelectorChanged( $('#filter_module_id') );
	fOnActionModuleSelectorChanged( $('#action_module_id') );
	$('#trigger_module_id').live('change', function(){ fOnTriggerModuleSelectorChanged($(this)) } );
	$('#trigger_id').live('change', function(){ fOnTriggerSelectorChanged($(this)) } );
	$('#filter_module_id').live('change', function(){ fOnFilterModuleSelectorChanged($(this)) } );
	$('#filter_id').live('change', function(){ fOnFilterSelectorChanged($(this)) } );
	$('#action_module_id').live('change', function(){ fOnActionModuleSelectorChanged($(this)) } );
	$('#action_id').live('change', function(){ fOnActionSelectorChanged($(this)) } );
}

// -------------------------------------------------------------------------------------------------
//	UI Triggers
// -------------------------------------------------------------------------------------------------

function fOnBtnShowRules(sender)
{
	var isVisible = $('#panel_all_rules').css('display') != 'none';
	if (isVisible) {
		$('#btn_show_rules').attr({src:"images/list.png"});
		$('#panel_all_rules').hide('fast', function() {   /* animation complete */  });
	}
	else {
		$('#btn_show_rules').attr({src:"images/minus.png"});
		$('#panel_all_rules').show('fast', function() {   /* animation complete */  });		
	}
}

function fOnBtnAddRule(sender)
{
	var isVisible = $('#panel_add_rule').css('display') != 'none';
	if (isVisible) {
		$('#btn_add_rule').attr({src:"images/add.png"});
		$('#panel_add_rule').hide('fast', function() {   /* animation complete */  });
	}
	else {
		$('#btn_add_rule').attr({src:"images/minus.png"});
		$('#panel_add_rule').show('fast', function() {   /* animation complete */  });
	}
}

function fOnTriggerModuleSelectorChanged(sender)
{
	var trigger_module_id = sender.val();
	fSelectTriggerModuleID(trigger_module_id);
}
function fOnTriggerSelectorChanged(sender)
{
	var trigger_id = sender.val();
	fSelectTriggerID(trigger_id);
}
function fOnFilterModuleSelectorChanged(sender)
{
	var filter_module_id = sender.val();
	fSelectFilterModuleID(filter_module_id);
}
function fOnFilterSelectorChanged(sender)
{
	var filter_id = sender.val();
	fSelectFilterID(filter_id);
}
function fOnActionModuleSelectorChanged(sender)
{
	var action_module_id = sender.val();
	fSelectActionModuleID(action_module_id);
}
function fOnActionSelectorChanged(sender)
{
	var action_id = sender.val();
	fSelectActionID(action_id);
}



// -------------------------------------------------------------------------------------------------
//	Trigger/Triggers
// -------------------------------------------------------------------------------------------------

function fSelectTriggerModuleID(trigger_module_id)
{
	fConsoleLog("trigger_module_id selected: " + trigger_module_id);
	
	//hide selector to the bottom
	$('#trigger_selector_wrapper').fadeOut('fast', function() {   /* animation complete */  });
	$('#trigger_parameter_wrapper').fadeOut('fast', function() {   /* animation complete */  });
	
	//AJAX load
	fLoadTriggersForModuleID(trigger_module_id);
	
	//Special case
	$('#filter_module_selector_wrapper').fadeIn('fast', function() {   /* animation complete */  });
	$('#action_module_selector_wrapper').fadeIn('fast', function() {   /* animation complete */  });
}

function fLoadTriggersForModuleID(trigger_module_id)
{
	var url = 'srv_get_triggers.php?user_id=' + user_id + '&module_id=' + trigger_module_id;
	$.getJSON(url, function(data){ fOnReceiveTriggersForModuleID(trigger_module_id, data) } );
}

function fOnReceiveTriggersForModuleID(trigger_module_id, jsonData)
{
	if (!fValidateWebserviceOutput(jsonData))
		return;
	var ws_data = jsonData.ws_data;
	
	triggers_data = null;
	triggers_data = new Array();

	var html_string = "";
	var first_id = -1;
	for (var i=0; i<ws_data.length; i++)
	{
		//To output this: <option value='$trigger_id'>$trigger_name</option>\n
		var one_data = ws_data[i];
		var the_id = one_data['trigger_id'];
		triggers_data[""+the_id] = one_data;
		html_string += "<option value='" + the_id + "'";
		if (first_id < 0) {
			html_string += " selected='selected'";
			first_id = the_id;
		}
		html_string += ">" + one_data['trigger_name'] + "</option>\n";
	}
	
	//Inject new select-options and display them
	$('#trigger_selector_wrapper select').html(html_string);
	$('#trigger_selector_wrapper').fadeIn('fast', function() {   /* animation complete */  });
	
	//Select the first option by default
	if (first_id >= 0)
		fSelectTriggerID(first_id);
}

function fSelectTriggerID(trigger_id)
{
	fConsoleLog("trigger_id selected: " + trigger_id);
	paramsArray = fGetTriggerParams(trigger_id);
	paramsUI = fGetTriggerUI(trigger_id);
		
	//No parameters
	if (paramsArray == null) {
		$('#trigger_parameter_wrapper').fadeOut('fast', function() {   $('#trigger_parameter_wrapper').html('');  });
		return;
	}

	fConsoleLog(paramsUI);

	//For conveninent testing of UI code
	/*
	if (trigger_id >= 27 && trigger_id <= 30)
	{
		paramsUI = " \
		<div class='btn-group' data-toggle='buttons-radio'> \n\
			<button onclick='fOnTriggerParam(0);' class='btn btn-primary' id='trigger_param_" + paramsArray[0] + "_0'>0</button> \n\
			<button onclick='fOnTriggerParam(1);' class='btn btn-primary' id='trigger_param_" + paramsArray[0] + "_1'>1</button> \n\
			<button onclick='fOnTriggerParam(2);' class='btn btn-primary' id='trigger_param_" + paramsArray[0] + "_2'>2</button> \n\
			<button onclick='fOnTriggerParam(3);' class='btn btn-primary' id='trigger_param_" + paramsArray[0] + "_3'>3</button> \n\
			<button onclick='fOnTriggerParam(4);' class='btn btn-primary' id='trigger_param_" + paramsArray[0] + "_4'>4</button> \n\
			<button onclick='fOnTriggerParam(5);' class='btn btn-primary' id='trigger_param_" + paramsArray[0] + "_5'>5</button> \n\
			<button onclick='fOnTriggerParam(6);' class='btn btn-primary' id='trigger_param_" + paramsArray[0] + "_6'>6</button> \n\
			<button onclick='fOnTriggerParam(7);' class='btn btn-primary' id='trigger_param_" + paramsArray[0] + "_7'>7</button> \n\
		</div> \n\
		\n\
		<script> \n\
			fOnTriggerParam( 0 ); \n\
			$('#trigger_param_" + paramsArray[0] + "_0').button('toggle'); \n\
			function fOnTriggerParam(newValue) { \n\
				$('#trigger_param_" + paramsArray[0] + "').val( newValue ); \n\
				console.log('channel ' + newValue + ' selected'); \n\
			} \n\
		</script>";
	}
	*/

	//Construct HTML elements for the parameters
	var html_string = "";
	for (var i=0; i<paramsArray.length; i++) {
		if (paramsUI != null)
			html_string += paramsArray[i] + "<input type='hidden' id='trigger_param_" + paramsArray[i] + "'>\n";
		else
			html_string += paramsArray[i] + "<input type='text' id='trigger_param_" + paramsArray[i] + "' class='styled-text-input'>\n";
	}
	if (paramsUI != null)
		html_string += paramsUI;

	$('#trigger_parameter_wrapper').fadeOut('fast', function() {   
		$('#trigger_parameter_wrapper').html(html_string);
		$('#trigger_parameter_wrapper').fadeIn('fast', function() {   /* animation complete */  });
	});
}

function fGetTriggerParams(trigger_id)
{
	var one_data = triggers_data[""+trigger_id];
	
	var paramsArray = null;
	if (one_data['trigger_params'] != null)
		paramsArray = one_data['trigger_params'].split("|");
		
	//No parameters
	if (paramsArray == null)
		return null;
		
	return paramsArray;
}

function fGetTriggerParamsJSON(trigger_id)
{
	var prefix = "trigger_param_";
	var paramsArray = fGetTriggerParams(trigger_id);
	if (paramsArray == null)
		return null;

	var paramsObject = {};
	for (var i=0; i<paramsArray.length; i++)
	{
		var name = paramsArray[i];
		var id = prefix + name;
		var val = $("#"+id+"").val();
		if (val == null || val == undefined)
			val = "";
		paramsObject[""+name] = val;
	}
	var params_json = $.toJSON( paramsObject );		//jQuery JSON plugin

	return params_json;
}

function fGetTriggerUI(trigger_id)
{
	var one_data = triggers_data[""+trigger_id];
	
	var trigger_ui = one_data['trigger_ui'];
	if (trigger_ui == null || trigger_ui.length <= 5)
		return null;
		
	return trigger_ui;
}

// -------------------------------------------------------------------------------------------------
//	Filters
// -------------------------------------------------------------------------------------------------

function fSelectFilterModuleID(filter_module_id)
{
	fConsoleLog("filter_module_id selected: " + filter_module_id);
	
	//hide selector to the bottom
	$('#filter_selector_wrapper').fadeOut('fast', function() {   /* animation complete */  });
	$('#filter_parameter_wrapper').fadeOut('fast', function() {   /* animation complete */  });
	
	//no filter
	if (filter_module_id == 0) {
		$('#filter_id').val(0);
		$('#filter_param').val(0);
		$('#filter_selector_wrapper select').html("");
		fSelectFilterID(0);
		return;
	}
	
	//AJAX load the individual filters
	fLoadFiltersForModuleID(filter_module_id);
}

function fLoadFiltersForModuleID(filter_module_id)
{
	var url = 'srv_get_filters.php?user_id=' + user_id + '&module_id='+filter_module_id;
	$.getJSON(url, function(data){ fOnReceiveFiltersForModuleID(filter_module_id, data) } );
}

function fOnReceiveFiltersForModuleID(filter_module_id, jsonData)
{
	if (!fValidateWebserviceOutput(jsonData))
		return;
	var ws_data = jsonData.ws_data;
	
	filters_data = null;
	filters_data = new Array();

	var html_string = "";
	var first_id = -1;
	for (var i=0; i<ws_data.length; i++)
	{
		//To output this: <option value='$filter_id'>$filter_name</option>\n
		var one_data = ws_data[i];
		var the_id = one_data['filter_id'];
		filters_data[""+the_id] = one_data;
		html_string += "<option value='" + the_id + "'";
		if (first_id < 0) {
			html_string += " selected='selected'";
			first_id = the_id;
		}
		html_string += ">" + one_data['filter_name'] + "</option>\n";
	}
	
	//Inject new select-options and display them
	$('#filter_selector_wrapper select').html(html_string);
	$('#filter_selector_wrapper').fadeIn('fast', function() {   /* animation complete */  });
	
	//Select the first option by default
	if (first_id >= 0)
		fSelectFilterID(first_id);
}

function fSelectFilterID(filter_id)
{
	fConsoleLog("filter_id selected: " + filter_id);
	paramsArray = fGetFilterParams(filter_id);
		
	//No parameters
	if (paramsArray == null) {
		$('#filter_parameter_wrapper').fadeOut('fast', function() {   $('#filter_parameter_wrapper').html('');  });
		return;
	}
	
	//Construct HTML elements for the parameters
	var html_string = "";
	for(var i=0; i<paramsArray.length; i++)
		html_string += paramsArray[i] + "<input type='text' id='filter_param_" + paramsArray[i] + "' class='styled-text-input'>\n";
	
	$('#filter_parameter_wrapper').fadeOut('fast', function() {   
		$('#filter_parameter_wrapper').html(html_string);
		$('#filter_parameter_wrapper').fadeIn('fast', function() {   /* animation complete */  });
	});
}

function fGetFilterParams(filter_id)
{
	//No filter
	if (filter_id == null || filter_id <= 0)
		return null;
	var one_data = filters_data[""+filter_id];
	
	var paramsArray = null;
	if (one_data['filter_params'] != null)
		paramsArray = one_data['filter_params'].split("|");
		
	//No parameters
	if (paramsArray == null)
		return null;
		
	return paramsArray;
}

function fGetFilterParamsJSON(filter_id)
{
	var prefix = "filter_param_";
	var paramsArray = fGetFilterParams(filter_id);
	if (paramsArray == null)
		return null;

	var paramsObject = {};
	for (var i=0; i<paramsArray.length; i++)
	{
		var name = paramsArray[i];
		var id = prefix + name;
		var val = $("#"+id+"").val();
		if (val == null || val == undefined)
			val = "";
		paramsObject[""+name] = val;
	}
	var params_json = $.toJSON( paramsObject );		//jQuery JSON plugin

	return params_json;
}

function fGetFilterUI(filter_id)
{
	var one_data = filters_data[""+filter_id];
	
	var filter_ui = one_data['filter_ui'];
	if (filter_ui == null || filter_ui.length <= 5)
		return null;
		
	return filter_ui;
}

// -------------------------------------------------------------------------------------------------
//	Actions
// -------------------------------------------------------------------------------------------------

function fSelectActionModuleID(action_module_id)
{
	fConsoleLog("action_module_id selected: " + action_module_id);
	
	//hide selector to the bottom
	$('#action_selector_wrapper').fadeOut('fast', function() {   /* animation complete */  });
	$('#action_parameter_wrapper').fadeOut('fast', function() {   /* animation complete */  });
	
	//AJAX load
	fLoadActionsForModuleID(action_module_id);
}

function fLoadActionsForModuleID(action_module_id)
{
	var url = 'srv_get_actions.php?user_id=' + user_id + '&module_id=' + action_module_id;
	$.getJSON(url, function(data){ fOnReceiveActionsForModuleID(action_module_id, data) } );
}

function fOnReceiveActionsForModuleID(action_module_id, jsonData)
{
	if (!fValidateWebserviceOutput(jsonData))
		return;
	var ws_data = jsonData.ws_data;
	
	actions_data = null;
	actions_data = new Array();

	var html_string = "";
	var first_id = -1;
	for (var i=0; i<ws_data.length; i++)
	{
		//To output this: <option value='$action_id'>$action_name</option>\n
		var one_data = ws_data[i];
		var the_id = one_data['action_id'];
		actions_data[""+the_id] = one_data;
		html_string += "<option value='" + the_id + "'";
		if (first_id < 0) {
			html_string += " selected='selected'";
			first_id = the_id;			
		}
		html_string += ">" + one_data['action_name'] + "</option>\n";
	}
	
	//Inject new select-options and display them
	$('#action_selector_wrapper select').html(html_string);
	$('#action_selector_wrapper').fadeIn('fast', function() {   /* animation complete */  });
	
	//Select the first option by default
	if (first_id >= 0)
		fSelectActionID(first_id);
}

function fSelectActionID(action_id)
{
	fConsoleLog("action_id selected: " + action_id);
	paramsArray = fGetActionParams(action_id)
		
	//No parameters
	if (paramsArray == null) {
		$('#action_parameter_wrapper').fadeOut('fast', function() {   $('#action_parameter_wrapper').html('');  });
		$('#add_rule_submit_wrapper').fadeIn('fast', function() {   /* animation complete */  });
		return;
	}
	
	//Construct HTML elements for the parameters
	var html_string = "";
	for(var i=0; i<paramsArray.length; i++)
		html_string += paramsArray[i] + "<input type='text' id='action_param_" + paramsArray[i] + "' class='styled-text-input'>\n";
	
	$('#action_parameter_wrapper').fadeOut('fast', function() {   
		$('#action_parameter_wrapper').html(html_string);
		$('#action_parameter_wrapper').fadeIn('fast', function() {   /* animation complete */  });
	});
	$('#add_rule_submit_wrapper').fadeIn('fast', function() {   /* animation complete */  });
}

function fGetActionParams(action_id)
{
	var one_data = actions_data[""+action_id];
	
	var paramsArray = null;
	if (one_data['action_params'] != null)
		paramsArray = one_data['action_params'].split("|");
		
	//No parameters
	if (paramsArray == null)
		return null;
		
	return paramsArray;
}

function fGetActionParamsJSON(action_id)
{
	var prefix = "action_param_";
	var paramsArray = fGetActionParams(action_id);
	if (paramsArray == null)
		return null;

	var paramsObject = {};
	for (var i=0; i<paramsArray.length; i++)
	{
		var name = paramsArray[i];
		var id = prefix + name;
		var val = $("#"+id+"").val();
		if (val == null || val == undefined)
			val = "";
		paramsObject[""+name] = val;
	}
	var params_json = $.toJSON( paramsObject );		//jQuery JSON plugin

	return params_json;
}

function fGetFilterUI(action_id)
{
	var one_data = actions_data[""+action_id];
	
	var action_ui = one_data['action_ui'];
	if (action_ui == null || action_ui.length <= 5)
		return null;
		
	return action_ui;
}

// -------------------------------------------------------------------------------------------------
//	Form submission
// -------------------------------------------------------------------------------------------------

function fOnBtnRuleSubmit(sender)
{
	var user_id = $('#user_id').val();
	var trigger_id = $('#trigger_id').val();
	var filter_id = $('#filter_id').val();
	var action_id = $('#action_id').val();
	
	if (filter_id == null)
		filter_id = 0;
				
	//Use JSON dictionary string to store the parameters
	var trigger_params_json = fGetTriggerParamsJSON(trigger_id);
	fConsoleLog("trigger_param: " + trigger_params_json);
	
	var filter_params_json = fGetFilterParamsJSON(filter_id);
	fConsoleLog("filter_param: " + filter_params_json);

	var action_params_json = fGetActionParamsJSON(action_id);
	fConsoleLog("action_param: " + action_params_json);
		
	//AJAX form submit
	var parameters = new Array();
	parameters['user_id'] = user_id;
	parameters['trigger_id'] = trigger_id;
	if (trigger_params_json != null && trigger_params_json != "")
		parameters['trigger_param'] = trigger_params_json;
	parameters['filter_id'] = filter_id;
	if (filter_params_json != null && filter_params_json != "")
		parameters['filter_param'] = filter_params_json;
	parameters['action_id'] = action_id;
	if (action_params_json != "" && action_params_json != "")
		parameters['action_param'] = action_params_json;
	
	//convert parameters to URL encoded string
	var parametersString = "";
	for (var key in parameters)
		parametersString += "&" + key + "=" + encodeURIComponent(parameters[key]);
	parametersString = parametersString.substring(1);
	fConsoleLog(parametersString);

	$.ajax({
		type: "POST",
		url: "srv_put_rule.php",
		data: parametersString,
		success: fOnReceivePutRule
		});
}

function fOnReceivePutRule(jsonData)
{
	fConsoleLog(jsonData);
	
	if (!fValidateWebserviceOutput(jsonData))
		return;
	var ws_data = jsonData.ws_data;
	
	alert("New rule added");

	//Hide the adding rule panel
	$('#btn_add_rule').attr({src:"images/add.png"});
	$('#panel_add_rule').hide('fast', function() {
		//Refresh page
		location.reload(true);
	});	
}