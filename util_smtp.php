<?php
/*
- Created by Torin
- SMTP configurations for sending emails
*/

	require_once("db_config.php");
	
	/*
	 * Returns a single default SMTP configuration
	 */
	function func_getSMTPConfig()
	{
		$condition = "smtp_config_enable = 1";
		return func_getSingleRowFromTable('smtp', $condition);
	}
	
	/*
	 * This function send mail with custom SMTP configuration,
	 * if one is not available, use default mail() function
	 * Returns true/false for success or failure
	 */
	function func_mail($to, $subject, $message)
	{
		$headers = "";
		$headers .= 'From: torinnguyen@torinnguyen.com' . '\r\n';
		$headers .= 'Reply-To: torinnguyen@gmail.com' . '\r\n';
		$headers .= 'Content-Type: text/html; charset=UTF-8' . '\r\n';
		$headers .= 'X-Mailer: PHP/' . phpversion() . '\r\n';
		return mail($to, $subject, $message, $headers);
	}
	
	function func_customMail($to, $subject, $message)
	{
		$smtp_config = func_getSMTPConfig();
		if ($smtp_config == null)
			return false;
			
		//Send mail with PHPMailer
		require_once("class.phpmailer.php");
		
		$SMTP_HOST = $smtp_config['smtp_host'];
		$SMTP_PORT = $smtp_config['smtp_port'];
		$SMTP_NAME = $smtp_config['smtp_name'];
		$SMTP_FROM = $smtp_config['smtp_from_email'];
		$SMTP_USER = $smtp_config['smtp_username'];
		$SMTP_PASS = $smtp_config['smtp_password'];
		
		if (isset($smtp_config['smtp_port']) && $smtp_config['smtp_port'] != null)
			$SMTP_PORT = intval($smtp_config['smtp_port']);
		if ($SMTP_PORT <= 0)
			$SMTP_PORT == 25;
			
		if (isset($smtp_config['smtp_secure']) && $smtp_config['smtp_secure'] != null)
			$SMTP_SECURE = $smtp_config['smtp_secure'];
		if ($SMTP_SECURE == " " || strlen($SMTP_SECURE) < 3)
			$SMTP_SECURE == "";
		
		$mail = new PHPMailer();
		$mail->IsSMTP();
		$mail->Host = $SMTP_HOST;
		$mail->SMTPAuth = true;
		$mail->Port = $SMTP_PORT;
		$mail->SMTPSecure = $SMTP_SECURE;
		$mail->Username = $SMTP_USER;
		$mail->Password = $SMTP_PASS;
		$mail->From = $SMTP_FROM;			//GMail STMP server ignores this
		$mail->FromName = $SMTP_NAME;		//GMail STMP server ignores this
		$mail->Subject = $subject;
		$mail->CharSet = "UTF-8";
		$mail->Encoding = "8bit";
		$mail->IsHTML(true);
		$mail->AddAddress($to);
		$mail->Body = $message;
		
		//Do it!
		if(!$mail->Send())  	return $mail->ErrorInfo;
		else					return true;
	}
	
	/*
	 * Send a calendar event with iCal format through email
	 * This will be added automatically to sender's calendar
	 */
	function func_customMailIcal($organizer_email, $organizer_name,
						$fullname,$email,
						$event_date,$event_title,$event_duration,
						$event_location,$event_description,
						$subject,$email_body)
	{
		include_once("class.phpmailer.php");
	}
?>