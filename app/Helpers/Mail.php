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

	/**
	 * TODO SEND_NOTIFICATION da bude jedan localhost jedan za alliantranslate
	 * PRavi za live production $to = "orders@alliancebizsolutions.com,cs@alliantranslate.com"
	 *
	 */
	public function send_notification($subject = "", $body = "", $to = "slavensakacic@gmail.com", $from = "Alliance Business Solutions LLC Client Services <cs@alliantranslate.com>", $reply_to = "cs@alliantranslate.com", $attachment = ""){

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

}