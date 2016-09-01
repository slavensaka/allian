<?php

namespace Allian\Helpers;

use \Dotenv\Dotenv;
use PHPMailer;

class Mail {

	/**
	 *
	 * Send an email to customer when we
	 * changes his password
	 */
	public function newPassProduction($email, $FName, $LoginPassword){
		$mail = new PHPMailer;
		$mail->From = getenv('MAIL_REPLY_TO');
		$mail->FromName = "ALLIAN";

		date_default_timezone_set('Etc/UTC');

		$message = file_get_contents('resources/views/emails/newpassword.php');
		$message = str_replace('%FName%', $FName, $message);
		$message = str_replace('%logo%', getenv('LOGO'), $message);
		$message = str_replace('%LoginPassword%', $LoginPassword, $message);

		$mail->isHTML(true);
		$mail->Subject = "Your account credentials for ALLIAN";
		$mail->MsgHTML($message);

		$mail->addAddress($email, $FName);

		if(!$mail->send()) {
		    return false;
		} else {
		    return true;
		}
	}

	/**
	 *
	 * SMTP needs accurate times, and the PHP time zone MUST be set
	 * This should be done in your php.ini, but this is how to do
	 * it if you don't have access to that
	 *
	 */
	public function newPassEmail($email, $FName, $LoginPassword){
		date_default_timezone_set('Etc/UTC');

		$message = file_get_contents('resources/views/emails/newpassword.php');
		$message = str_replace('%FName%', $FName, $message);
		$message = str_replace('%logo%', getenv('LOGO'), $message);
		$message = str_replace('%LoginPassword%', $LoginPassword, $message);

		$mail = new PHPMailer;
		$mail->isSMTP();
		$mail->CharSet='UTF-8';
		$mail->SMTPAuth = true;

		$mail->Host = getenv('MAIL_HOST');
		$mail->Port = getenv('MAIL_PORT');
		$mail->SMTPSecure = getenv('MAIL_ENCRYPTION');
		$mail->Username = getenv('MAIL_USERNAME');
		$mail->Password = getenv('MAIL_PASSWORD');
		$mail->setFrom(getenv('MAIL_REPLY_TO'), 'ALLIAN');
		$mail->addReplyTo(getenv('MAIL_REPLY_TO'), 'ALLIAN');

		$mail->addAddress($email, $FName);
		$mail->IsHTML(true);
		$mail->Subject = 'Your account credentials for ALLIAN';
		$mail->MsgHTML($message);
		if (!$mail->send()) {
		   return false;
		} else {
		    return true;
		}
	}

	/**
	 *
	 * Send the telephone access to customer
	 *	password and the phoneNumber
	 */
	public function telAccessProduction($values){
		$mail = new PHPMailer;
		$mail->From = getenv('MAIL_REPLY_TO');
		$mail->FromName = "ALLIAN";

		date_default_timezone_set('Etc/UTC');

		$message = file_get_contents('resources/views/emails/telephonicAccessEmail.php');
		$message = str_replace('%FName%',$values['FName'], $message);
		$message = str_replace('%LName%', $values['LName'], $message);
		$message = str_replace('%telephonicUserId%', $values['telephonicUserId'], $message);
		$message = str_replace('%telephonicPassword%', $values['telephonicPassword'], $message);
		$message = str_replace('%tel%', $values['tel'], $message);
		$message = str_replace('%csEmail%', $values['csEmail'], $message);

		$mail->isHTML(true);
		$mail->Subject = "Telephonic Access Session Credentials.";
		$mail->MsgHTML($message);

		$mail->addAddress($values['Email'], $FName);

		if(!$mail->send()) {
		    return false;
		} else {
		    return true;
		}
	}

	/**
	 *
	 * Send the telephonic only on localhost,
	 * it's not for production
	 *
	 */
	public function telAccessEmail($values){
		date_default_timezone_set('Etc/UTC');

		$message = file_get_contents('resources/views/emails/telephonicAccessEmail.php');
		$message = str_replace('%FName%',$values['FName'], $message);
		$message = str_replace('%LName%', $values['LName'], $message);
		$message = str_replace('%telephonicUserId%', $values['telephonicUserId'], $message);
		$message = str_replace('%telephonicPassword%', $values['telephonicPassword'], $message);
		$message = str_replace('%tel%', $values['tel'], $message);
		$message = str_replace('%csEmail%', $values['csEmail'], $message);

		$mail = new PHPMailer;
		$mail->isSMTP();
		$mail->CharSet='UTF-8';
		$mail->SMTPAuth = true;
		$mail->Host = getenv('MAIL_HOST');
		$mail->Port = getenv('MAIL_PORT');
		$mail->SMTPSecure = getenv('MAIL_ENCRYPTION');
		$mail->Username = getenv('MAIL_USERNAME');
		$mail->Password = getenv('MAIL_PASSWORD');
		$mail->setFrom(getenv('MAIL_FROM'), "From: Client Services - Alliance Business Solutions<cs@alliantranslate.com>");
		$mail->addReplyTo(getenv('MAIL_REPLY_TO'), "Client Services<cs@alliantranslate.com>");

		$mail->addAddress($values['Email'], $FName);
		$mail->IsHTML(true);
		$mail->Subject = 'Telephonic Access Session Credentials.';
		$mail->MsgHTML($message);
		if (!$mail->send()) {
		   return false;
		} else {
		    return true;
		}
	}

	/**
	 *
	 * Used to send email notification to staff and admins
	 *	only on localhost, not for production
	 */
	public function sendStaffMail($sendToEmail, $text, $param){
		$client = "Client Services - Alliance Business Solutions <cs@alliantranslate.com>" ;
	    $number = "";
	    $pair = "";
	    $queueresult = "";
	    $time = "";
	    $email = "";
        $arr = explode(",", $param);
        $number = $arr[0];
        $pair = $arr[1];
        $queueresult = $arr[2];
        $time = $arr[3];
        $email = $arr[4];

	    date_default_timezone_set('Etc/UTC');

		$message = file_get_contents('resources/views/emails/' . $text . '.php');
		$message = str_replace('%MAIL%', $email, $message);
		$message = str_replace('%NUMBER%', $number, $message);
		$message = str_replace('%LANGPAIR%', $pair, $message);
		$message = str_replace('%REASON%', $queueresult, $message);
		$message = str_replace('%DATETIME%', $time, $message);

		$mail = new PHPMailer;
		$mail->isSMTP();
		$mail->CharSet='UTF-8';
		$mail->SMTPAuth = true;
		$mail->Host = getenv('MAIL_HOST');
		$mail->Port = getenv('MAIL_PORT');
		$mail->SMTPSecure = getenv('MAIL_ENCRYPTION');
		$mail->Username = getenv('MAIL_USERNAME');
		$mail->Password = getenv('MAIL_PASSWORD');
		$mail->setFrom($staff, "From: Client Services - Alliance Business Solutions<cs@alliantranslate.com>");
		$mail->addReplyTo(getenv('MAIL_REPLY_TO'), "Client Services<cs@alliantranslate.com>");
		$mail->addAddress($sendToEmail, "Order");
		$mail->IsHTML(true);
		$mail->Subject = 'POTENTIAL CLIENT CALL FAILED:';
		$mail->MsgHTML($message);
		if (!$mail->send()) {
		   return false;
		} else {
		    return true;
		}
	}

	/**
	 *
	 * Used to send email notification to staff and admins
	 *
	 */
	public function sendStaffMailProduction($sendToEmail, $text, $param){
		$mail = new PHPMailer;
		$mail->From = "From: Client Services - Alliance Business Solutions<cs@alliantranslate.com>";
		$mail->FromName = "ALLIAN";

		date_default_timezone_set('Etc/UTC');

		$client = "Client Services - Alliance Business Solutions <cs@alliantranslate.com>" ;
	    $number = "";
	    $pair = "";
	    $queueresult = "";
	    $time = "";
	    $email = "";
        $arr = explode(",", $param);
        $number = $arr[0];
        $pair = $arr[1];
        $queueresult = $arr[2];
        $time = $arr[3];
        $email = $arr[4];

		$message = file_get_contents('resources/views/emails/' . $text . '.php');
		$message = str_replace('%MAIL%', $email, $message);
		$message = str_replace('%NUMBER%', $number, $message);
		$message = str_replace('%LANGPAIR%', $pair, $message);
		$message = str_replace('%REASON%', $queueresult, $message);
		$message = str_replace('%DATETIME%', $time, $message);

		$mail->isHTML(true);
		$mail->Subject = "POTENTIAL CLIENT CALL FAILED:";
		$mail->MsgHTML($message);
		$mail->addAddress($sendToEmail, "Order");
		if (!$mail->send()) {
		   return false;
		} else {
		    return true;
		}
	}

	public static function sendClientCallWaitingProduction($email, $param){
		$arr = explode(",", $param);
	    $pair = $arr[0];
	    $time = $arr[1];

		if(getenv('APP_MODE') == 'dev'){
			date_default_timezone_set('Etc/UTC');
		    $footer = self::footer_signature();
			$message = file_get_contents('resources/views/emails/clientCallWaiting.php');
			$message = str_replace('%QUEUE%', $pair, $message);
			$message = str_replace('%DATETIME%', $time, $message);
			$message = str_replace('%FOOTER%', $footer, $message);
			$message = str_replace('%TWILIO_CONF_OB_NUMBER%', getenv('TWILIO_CONF_OB_NUMBER'), $message);

		    $mail = new PHPMailer;
			$mail->isSMTP();
			$mail->CharSet='UTF-8';
			$mail->SMTPAuth = true;
			$mail->Host = getenv('MAIL_HOST');
			$mail->Port = getenv('MAIL_PORT');
			$mail->SMTPSecure = getenv('MAIL_ENCRYPTION');
			$mail->Username = getenv('MAIL_USERNAME');
			$mail->Password = getenv('MAIL_PASSWORD');
			$mail->setFrom("From:" .  "Projects Desk - Alliance Business Solutions <projects@alliancebizsolutions.com>");
			$mail->addReplyTo(getenv('MAIL_REPLY_TO'), "Client Services<cs@alliantranslate.com>");
			$mail->addAddress('slavensakacic@gmail.com', "Order");
			$mail->IsHTML(true);
			$mail->Subject = "NEW INTERPRETING PROJECT - TELEPHONIC - " . $pair;;
			$mail->MsgHTML($message);
			if($email == 'nethramllc@gmail.com' || $email == 'goharulzaman@gmail.com' || $email == 'missridikas@gmail.com'){

			} else {
				if (!$mail->send()) {
				   return false;
				} else {
				    return true;
				}
			}
		} else if(getenv('APP_MODE') == 'prod'){
			$footer = self::footer_signature();
			$message = file_get_contents('resources/views/emails/clientCallWaiting.php');
			$message = str_replace('%QUEUE%', $pair, $message);
			$message = str_replace('%DATETIME%', $time, $message);
			$message = str_replace('%FOOTER%', $footer, $message);
			$message = str_replace('%TWILIO_CONF_OB_NUMBER%', getenv('TWILIO_CONF_OB_NUMBER'), $message);
			$subject = "NEW INTERPRETING PROJECT - TELEPHONIC - " . $pair;
			$headers = "From:" .  "Projects Desk - Alliance Business Solutions <projects@alliancebizsolutions.com>" . "\r\n";
    		$headers .= "MIME-Version: 1.0" . "\r\n";
    		$headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
			if($email == 'nethramllc@gmail.com' || $email == 'goharulzaman@gmail.com' || $email == 'missridikas@gmail.com'){

			} else {
				// $sent = mail($email, $subject, $message, $headers);
				$sent = mail("alen.brcic@alliancebizsolutions.com", $subject, $message, $headers); // TODO PRODUCTION
				$sent = mail("lalbescu@alliancebizsolutions.com", $subject, $message, $headers); // TODO PRODUCTION
				if (!$sent) {
				   return false;
				} else {
				    return true;
				}
			}
		}

	}

	public static function footer_signature(){
	    $service = "Project Desk";
	    $email = "projects@alliancebizsolutions.com";
		$footer = "<p>If you have any questions, please email us at any time.</p>
		<br><p><span style='font-family: Arial; font-size: 12px; font-style: normal;'><strong>Alliance Business Solutions<br /></strong><em>-- $service </em><strong><br />Email:</strong> $email <br /><strong>Fax:</strong> 1.615.472.7924<br /></span><a href='" . getenv('HOME_SECURE') . "' target='_blank'>Translate</a><span style='font-family: Arial; font-size: 12px; font-style: normal;'>. | </span><a href='" . getenv('INTERPRETING_HOME_SECURE') . "/' target='_blank'>Interpret</a><span style='font-family: Arial; font-size: 12px; font-style: normal;'>. | </span><a href='" . getenv('TRANSCRIPTION_HOME_SECURE') . "' target='_blank'>Transcribe</a><span style='font-family: Arial; font-size: 12px; font-style: normal;'>.<br /></span><img style='font-family: Arial; font-size: 12px; font-style: normal;' src='" . getenv('HOME') . "logos/alliancebizsolutions_logo.png' alt='' width='150' height='58' /></p>";
	    return $footer;
	}

	/**
	 * TODO SEND_NOTIFICATION DONE
	 * Pravi za live production $to = "orders@alliancebizsolutions.com,cs@alliantranslate.com"
	 *
	 */
	public function send_notification($subject = "", $body = "", $to = "alen.brcic@alliancebizsolutions.com", $from = "Alliance Business Solutions LLC Client Services <cs@alliantranslate.com>", $reply_to = "cs@alliantranslate.com", $attachment = ""){

		date_default_timezone_set('Etc/UTC');

		$mail = new PHPMailer;
		$mail->isSMTP();
		$mail->CharSet='UTF-8';
		$mail->SMTPAuth = true;

		$mail->Host = getenv('MAIL_HOST');
		$mail->Port = getenv('MAIL_PORT');
		$mail->SMTPSecure = getenv('MAIL_ENCRYPTION');
		$mail->Username = getenv('MAIL_USERNAME');
		$mail->Password = getenv('MAIL_PASSWORD');
		$mail->setFrom($from, 'Allian Translate');
		$mail->addReplyTo($reply_to, 'Allian Translate');
		$mail->addAddress($to, $to);
		$mail->IsHTML(true);
		$mail->Subject = $subject;
		$mail->MsgHTML($body);
		if (!$mail->send()) {
		   return false;
		} else {
		    return true;
		}
	}

	/**
	 * TODO SEND_NOTIFICATION DONE
	 * Pravi za live production $to = "orders@alliancebizsolutions.com,cs@alliantranslate.com"
	 *
	 */
	public function send_notification_handle_payment($subject = "", $body = "", $to = "slavensakacic@gmail.com", $from = "Alliance Business Solutions LLC Client Services <cs@alliantranslate.com>", $reply_to = "cs@alliantranslate.com", $attachment = ""){

		date_default_timezone_set('Etc/UTC');

		$mail = new PHPMailer;
		$mail->isSMTP();
		$mail->CharSet='UTF-8';
		$mail->SMTPAuth = true;

		$mail->Host = getenv('MAIL_HOST');
		$mail->Port = getenv('MAIL_PORT');
		$mail->SMTPSecure = getenv('MAIL_ENCRYPTION');
		$mail->Username = getenv('MAIL_USERNAME');
		$mail->Password = getenv('MAIL_PASSWORD');
		$mail->setFrom($from, 'Allian Translate');
		$mail->addReplyTo($reply_to, 'Allian Translate');
		$mail->addAddress($to, $to);
		$mail->IsHTML(true);
		$mail->Subject = $subject;
		$mail->MsgHTML($body);
		if (!$mail->send()) {
		   return false;
		} else {
		    return true;
		}
	}

	public function simpleLocalMail($subject = "", $body = ""){

		date_default_timezone_set('Etc/UTC');

		$mail = new PHPMailer;
		$mail->isSMTP();
		$mail->CharSet='UTF-8';
		$mail->SMTPAuth = true;

		$mail->Host = getenv('MAIL_HOST');
		$mail->Port = getenv('MAIL_PORT');
		$mail->SMTPSecure = getenv('MAIL_ENCRYPTION');
		$mail->Username = getenv('MAIL_USERNAME');
		$mail->Password = getenv('MAIL_PASSWORD');
		$mail->setFrom("slaven", 'Test local');
		$mail->addAddress("slavensakacic@gmail.com");
		$mail->IsHTML(true);
		$mail->Subject = $subject;
		$mail->MsgHTML($body);
		if (!$mail->send()) {
		   return false;
		} else {
		    return true;
		}
	}

}