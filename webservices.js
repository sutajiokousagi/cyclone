// Common core functions
//------------------------------------------------------------------------------

function getSelectedFormat()
{
	if (document.form_output_format.radio_output_format[0].checked)
		return document.form_output_format.radio_output_format[0].value;
	else
		return document.form_output_format.radio_output_format[1].value;
}

function submitNoInput(webservice_file)
{
	//Create a clean new form
	var myForm = document.createElement("form");
	myForm.method = 'POST';
	myForm.action = webservice_file;
	
	var myInput1 = document.createElement("input");
	myInput1.setAttribute('name', 'format');
	myInput1.setAttribute('value', getSelectedFormat());
	myForm.appendChild(myInput1);

	//Submit & clean up
	document.body.appendChild(myForm);
	myForm.submit();
	document.body.removeChild(myForm);
}

function submitSingleInput(input_name, input_value, webservice_file)
{
	//Create a clean new form and input fields
	var myForm = document.createElement("form");
	myForm.method = 'POST';
	myForm.action = webservice_file;
	
	var myInput1 = document.createElement("input");
	myInput1.setAttribute('name', 'format');
	myInput1.setAttribute('value', getSelectedFormat());
	myForm.appendChild(myInput1);
	
	var myInput2 = document.createElement("input");
	myInput2.setAttribute('name', input_name);
	myInput2.setAttribute('value', input_value);
	myForm.appendChild(myInput2);
	
	//Submit & clean up
	document.body.appendChild(myForm);
	myForm.submit();
	document.body.removeChild(myForm);
}

function submitMultipleInput(input_array, webservice_file)
{
	//Create a clean new form and input fields
	var myForm = document.createElement("form");
	myForm.method = 'POST';
	myForm.action = webservice_file;
	
	var myInput1 = document.createElement("input");
	myInput1.setAttribute('name', 'format');
	myInput1.setAttribute('value', getSelectedFormat());
	myForm.appendChild(myInput1);
	
	for (var keyName in input_array)
	{
		var myInput2 = document.createElement("input");
		myInput2.setAttribute('name', keyName);
		myInput2.setAttribute('value', input_array[keyName]);
		myForm.appendChild(myInput2);
	}

	//Submit & clean up
	document.body.appendChild(myForm);
	myForm.submit();
	document.body.removeChild(myForm);
}

// Individual services
//------------------------------------------------------------------------------

function search()
{
	var webservice_file = "search.php";
	if (document.form_search.inputField0.value != "")
	{
		submitSingleInput('keyword', document.form_search.inputField0.value, webservice_file);
	}
	else
	{
		alert('Input field is required');
	}
}

function get_blocks()
{
	submitNoInput("get_blocks.php");
}

function get_blocks_levels()
{
	submitNoInput("get_blocks_levels.php");
}

function get_kiosks()
{
	var webservice_file = "get_kiosks.php";
	if (document.form_get_kiosks.inputField0.value != "")
	{
		submitSingleInput('kiosk_mac', document.form_get_kiosks.inputField0.value, webservice_file);
	}
	else if (document.form_get_kiosks.inputField1.value != "")
	{
		submitSingleInput('kiosk_poi_ID', document.form_get_kiosks.inputField1.value, webservice_file);
	}
	else
	{
		submitNoInput(webservice_file);
	}
}

function get_levels()
{
	var webservice_file = "get_levels.php";
	if (document.form_get_levels.inputField0.value != "")
	{
		submitSingleInput('block_ID', document.form_get_levels.inputField0.value, webservice_file);
	}
	else if (document.form_get_levels.inputField1.value != "")
	{
		submitSingleInput('level_height', document.form_get_levels.inputField1.value, webservice_file);
	}
	else
	{
		submitNoInput(webservice_file);
	}
}

function get_pois()
{
	var webservice_file = "get_pois.php";
	if (document.form_get_pois.inputField0.value != "")
	{
		submitSingleInput('level_ID', document.form_get_pois.inputField0.value, webservice_file);
	}
	else if (document.form_get_pois.inputField1.value != "")
	{
		submitSingleInput('facility_type_ID', document.form_get_pois.inputField1.value, webservice_file);
	}
	else if (document.form_get_pois.inputField2.value != "")
	{
		submitSingleInput('poi_code', document.form_get_pois.inputField2.value, webservice_file);
	}
	else if (document.form_get_pois.inputField3.value != "")
	{
		submitSingleInput('popular', document.form_get_pois.inputField3.value, webservice_file);
	}
	else
	{
		alert('Input field is required');
	}
}

function get_staffs()
{
	var webservice_file = "get_staffs.php";
	if (document.form_get_staffs.inputField0.value != "")
	{
		submitSingleInput('staff_ID', document.form_get_staffs.inputField0.value, webservice_file);
	}
	else if (document.form_get_staffs.inputField1.value != "")
	{
		submitSingleInput('staff_poi_id', document.form_get_staffs.inputField1.value, webservice_file);
	}
	else if (document.form_get_staffs.inputField2.value != "")
	{
		submitSingleInput('staff_contact', document.form_get_staffs.inputField2.value, webservice_file);
	}
	else
	{
		submitNoInput(webservice_file);
	}
}

function get_users()
{
	var webservice_file = "get_users.php";
	if (document.form_get_users.inputField0.value != "")
	{
		submitSingleInput('user_ID', document.form_get_users.inputField0.value, webservice_file);
	}
	else if (document.form_get_users.inputField1.value != "")
	{
		submitSingleInput('user_matric', document.form_get_users.inputField1.value, webservice_file);
	}
	else if (document.form_get_users.inputField2.value != "")
	{
		submitSingleInput('user_email', document.form_get_users.inputField2.value, webservice_file);
	}
	else if (document.form_get_users.inputField3.value != "")
	{
		submitSingleInput('user_card_status', document.form_get_users.inputField3.value, webservice_file);
	}
	else
	{
		alert('Input field is required');
	}
}

function get_paths()
{
	var webservice_file = "get_paths.php";
	if (document.form_get_paths.inputField0.value != "")
	{
		submitSingleInput('poi_ID', document.form_get_paths.inputField0.value, webservice_file);
	}
	else if (document.form_get_paths.inputField1.value != "")
	{
		submitSingleInput('poi_ID_list', document.form_get_paths.inputField1.value, webservice_file);
	}
	else
	{
		alert('Input field is required');
	}
}

function get_events()
{
	var webservice_file = "get_events.php";
	if (document.form_get_events.inputField0.value != "")
	{
		submitSingleInput('month_number', document.form_get_events.inputField0.value, webservice_file);
	}
	else if (document.form_get_events.inputField1.value != "")
	{
		submitSingleInput('week_number', document.form_get_events.inputField1.value, webservice_file);
	}
	else if (document.form_get_events.inputField2.value != "")
	{
		submitSingleInput('event_ID', document.form_get_events.inputField2.value, webservice_file);
	}
	else if (document.form_get_events.inputField3.value != "")
	{
		submitSingleInput('event_organizer', document.form_get_events.inputField3.value, webservice_file);
	}
	else if (document.form_get_events.inputField4.value != "")
	{
		submitSingleInput('event_poi_ID', document.form_get_events.inputField4.value, webservice_file);
	}
	else if (document.form_get_events.inputField5.value != "")
	{
		submitSingleInput('date', document.form_get_events.inputField5.value, webservice_file);
	}
	else
	{
		submitNoInput(webservice_file);
	}
}

function get_announcements()
{
	submitNoInput("get_announcements.php");
}

function get_facility_types()
{
	submitNoInput("get_facility_types.php");
}

function get_legends()
{
	submitNoInput("get_legends.php");
}

function get_rss()
{
	submitNoInput("get_rss.php");
}

function get_feedback_emails()
{
	submitNoInput("get_feedback_emails.php");
}

function get_home_slideshow()
{
	submitNoInput("get_home_slideshow.php");
}

function update_block_info()
{
	var webservice_file = "update_block_info.php";
	
	//required fields
	if (document.form_update_block_info.inputField0.value == "" || 
		document.form_update_block_info.inputField1.value == "" || 
		document.form_update_block_info.inputField2.value == "" || 
		document.form_update_block_info.inputField3.value == "" || 
		document.form_update_block_info.inputField4.value == "" )
	{
		alert("Some input field(s) is missing");
		return;
	}
	
	var input_array = {"block_ID": document.form_update_block_info.inputField0.value,
						"block_x": document.form_update_block_info.inputField1.value,
						"block_y": document.form_update_block_info.inputField2.value,
				 "block_rotation": document.form_update_block_info.inputField3.value,
					"block_scale": document.form_update_block_info.inputField4.value,
					 "block_name": document.form_update_block_info.inputField5.value };
	
	submitMultipleInput(input_array, webservice_file);
}

function update_level_info()
{
	var webservice_file = "update_level_info.php";
	
	//required fields
	if (document.form_update_level_info.inputField0.value == "" || 
		document.form_update_level_info.inputField1.value == "" || 
		document.form_update_level_info.inputField2.value == "" || 
		document.form_update_level_info.inputField3.value == "" || 
		document.form_update_level_info.inputField4.value == "" )
	{
		alert("Some input field(s) is missing");
		return;
	}
	
	var input_array = {"level_ID": document.form_update_level_info.inputField0.value,
						"level_name": document.form_update_level_info.inputField1.value,
						"level_code": document.form_update_level_info.inputField2.value,
						"level_map_file": document.form_update_level_info.inputField3.value,
						"level_height": document.form_update_level_info.inputField4.value };
	
	submitMultipleInput(input_array, webservice_file);
}

function add_update_poi()
{
	var webservice_file = "add_update_poi.php";
	
	//required fields
	if (document.form_add_update_poi.inputField1.value == "" || 
		document.form_add_update_poi.inputField2.value == "" || 
		document.form_add_update_poi.inputField3.value == "" || 
		document.form_add_update_poi.inputField4.value == "" )
	{
		alert("Some input field(s) is missing");
		return;
	}
	
	var input_array = { "poi_ID": document.form_add_update_poi.inputField0.value,
						"level_ID": document.form_add_update_poi.inputField1.value,
						"poi_x": document.form_add_update_poi.inputField2.value,
						"poi_y": document.form_add_update_poi.inputField3.value,
						"facility_type_ID": document.form_add_update_poi.inputField4.value,
						"poi_name": document.form_add_update_poi.inputField5.value,
						"poi_code": document.form_add_update_poi.inputField6.value,
						"poi_description": document.form_add_update_poi.inputField7.value };
	
	submitMultipleInput(input_array, webservice_file);
}

function update_staff_info()
{
	var webservice_file = "update_staff_info.php";
	
	//required fields
	if (document.form_update_staff_info.inputField0.value == "" || 
		document.form_update_staff_info.inputField1.value == "" || 
		document.form_update_staff_info.inputField2.value == "" || 
		document.form_update_staff_info.inputField3.value == "" || 
		document.form_update_staff_info.inputField4.value == "" || 
		document.form_update_staff_info.inputField5.value == "" || 
		document.form_update_staff_info.inputField6.value == "" || 
		document.form_update_staff_info.inputField7.value == "" )
	{
		alert("Some input field(s) is missing");
		return;
	}
	
	var input_array = {"staff_ID": document.form_update_staff_info.inputField0.value,
						"staff_name": document.form_update_staff_info.inputField1.value,
						"staff_account": document.form_update_staff_info.inputField2.value,
					"staff_office_code": document.form_update_staff_info.inputField3.value,
					"staff_poi_ID": document.form_update_staff_info.inputField4.value,
					"staff_contact": document.form_update_staff_info.inputField5.value,
					"staff_designation": document.form_update_staff_info.inputField6.value,
					"staff_type": document.form_update_staff_info.inputField7.value };
	
	submitMultipleInput(input_array, webservice_file);
}

function add_log_system()
{
	var webservice_file = "add_log_system.php";
	
	//required fields
	if (document.form_add_log_system.inputField0.value == "" || 
		document.form_add_log_system.inputField1.value == "" || 
		document.form_add_log_system.inputField2.value == "" || 
		document.form_add_log_system.inputField3.value == "" )
	{
		alert("Some input field(s) is missing");
		return;
	}
	
	var input_array = {"log_system_mac": document.form_add_log_system.inputField0.value,
						"log_system_ip": document.form_add_log_system.inputField1.value,
					  "log_system_type": document.form_add_log_system.inputField2.value,
					"log_system_message": document.form_add_log_system.inputField3.value };
	
	submitMultipleInput(input_array, webservice_file);
}

function send_calendar()
{
	var webservice_file = "send_calendar.php";
	
	//required fields
	if (document.form_send_calendar.inputField0.value == "" || 
		document.form_send_calendar.inputField1.value == "" )
	{
		alert("Some input field(s) is missing");
		return;
	}
	
	var input_array = {"event_ID": document.form_send_calendar.inputField0.value,
						"user_ID": document.form_send_calendar.inputField1.value };
	
	submitMultipleInput(input_array, webservice_file);
}

function youtube_upload()
{
	var webservice_file = "youtube_upload.php";
	
	//required fields
	if (document.form_youtube_upload.inputField0.value == "")
	{
		alert("Some input field(s) is missing");
		return;
	}
	
	var input_array = {"media_list": document.form_youtube_upload.inputField0.value };
	
	submitMultipleInput(input_array, webservice_file);
}

function cron()
{
	var webservice_file = "cron.php";
	submitNoInput(webservice_file);
}