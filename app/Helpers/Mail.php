<?php

namespace Allian\Helpers;

use Database\Connect;
use \Dotenv\Dotenv;
use PHPMailer;

class Mail {

	/**
	 *
	 * Block comment
	 *
	 */
	public function tester($request, $response, $service, $app){
		$headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
		$headers .= "From: cs@alliantranslate.com\r\n";
        $headers.="Reply-To: cs@alliantranslate.com\r\n";
		mail("slavensakacic@gmail.com", "SUBJECT", "OVO JE PORUKA", $headers);
	}

	/**
	 *
	 * Block comment
	 *
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
	 * Maybe create Mail Model?
	 *
	 */
	public function newPassEmail($email, $FName, $LoginPassword){
		//SMTP needs accurate times, and the PHP time zone MUST be set
		//This should be done in your php.ini, but this is how to do it if you don't have access to that
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
	 * Block comment
	 *
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
	 * Block comment
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
	 * TODO SEND_NOTIFICATION da bude jedan localhost jedan za alliantranslate
	 * PRavi za live production $to = "orders@alliancebizsolutions.com,cs@alliantranslate.com"
	 *
	 */
	public function send_notification($subject = "", $body = "", $to = "slavensakacic@gmail.com", $from = "Alliance Business Solutions LLC Client Services <cs@alliantranslate.com>", $reply_to = "cs@alliantranslate.com", $attachment = ""){

		date_default_timezone_set('Etc/UTC');

		// $message = file_get_contents('resources/views/emails/newpassword.php');
		// $message = str_replace('%FName%', $FName, $message);
		// $message = str_replace('%logo%', getenv('LOGO'), $message);
		// $message = str_replace('%LoginPassword%', $LoginPassword, $message);

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

	/*
		send_notification function is used to send notifications/emails.
		@Param  $subject: Subject of the email notification. empty by default.
		@Param  $body: Body of the email notification. empty by default.
		@Param  $to: The recipient(s) of the email notification. It sends to orders@alliancebizsolutions.com andcs@alliantranslate.com by default.
		@Param  $from: The sender of the email notification. It is sent from cs@alliantranslate.com by default.
		@Param  $reply_to: The email address where the reciever of email can reply to. It replies to cs@alliantranslate.com by default.
		@Param  $attachment: This is the source of attachment file. The file to be attached must reside on server. It does not attach by default.
		 *
		Usage:
		1: Please press Ctrl+Shift+f
		2: A search Window asks to search specific function. You may search "send_notification(" without double quotes and with opening parentheses.
		3: choose directory to search within and press "Find" Button
		4: The "Search Results" panel will search and display the pages where this functions has been used in the code.
	*/
	function tester_send_notification($subject = "", $body = "", $to = "orders@alliancebizsolutions.com,cs@alliantranslate.com", $from = "Alliance Business Solutions LLC Client Services <cs@alliantranslate.com>", $reply_to = "cs@alliantranslate.com", $attachment = "") {
	    if ($attachment == "") {
	        // Prepare Headers and Subject
	        $mailheader = "From: $from\r\n";
	        $mailheader .= "Reply-To: $reply_to\r\n";
	        $mailheader .= "Content-type: text/html; charset=iso-8859-1\r\n";
	        $mailheader .= "MIME-Version: 1.0\r\n";
	        // Send Email
	        return mail($to, $subject, $body, $mailheader);
	    } else {
	        // Attachment file is provided, attach it with email an send.
	        ini_set("include_path", '/home/alliantranslate/php:' . ini_get("include_path")  );
	        require_once "Mail.php";
	        include_once('Mail/mime.php');
	        $message = new Mail_mime();
	        $message->setHTMLBody($body);

	        if( is_array($attachment)){
	          foreach($attachment as $file){
	          $message->addAttachment($file);
	          }
	        }else{
	        $message->addAttachment($attachment);
	        }

	        $body = $message->get();
	        $headers = array('From' => $from, 'To' => $to, 'Subject' => $subject);
	        $headers = $message->headers($headers);
	        $smtp = Mail::factory('smtp');
	        $mail = $smtp->send($to, $headers, $body);
	        if (PEAR::isError($mail)) {
	            return $mail->getMessage();
	        } else {
	            return true;
	        }
	    }
	}
}