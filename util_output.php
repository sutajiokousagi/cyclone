<?php
/*
- Created by Torin
- A common utility for printing outputs
*/
	
function func_dieWithMessage($ws_message)
{
	func_dieWithMessageWithParam($ws_message, null);
}
		
function func_dieWithMessageWithParam($ws_message, $extraArray)
{		
	//Script name
	$file = $_SERVER["SCRIPT_NAME"];
	$break = Explode('/', $file);
	$scriptname = $break[count($break) - 1];
	$scriptname = str_replace(".php", "", $scriptname);

	$extraParameters = "";
	if($extraArray != null)
		foreach ($extraArray as $key => $val)
			if ($key != "ws_status" && $key != "ws_message" && $key != "ws_service")
				$extraParameters = $extraParameters . ", \"$key\":\"$val\"";

	if(!headers_sent())
		header('Content-type: application/json; charset=utf-8');
	die("[{ \"ws_status\":\"no\", \"ws_message\":\"" . $ws_message . "\", \"ws_service\":\"" . $scriptname . "\"" . $extraParameters . " }]");
}

function func_successWithMessage($ws_message)
{
	func_successWithMessageWithParam($ws_message, null);
}

function func_successWithMessageWithParam($ws_message, $extraArray)
{	
	//Script name
	$file = $_SERVER["SCRIPT_NAME"];
	$break = Explode('/', $file);
	$scriptname = $break[count($break) - 1];
	$scriptname = str_replace(".php", "", $scriptname);
			
	$extraParameters = "";
	if($extraArray != null)
		foreach ($extraArray as $key => $val)
			if ($key != "ws_status" && $key != "ws_message" && $key != "ws_service")
				$extraParameters = $extraParameters . ", \"$key\":\"$val\"";
		
	if(!headers_sent())
		header('Content-type: application/json; charset=utf-8');
	die("[{ \"ws_status\":\"Yes\", \"ws_message\":\"" . $ws_message . "\", \"ws_service\":\"" . $scriptname . "\"" . $extraParameters . " }]");
}

function func_outputSqlArrayResult($myresult)
{	
	/* create one master array of the records */
	$big_output_array = array();
	
	if(mysql_num_rows($myresult) > 0)
		while($output_item = mysql_fetch_assoc($myresult))
			$big_output_array[] = $output_item;

	func_webserviceOutputJSON(null, null, $big_output_array);
}

function func_outputArray($somearray)
{
	func_webserviceOutputJSON(null, null, $somearray);
}

function func_webserviceOutputJSON($status_code, $message, $data)
{
	if ($status_code == null || $status_code == "")
		$status_code = 200;
	if ($message == null || $message == "")
		$message = 'ok';
		
	//Script name
	$file = $_SERVER["SCRIPT_NAME"];
	$break = Explode('/', $file);
	$scriptname = $break[count($break) - 1];
	$scriptname = str_replace(".php", "", $scriptname);
	
	$final_output_array = array();
	$final_output_array['ws_service'] = $scriptname;
	$final_output_array['ws_status'] = $status_code;
	$final_output_array['ws_message'] = $message;
	$final_output_array['ws_data'] = $data;
	
	if(!headers_sent())
		header('Content-type: application/json; charset=utf-8');
	echo json_encode($final_output_array);
}

//-------------------------------------------------------------------------------
// Utility functions
//-------------------------------------------------------------------------------

function func_startsWith($haystack, $needle)
{
    $length = strlen($needle);
    return (substr($haystack, 0, $length) === $needle);
}

function func_endsWith($haystack, $needle)
{
    $length = strlen($needle);
    $start  = $length * -1; //negative
    return (substr($haystack, $start) === $needle);
}

function func_contains($haystack, $needle)
{
    return (strpos($haystack, $needle) !== false);
}

function func_outputArrayInJSON($extraArray)
{	
	if(!headers_sent())
		header('Content-type: application/json; charset=utf-8');
	$json_string = json_encode($extraArray);
	die("$json_string");
}

function func_getMimeType($extension)
{
	$extension = strtolower($extension);
	if ($extensions == "jpeg" || $extension == "jpg")	return "image/jpeg";
	if ($extensions == "png")							return "image/png";
	if ($extensions == "bmp")							return "image/bmp";
	if ($extensions == "gif")							return "image/gif";
	return "text/plain";
}

/**
Validate an email address.
Provide email address (raw input)
Returns true if the email address has the email 
address format and the domain exists.
*/
function func_validEmail($email)
{
   $isValid = true;
   $atIndex = strrpos($email, "@");
   if (is_bool($atIndex) && !$atIndex)
   {
      $isValid = false;
   }
   else
   {
      $domain = substr($email, $atIndex+1);
      $local = substr($email, 0, $atIndex);
      $localLen = strlen($local);
      $domainLen = strlen($domain);
      if ($localLen < 1 || $localLen > 64)
      {
         // local part length exceeded
         $isValid = false;
      }
      else if ($domainLen < 1 || $domainLen > 255)
      {
         // domain part length exceeded
         $isValid = false;
      }
      else if ($local[0] == '.' || $local[$localLen-1] == '.')
      {
         // local part starts or ends with '.'
         $isValid = false;
      }
      else if (preg_match('/\\.\\./', $local))
      {
         // local part has two consecutive dots
         $isValid = false;
      }
      else if (!preg_match('/^[A-Za-z0-9\\-\\.]+$/', $domain))
      {
         // character not valid in domain part
         $isValid = false;
      }
      else if (preg_match('/\\.\\./', $domain))
      {
         // domain part has two consecutive dots
         $isValid = false;
      }
      else if (!preg_match('/^(\\\\.|[A-Za-z0-9!#%&`_=\\/$\'*+?^{}|~.-])+$/', str_replace("\\\\","",$local)))
      {
         // character not valid in local part unless 
         // local part is quoted
         if (!preg_match('/^"(\\\\"|[^"])+"$/',
             str_replace("\\\\","",$local)))
         {
            $isValid = false;
         }
      }
	  /* Check if DNS is valid
      if ($isValid && !(checkdnsrr($domain,"MX") || checkdnsrr($domain,"A")))
      {
         // domain not found in DNS
         $isValid = false;
      }
	  */
   }
   return $isValid;
}

?>