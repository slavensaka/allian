<?php

namespace Allian\Helpers;

use Database\Connect;
use \Dotenv\Dotenv;
use PHPMailer;

class Mail {

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
		$mail->setFrom(getenv('MAIL_FROM'), 'Allian Translate');
		$mail->addReplyTo(getenv('MAIL_REPLY_TO'), 'Allian Translate');

		$mail->addAddress($email, $FName);
		$mail->IsHTML(true);
		$mail->Subject = 'Allian Translate new password.';
		$mail->MsgHTML($message);
		if (!$mail->send()) {
		   return false;
		} else {
		    return true;
		}
	}

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
		$mail->Subject = 'Allian Translate new password.';
		$mail->MsgHTML($message);
		if (!$mail->send()) {
		   return false;
		} else {
		    return true;
		}
	}
}