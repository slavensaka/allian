<?php

namespace Allian\Http\Controllers;

use PHPMailer;
use Firebase\JWT\JWT;
use Allian\Models\CustLogin;

class Controller {

	/**
	 *
	 * Block comment
	 * TODO MORAM SPREMIT TOKEN_ID IZ JWT U BAZU i UVIJEK PROVJERIT
	 */
	public function generateResponseToken($dataArray){
		$secretKey = base64_decode(getenv('jwtKey'));

		$tokenId    = base64_encode(mcrypt_create_iv(32));
	    $issuedAt   = time();
	    $notBefore  = $issuedAt;
	    $expire     = $notBefore + 1209600; // 2 weeks working token
	    $serverName = $_SERVER['SERVER_NAME'];

	    $data = array(
	        'iat'  => $issuedAt,         // Issued at: time when the token was generated
	        'jti'  => $tokenId,          // Json Token Id: an unique identifier for the token
	        'iss'  => $serverName,       // Issuer
	        'nbf'  => $notBefore,        // Not before
	        'exp'  => $expire,           // Expire
	        'data' => $dataArray
	    );

	    // Encode the new json payload data
	    $jwt = JWT::encode($data, $secretKey, 'HS512');
	    header('Content-type: application/json');

    	return $jwt;
	}

	public function generateExpiredToken($dataArray){
		$secretKey = base64_decode(getenv('jwtKey'));

		$tokenId    = base64_encode(mcrypt_create_iv(32));
	    $issuedAt   = time();
	    $notBefore  = $issuedAt;
	    $expire     = $notBefore - 1209600; // Expire token
	    $serverName = $_SERVER['SERVER_NAME'];

	    $data = array(
	        'iat'  => $issuedAt,         // Issued at: time when the token was generated
	        'jti'  => $tokenId,          // Json Token Id: an unique identifier for the token
	        'iss'  => $serverName,       // Issuer
	        'nbf'  => $notBefore,        // Not before
	        'exp'  => $expire,           // Expire
	        'data' => $dataArray
	    );

	    // Encode the new json payload data
	    $jwt = JWT::encode($data, $secretKey, 'HS512');
	    header('Content-type: application/json');

    	return $jwt;
	}

	public function generateInfiniteToken($dataArray){
		$secretKey = base64_decode(getenv('jwtKey'));

		$tokenId    = base64_encode(mcrypt_create_iv(32));
	    $issuedAt   = time();
	    $notBefore  = $issuedAt;
	    $expire     = $notBefore + 126144000; // 4 years working token
	    $serverName = $_SERVER['SERVER_NAME'];

	    $data = array(
	        'iat'  => $issuedAt,         // Issued at: time when the token was generated
	        'jti'  => $tokenId,          // Json Token Id: an unique identifier for the token
	        'iss'  => $serverName,       // Issuer
	        'nbf'  => $notBefore,        // Not before
	        'exp'  => $expire,           // Expire
	        'data' => $dataArray
	    );

	    // Encode the new json payload data
	    $jwt = JWT::encode($data, $secretKey, 'HS512');
	    header('Content-type: application/json');

    	return $jwt;
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

	/**
	 *
	 * Block comment
	 *
	 */
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

	public function nullToken($CustomerID){
		// $secretKey = base64_decode(getenv('jwtKey'));
		// $token = JWT::decode($jwt, $secretKey, array('HS512'));
		$stored = CustLogin::nullToken($CustomerID);
		if(!$stored){
			return false;
		}
		return true;
	}

	/**
	 *
	 * Block comment
	 *
	 */
	public function storeToken($jwt, $CustomerID){
		$secretKey = base64_decode(getenv('jwtKey'));
		$token = JWT::decode($jwt, $secretKey, array('HS512'));
		$stored = CustLogin::updateToken($token->jti, $CustomerID);
		if(!$stored){
			return false;
		}
		return true;
	}

	/**
	 *
	 * Generate json for response for login route
	 *
	 */
	public function loginValues($customer){
		$jsonArray = array();
		$fname = $customer->getValueEncoded('FName');
		$lname = $customer->getValueEncoded('LName');
		$jsonArray['CustomerID'] = $customer->getValueEncoded('CustomerID');
		$jsonArray['status'] = 1;
		$jsonArray['fname'] = $fname;
		$jsonArray['lname'] = $lname;
		$jsonArray['userMessage'] = "Welcome $fname $lname.";
		return $jsonArray;
	}

	/**
	 *
	 * Block comment
	 *
	 */
	public function validateToken($jwt){
		try{
			$secretKey = base64_decode(getenv('jwtKey'));
			$token = JWT::decode($jwt, $secretKey, array('HS512'));
		} catch(ExpiredException $e) { // if result expired_token go to login page
	   		return $response->json($this->errorJson($e->getMessage(), 'expired_token'));
	    } catch(DomainException $e) {
	   		return $response->json($this->errorJson($e->getMessage(), 'invalid_domain'));
	    } catch(BeforeValidException $e){
	   		return $response->json($this->errorJson($e->getMessage(), 'before_valid'));
	    }
	}

	/**
	 *
	 * Block comment
	 *
	 */
	public function validateTokenInDatabase($jwt, $CustomerID){
		$secretKey = base64_decode(getenv('jwtKey'));
		$token = JWT::decode($jwt, $secretKey, array('HS512'));
		$stored = CustLogin::retrieveTokenInDatabase($CustomerID);
		if($token->jti == $stored['jwt_token']){
			return true;
		} else {
			return false;
		}
	}

	/**
	 *
	 * Decrypt values from request data input field with RNCryptor
	 *
	 */
	public function decryptValues($requestData){
		$password = getenv("CRYPTOR");
		$decryptor = new \RNCryptor\Decryptor();
		$plaintext = $decryptor->decrypt($requestData, $password);
		$data = json_decode($plaintext, true);
		return $data;
	}
	/**
	 *
	 * Encrypt values with RNCryptor for response data value
	 *
	 */
	public static function encryptValues($data){
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
	public function emailValues($customer){
		$jsonArray = array();
		$jsonArray['status'] = 1;
		$jsonArray['userMessage'] = "New password has been sent to your e-mail address. Please check your e-mail to retrieve your password.";
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