<?php

namespace Allian\Http\Controllers;

use PHPMailer;

class Controller {
	/**
	 *
	 * Block comment
	 *
	 */
	public function renderDocs($request, $response, $service, $app){
    		if ( !isset($_SERVER['PHP_AUTH_USER'] ) || !isset( $_SERVER['PHP_AUTH_PW'] ) ||
    			$_SERVER['PHP_AUTH_USER'] != getenv('BASIC_AUTH_USER') || $_SERVER['PHP_AUTH_PW'] != getenv('BASIC_AUTH_PASS') ) {
        		header( 'WWW-Authenticate: Basic realm="NO auth!"' );
        		header( 'HTTP/1.0 401 Unauthorized' );
        		exit;
    	} else {
       		$service->render('./docs/index.html');
    	}
	}

	/**
     * @ApiDescription(section="Terms", description="Render view for terms & conditions")
     * @ApiMethod(type="get")
     * @ApiRoute(name="/testgauss/terms")
     * @ApiReturnHeaders(sample="HTTP 200 OK")
     */
	public function getTerms($request, $response, $service, $app){
		$service->render('./resources/views/terms.html');

	}


	/**
	 *
	 * Block comment
	 *
	 */
	public function validateEmail($email){

	}

	/**
	 *
	 * Error response json message
	 *
	 */
	public static function errorJson($userMessage, $result = null){
		$jsonArray = array();
		$jsonArray['status'] = 0;
		$jsonArray['userMessage'] = $userMessage;
		if($result){
			$jsonArray['result'] = $result;
		}
		return $jsonArray;
	}

	/**
	 *
	 * Success response json message
	 *
	 */
	public static function successJson($userMessage){
		$jsonArray = array();
		$jsonArray['status'] = 1;
		$jsonArray['userMessage'] = $userMessage;
		return $jsonArray;
	}

	public function generatePassword(){
		$alphabet = "abcdefghijklmnopqrstuwxyzABCDEFGHIJKLMNOPQRSTUWXYZ0123456789";
	    $pass = array(); //remember to declare $pass as an array
	    $alphaLength = strlen($alphabet) - 1; //put the length -1 in cache
	    for ($i = 0; $i < 8; $i++) {
	        $n = rand(0, $alphaLength);
	        $pass[] = $alphabet[$n];
	    }
	    return implode($pass); //turn the array into a string
	}

	/**
	 *
	 * Block comment
	 *
	 */
	public function loginValues($customer){
		$jsonArray = array();
		$fname = $customer->getValueEncoded('FName');
		$lname = $customer->getValueEncoded('LName');
		$jsonArray['customerId'] = $customer->getValueEncoded('CustomerID');
		$jsonArray['status'] = 1;
		$jsonArray['fname'] = $fname;
		$jsonArray['lname'] = $lname;
		$jsonArray['userMessage'] = "Authentication Successfull. Welcome $fname $lname.";
		return $jsonArray;
	}

	/**
	 *
	 * Block comment
	 *
	 */
	public function encryptValues($data){
		$password = getenv("CRYPTOR");
		$encryptor = new \RNCryptor\Encryptor();
		$base64Encrypted = $encryptor->encrypt($data, $password);
		return $base64Encrypted;
	}

	/**
	 *
	 * Block comment
	 *
	 */
	public function makeLoginResponse($base64Encrypted, $genToken = null){
		$response = array('token' => $genToken, 'data' => $base64Encrypted);
		return $response;
	}

	/**
	 *
	 * Block comment
	 *
	 */
	public function emailValues($customer){
		$jsonArray = array();
		$jsonArray['status'] = 1;
		$jsonArray['userMessage'] = "New password has been sent to your e-mail address. Please check your e-mail to retrieve your password";
		return $jsonArray;
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

}