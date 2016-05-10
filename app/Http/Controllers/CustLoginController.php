<?php

namespace Allian\Http\Controllers;

use PHPMailer;
use Allian\Models\CustLogin;

class CustLoginController extends Controller {
	 /**
     * @ApiDescription(section="Login", description="Authenticate a user and return a user message and status code")
     * @ApiMethod(type="post")
     * @ApiRoute(name="/testgauss/login")
     * @ApiBody(sample="{ 'email': 'lorem@net.hr', 'password': '12345' }")
     * @ApiParams(name="data", type="object", nullable=false, description="Users email & password json object to authenticate")
     * @ApiReturnHeaders(sample="HTTP 200 OK")
     * @ApiReturn(type="object", sample="{
     *  'status': 1,
     *  'fname':'Slaven',
     *	'lname': 'Sakačić',
     *  'userMessage': 'Authentication was unsuccessful, please try again.'
     * }")
     *	@ApiReturn(type="object", sample="{
     *  'status': 0,
     *  'userMessage': 'Authentication was unsuccessful, please try again.'
     * }")
     *	@ApiReturn(type="object", sample="{
     *  'status': 0,
     *  'userMessage': 'Invalid email address'
     * }")
     */
	public function postLogin($request, $response, $service, $app) {
		$data = json_decode($request->data, true);
		$service->validate($data['email'], 'Invalid email address')->isLen(3,200)->isEmail();
		$service->validate($data['password'], 'Error: no password present')->notNull();
    	$email = $data['email'];
    	$password = $data['password'];
    	header('Content-type: application/json');
		$customer = CustLogin::authenticate($email, $password);
		if(!$customer){
			$errorJson = $this->errorJson("Authentication was unsuccessful, please try again.");
			$response->json($errorJson);
			exit;
		}
		// return $customer;
		$user = $this->userValues($customer);
		// $novi = $customer->getValueEncoded('Street');
		$response->json($user);
	}

	  /**
     * @ApiDescription(section="Register", description="Register a new user")
     * @ApiMethod(type="post")
     * @ApiRoute(name="/testgauss/register")
     * @ApiBody(sample="{ 'fname': 'Slaven', 'lname': 'Sakačić', 'email': 'lorem@net.hr', 'phone': '+13477960949', 'password': '12345', 'phone_password': '45435', 'services': ['telephonic_interpreting', 'translation_services', 'onsite_interpreting', 'transcription_services'] }")
     * @ApiParams(name="data", type="object", nullable=false, description="User registration information. fname, lname, email, phone, password, phone_password, services")
     * @ApiReturnHeaders(sample="HTTP 200 OK")
     * @ApiReturn(type="object", sample="{
     *  'status': 1,
     *  'developerMessage':'User successfully inserted into the database',
     *  'userMessage': 'Successful registration!'
     * }")
     *	@ApiReturn(type="object", sample="{
     *  'status': 0,
     * 	'developerMessage': 'Integrity constraint violation 1062 Duplicate entry lorem@net.hr for key Email_2',
     *  'userMessage': 'Error: Email already taken.'
     * }")
     */
	public function postRegister($request, $response, $service, $app) {
		// ENCODE IOS JSON,
		// TODO POGLEDATI PHPEMAILER
		$data = json_decode($request->data, true);
		$service->validate($data['fname'], 'Error: no first name is present')->isLen(3,200)->notNull();
		$service->validate($data['lname'], 'Error: no last name is present')->isLen(3,200)->notNull();
		$service->validate($data['email'], 'Invalid email address')->isLen(3,200)->isEmail();
		$service->validate($data['phone'], 'Invalid phone number')->isLen(3,200)->notNull();
		$service->validate($data['password'], 'Error: no password present')->isLen(3,200)->notNull();
		$service->validate($data['phone_password'], 'Error: no phone password present')->isLen(3,200)->notNull()->isInt();
		// $service->validate($data['services'], 'Invalid services')->;
		// $service->validate($data['token'], 'No stripe token provided')->notNull();
		header('Content-type: application/json');
		$customerRegister = CustLogin::register($data);
		if(!$customerRegister){
			$errorJson = $this->errorJson("An error occured during registration");
			$response->json($errorJson);
			exit;
		}
		$successJson = $this->successJson("Registration Succesfull");
		$response->json($customerRegister);
	}

	/**
     * @ApiDescription(section="Forgot", description="Check if valid email in database, then change password in database & email user the user the new password")
     * @ApiMethod(type="post")
     * @ApiRoute(name="/testgauss/forgot")
     * @ApiBody(sample="{'email': 'lorem@net.hr'}")
     *@ApiParams(name="data", type="object", nullable=false, description="Users email to evaluate password recovery.")
     * @ApiReturnHeaders(sample="HTTP 200 OK")
     * @ApiReturn(type="object", sample="{
     *  'status': 1,
     *  'userMessage': 'Email has been sent.'
     * }")
     */
	public function postForgot($request, $response, $service, $app) {
		$data = json_decode($request->data, true);
		$service->validate($data['email'], 'Invalid email address.')->isLen(3,200)->isEmail();
		$email = $data['email'];
		header('Content-type: application/json');
		$CustomerID = CustLogin::checkEmail($email);
		if(!$CustomerID){
			return $response->json("No user found with that email.");
			exit;
		}
		$customer = CustLogin::getCustomer($CustomerID['CustomerID']);
		if(!$customer){
			return $response->json("No user found with that id");
		}
		//GENERATE NEW PASS
		$pass = $this->generatePassword();
		//SEND EMAIL WITH NEW PASS
		$sentEmail = $this->newPassEmail($email, $customer->getValueEncoded('FName'), $pass);
		if(!$sentEmail){
			return $response->json("Error sending email");
		}
		// INSERT PASS IN CustomerID
		$insertPass = CustLogin::insertPass($pass, $CustomerID['CustomerID']);
		if(!$insertPass){
			return $response->json("Couldn't update users password in database.");
		}
		$user = $this->emailValues($customer);
		$response->json($user);
	}

	public function newPassEmail($email, $FName, $LoginPassword){
		//SMTP needs accurate times, and the PHP time zone MUST be set
		//This should be done in your php.ini, but this is how to do it if you don't have access to that
		date_default_timezone_set('Etc/UTC');

		$message = file_get_contents('resources/views/emails/newpassword.php');
		$message = str_replace('%FName%', $FName, $message);
		$message = str_replace('%LoginPassword%', $LoginPassword, $message);

		$mail = new PHPMailer;
		$mail->isSMTP();
		// $mail->SMTPDebug = 2;
		// $mail->Debugoutput = 'html';
		$mail->CharSet='UTF-8';
		$mail->Host = getenv('MAIL_HOST');
		$mail->Port = getenv('MAIL_PORT');
		$mail->SMTPSecure = getenv('MAIL_ENCRYPTION');
		$mail->SMTPAuth = true;
		$mail->Username = getenv('MAIL_USERNAME');
		$mail->Password = getenv('MAIL_PASSWORD');
		$mail->setFrom('cs@alliantranslate.com', 'Allian Translate');
		$mail->addReplyTo('cs@alliantranslate.com', 'Allian Translate');
		$mail->addAddress($email, $FName);
		$mail->IsHTML(true);
		$mail->Subject = 'Allian Translate new password.';

		$mail->MsgHTML($message);
		// return true;
		$mail->send();
		if (!$mail->send()) {
		   return false;
		} else {
		    return true;
		}
	}

	public function userValues($customer){
		$jsonArray = array();
		$fname = $customer->getValueEncoded('FName');
		$lname = $customer->getValueEncoded('LName');
		$jsonArray['status'] = 1;
		$jsonArray['fname'] = $fname;
		$jsonArray['lname'] = $lname;
		$jsonArray['userMessage'] = "Authentication was successful for $fname $lname";
		return $jsonArray;
	}

	public function emailValues($customer){
		$jsonArray = array();
		$jsonArray['status'] = 1;
		$jsonArray['userMessage'] = "Email has been sent.";
		return $jsonArray;
	}
}