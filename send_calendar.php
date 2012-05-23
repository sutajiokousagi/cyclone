<?php
	require_once("db_config.php");
	require_once("smtp_config.php");
	require_once("util_preferences.php");
	
	//--------------------------------------------------------
	// Template values
	
	$table_name = "log_calendar";
	$condition_names_array = array('log_calendar_ID');
	
	//put the N input fields for validation at the head of array
	$input_validate = 4;
	$input_names_array = array('event_ID', 'user_ID', 'is_staff', 'user_email', 'event_title');
	$datetime_field_name = 'log_calendar_date';
	
	$failure_message_email = func_getPreference("msg_sending_calendar_fail");
	if ($failure_message_email == null || strlen($failure_message_email) < 5)
		$failure_message_email = "Failed to send calendar event to email!";
		
	//--------------------------------------------------------
	// Early sanity checks

	$event_ID = $_POST[$input_names_array[0]];
	if ($event_ID == "")
		$event_ID = $_GET[$input_names_array[0]];
	$user_ID = $_POST[$input_names_array[1]];
	if ($user_ID == "")
		$user_ID = $_GET[$input_names_array[1]];
	$is_staff = $_POST[$input_names_array[2]];
	if ($is_staff == "")
		$is_staff = $_GET[$input_names_array[2]];

	if ($event_ID == "" || $user_ID == "" || $is_staff == "")
		func_dieWithMessage("invalid parameters");
		
	//--------------------------------------------------------
	// Retrieve event details
	
	$sql = "SELECT * from events, organizers WHERE event_organizer = organizer_ID 
										AND " . $input_names_array[0] . " = " . $event_ID;
	$result = mysql_query($sql, $link) or func_dieWithMessage("error in SQL syntax: $sql");
	if(!$result) {
		@mysql_close($link);
		func_dieWithMessage("error retrieving event data with " . $input_names_array[0] . " = " . $event_ID);
	}
	$exists = mysql_num_rows($result);
	if ($exists <= 0) {
		@mysql_close($link);
		func_dieWithMessage("event with " . $input_names_array[0] . " = " . $event_ID . " does not exist");
	}
	$event_record = mysql_fetch_assoc($result);
	
	//--------------------------------------------------------
	// Retrieve poi details (if custom location was not used)
	
	$custom_location = $event_record['event_custom_location'];
	$poi_ID = $event_record['event_poi_ID'];
	$pois_view_record = null;
	if (is_null($custom_location) || $custom_location === null || $custom_location == "null" || strlen($custom_location) < 3)
		$custom_location = null;
	if ($custom_location == null)
	{
		$sql = "SELECT * from pois_view WHERE poi_ID = " . $poi_ID;
		$result = mysql_query($sql, $link) or func_dieWithMessage("error in SQL syntax: $sql");
		if(!$result) {
			@mysql_close($link);
			func_dieWithMessage("error retrieving POI data with poi_ID = " . $poi_ID);
		}
		$exists = mysql_num_rows($result);
		if ($exists <= 0) {
			@mysql_close($link);
			func_dieWithMessage("POI with poi_ID = " . $poi_ID . " does not exist");
		}
		$pois_view_record = mysql_fetch_assoc($result);
	}
	
	//--------------------------------------------------------
	// Retrieve user details
	$user_record = null;
	$staff_record = null;
	$fullname = "";
	$email = "";

	if (intval($is_staff) != 1)
	{
		$sql = "SELECT * from users WHERE user_ID = '" . $user_ID . "'";
		$result = mysql_query($sql, $link) or func_dieWithMessage("error in SQL syntax: $sql");
		if(!$result) {
			@mysql_close($link);
			func_dieWithMessage("error retrieving user data for user_ID " . $input_names_array[1] . " = " . $user_matric);
		}
		if (mysql_num_rows($result) > 0)
			$user_record = mysql_fetch_assoc($result);
	}
	
	//--------------------------------------------------------
	// Retrieve staff details

	else
	{
		$sql = "SELECT * from staffs WHERE staff_ID = '" . $user_ID . "'";
		$result = mysql_query($sql, $link) or func_dieWithMessage("error in SQL syntax: $sql");
		if(!$result) {
			@mysql_close($link);
			func_dieWithMessage("error retrieving staff data for staff_ID = " . $user_ID);
		}
		if (mysql_num_rows($result) > 0)
			$staff_record = mysql_fetch_assoc($result);
	}
		
	//--------------------------------------------------------
	// Not found in both user & staff data
	
	if ($staff_record == null && $user_record == null) {
		func_dieWithMessage("user/staff with ID number " . $user_ID . " does not exist");
		@mysql_close($link);
	}
	
	if ($user_record != null) 
	{
		$fullname = $user_record['user_name'];
		$email = $user_record['user_email'];
		$user_ID = $user_record['user_ID'];
		$is_staff = false;
	}
	else
	{
		$fullname = $staff_record['staff_name'];
		$email = $staff_record['staff_email'];
		$user_ID = $staff_record['staff_ID'];
		$is_staff = true;
	}
	
	//--------------------------------------------------------
	// Send calendar event (with iCal format)
		
	$event_location = "";
	if ($custom_location != "" && strlen(trim($custom_location)) > 3)
		$event_location = $custom_location ;
	else if ($pois_view_record != null)
		$event_location = "" . $pois_view_record['level_name'] . ", " . $pois_view_record['poi_name'];
	
	$event_date = $event_record['event_datetime_start']; 				//mysql format "2011-10-26 06:00:00"
	$event_title = $event_record['event_title'];
	$event_description = $event_record['event_description'];
	$event_link = $event_record['event_link'];
	$event_duration = abs(strtotime($event_record['event_datetime_end']) - strtotime($event_record['event_datetime_start']));		//seconds
	$organizer_email = $event_record['event_organizer_email'];
	//$organizer_name = $event_record['organizer_name'];
	$organizer_name = "iSMU Interactive Kiosk";
	$email_body = "You have requested the following event to be added to your calendar." .
	"<br/><br/>Sent from iSMU Interactive Kiosk" .
	"<br/><br/>---------------------------------------------------------------------<br/><br/>" .
	$event_title . "<br/>" .
	$event_link . "<br/><br/>" .
	$event_description . "<br/><br/>";

	//NOTE: $organizer_email is currently empty
	
	//Will be seen in calendar
	$subject = $event_title;

	//Try sending email
	$result = sendIcalWithPHPMailer($organizer_email, $organizer_name,
								$fullname,$email,
								$event_date,$event_title,$event_duration,
								$event_location,$event_description,
								$subject,$email_body);
	
	//Error sending email
	if($result != true && $result != 1)
		func_dieWithMessage($failure_message_email . ". Error: " . $result);

	$success_message_sent = func_getPreference("msg_sending_calendar_ok");
	if ($success_message_sent == null || strlen($success_message_sent) < 5)
		$success_message_sent = "[fullname], you will receive an calendar request in your email [email] shortly.";
	$success_message_sent = str_replace("[fullname]", $fullname, $success_message_sent);
	$success_message_sent = str_replace("[email]", $email, $success_message_sent);
	
	//func_successWithMessage($success_message_sent);
	
	//--------------------------------------------------------
	// Log it to database
	
	$table_name = 'log_calendar';
	$currentDateTime = date('Y-m-d H:i:s');
	$sql = "INSERT INTO $table_name (";
	$sql2 = " VALUES (";
	
	if ($datetime_field_name != "")
	{
		$tempArray = array($datetime_field_name);;
		$input_names_array = array_merge($tempArray, $input_names_array);
		$input_validate = $input_validate + 1;
	}
	
	//Inject
	$_POST['user_email'] = $email;
	$_POST['is_staff'] = $is_staff ? 1 : 0;
	$_POST['event_title'] = $event_title;
	
	//Input values
	for($i=0; $i<count($input_names_array); $i++)
	{ 
		$key_name = $input_names_array[$i];
		$input_value = $_POST[$key_name];
		if ($input_value == "")
			$input_value = $_GET[$key_name];
		
		//prefer external input for datetime field
		if ($input_value == "") {
			if ($key_name == $datetime_field_name)				$input_value = $currentDateTime;
			else												continue;
		}
		
		$input_value = mysql_real_escape_string($input_value);
			
		if ($i < count($input_names_array)-1)	{	$sql = $sql . "$key_name,";		$sql2 = $sql2 . "'$input_value',";		}
		else									{	$sql = $sql . "$key_name)";		$sql2 = $sql2 . "'$input_value')";		}
	}
	
	//some cleaning
	if (func_endsWith($sql, ','))	$sql = substr($sql,0,-1) . ')';
	if (func_endsWith($sql2, ','))	$sql2 = substr($sql2,0,-1) . ')';
	$sql = $sql . $sql2;

	//--------------------------------------------------------
	// SQL query
	
	$result = mysql_query($sql, $link) or func_dieWithMessage("error in SQL syntax: $sql");
	
	if(!$result)
	{
		@mysql_close($link);
		func_dieWithMessage("error adding into $table_name");
	}
		
	//--------------------------------------------------------
	// Get back new entry (ID) just inserted
		
	$sql = "SELECT * FROM $table_name WHERE ";
	
	//Input values
	for($i=0; $i<$input_validate; $i++)
	{ 
		$key_name = $input_names_array[$i];
		$input_value = $_POST[$key_name];
		if ($input_value == "")
			$input_value = $_GET[$key_name];
			
		//prefer external input for datetime field
		if ($input_value == "") {
			if ($key_name == $datetime_field_name)				$input_value = $currentDateTime;
			else												continue;
		}
		
		$input_value = mysql_real_escape_string($input_value);
			
		if ($i < $input_validate-1)		$sql = $sql . "$key_name = '$input_value' AND ";
		else							$sql = $sql . "$key_name = '$input_value' ";
	}
	
	//some cleaning
	if (func_endsWith($sql, 'AND '))	$sql = substr($sql,0,-4);
	
	//SQL query
	$result = mysql_query($sql, $link) or func_dieWithMessage("error in SQL syntax: $sql");
	if(!$result)
	{
		@mysql_close($link);
		func_dieWithMessage("error adding into $table_name");
	}
	
	$exists = mysql_num_rows($result);
	if ($exists <= 0) {
		@mysql_close($link);
		func_dieWithMessage("error adding into $table_name");
	}
	
	$existing_record = mysql_fetch_assoc($result);
	$entry_ID = $existing_record[ $condition_names_array[0] ];
	
	$extraParam = array();
	foreach ($condition_names_array as $val)
		$extraParam[$val] = urlencode($existing_record[$val]);
	foreach ($input_names_array as $val)
		$extraParam[$val] = urlencode($existing_record[$val]);
	
	//Success message
	func_successWithMessage($success_message_sent);
	
	@mysql_close($link);

//-------------------------------------------------------------------------------------------------------

function clean_up_description_string($string)
{
	//this will replace all kind of <br> tag with \n
	$return = nl2br($string);
	
	$return = str_replace("<br/>", "\\n", $return);
	$return = str_replace("<br>", "\\n", $return);
		
	$return = strip_tags($return);
	
	$return = str_replace(",", "\\,", $return);
	
	$return = str_replace("".chr(13), "", $return);
	$return = str_replace("".chr(10), "", $return);
	
	$return = chunk_split($return, 76, chr(13)."\t");
	
	return $return;
}

function sendIcalWithPHPMailer($organizer_email, $organizer_name,
						$fullname,$email,
						$event_date,$event_title,$event_duration,
						$event_location,$event_description,
						$subject,$email_body)
{
	include_once("class.phpmailer.php");
	
	global $SMTP_HOST;
	global $SMTP_PORT;
	global $SMTP_SECURE;
	global $SMTP_NAME;
	global $SMTP_FROM;
	global $SMTP_USER;
	global $SMTP_PASS;
	
	$event_description .= "<br/><br/>";
	$match = array("\n");
	$replacement = array("<br/>");
	$event_description = str_replace($match, $replacement, $event_description);
		
	//Convert MYSQL datetime and construct iCal start, end and issue dates
	date_default_timezone_set("Asia/Singapore");
	$meetingstamp = strtotime($event_date);
	$dtstart= gmdate("Ymd\THis\Z",$meetingstamp);
	$dtend= gmdate("Ymd\THis\Z",$meetingstamp+$event_duration);
	$todaystamp = gmdate("Ymd\THis\Z");
					
	//Create Email Body (HTML)
	$message = "<html>\n";
	$message .= "<body>\n";
	if ($fullname == "")	$message .= '<p>Dear Madam/Sir,</p>';
	else					$message .= '<p>Dear ' . $fullname . ',</p>';
	$message .= '<p>' . $email_body . '</p>';    
	$message .= "</body>\n";
	$message .= "</html>\n";
	
	//Create unique identifier
	$cal_uid = date('Ymd').'T'.date('His')."-".rand()."@smu.edu.sg";
	
	//Reverse engineering note:
	//if both ORGANIZER & X-MS-OLK-SENDER are blank --> Unknown organizer
	//if both ORGANIZER not blank & X-MS-OLK-SENDER blank --> Got decline email
	//if both ORGANIZER blank & X-MS-OLK-SENDER not blank --> no Unknown organizer, no decline email
	
	//noreply address
	//$abcdef = explode("@", $SMTP_FROM);
	//$noreply = "noreply@" . $abcdef[1];
	$noreply = $SMTP_FROM;
	
	//Organizer email
	if ($organizer_email == null || $organizer_email == "") {
		$organizer_string = 'ORGANIZER;CN="'.$organizer_name.'":mailto:'.$noreply;
		$ms_olk_sender = 'X-MS-OLK-SENDER;CN="'.$organizer_name.'":mailto:'.$noreply;
	}
	else {
		$organizer_string = 'ORGANIZER;CN="'.$organizer_name.'":mailto:'.$organizer_email;
		$ms_olk_sender = 'X-MS-OLK-SENDER;CN="'.$organizer_name.'":mailto:'.$organizer_email;
	}
	
	//Create ICAL Content (Google rfc 2445 for details and examples of usage) 
	$ical = 'BEGIN:VCALENDAR
PRODID:-//Microsoft Corporation//Outlook 12.0 MIMEDIR//EN
VERSION:2.0
METHOD:REQUEST
X-MS-OLK-FORCEINSPECTOROPEN:TRUE
BEGIN:VEVENT
ATTENDEE;CN="'.$fullname.'";RSVP=TRUE:mailto:'.$email.'
CLASS:PUBLIC
DESCRIPTION:'.clean_up_description_string($event_description).'
DTEND:'.$dtend.'
DTSTAMP:'.$todaystamp.'
DTSTART:'.$dtstart.'
LOCATION:'.$event_location.'
'.$organizer_string.'
PRIORITY:5
SEQUENCE:0
SUMMARY;LANGUAGE=en-us:'.$subject.'
TRANSP:OPAQUE
UID:'.$cal_uid.'
PRIORITY:5
X-MICROSOFT-CDO-BUSYSTATUS:TENTATIVE
X-MICROSOFT-CDO-IMPORTANCE:1
X-MICROSOFT-CDO-INTENDEDSTATUS:BUSY
X-MICROSOFT-DISALLOW-COUNTER:FALSE
X-MS-OLK-ALLOWEXTERNCHECK:TRUE
X-MS-OLK-AUTOSTARTCHECK:FALSE
X-MS-OLK-CONFTYPE:0
'.$ms_olk_sender.'
BEGIN:VALARM
TRIGGER:-PT15M
ACTION:DISPLAY
DESCRIPTION:Reminder
END:VALARM
END:VEVENT
END:VCALENDAR';
			
	$mail = new PHPMailer();
	$mail->IsSMTP();
	$mail->Host = $SMTP_HOST;
	$mail->SMTPAuth = true;
	$mail->Port = $SMTP_PORT;
	$mail->SMTPSecure = $SMTP_SECURE;
	$mail->Username = $SMTP_USER;
	$mail->Password = $SMTP_PASS;
	$mail->From = $SMTP_FROM;
	$mail->FromName = $SMTP_NAME;
	$mail->Subject = $subject;
	$mail->ContentType = "text/calendar; method=REQUEST;";
	$mail->CharSet = "UTF-8";
	$mail->Encoding = "8bit";
	$mail->AddAddress($email);
	$mail->Body = $ical;
	//$mail->Body = $message;
	//$mail->AddStringAttachment($ical, 'event.ics', "7bit", "text/calendar; charset=UTF-8; method=REQUEST");
	
	//Do it!
	if(!$mail->Send())  	return $mail->ErrorInfo;
	else					return true;
}


?>
