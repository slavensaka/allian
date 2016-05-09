<?php

namespace Allian\Http\Controllers;

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
		$customerRegister = CustLogin::register($data);
		header('Content-type: application/json');
		$response->json($customerRegister);
	}

	public function postForgotPassword($request, $response, $service, $app) {
		//Get email, check if valid
		// Search database for the email
		// Send an email to the user emial lorem@net.hr
		//Configure SMTP server for sending email
		// Email contains a link for
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
}