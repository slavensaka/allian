<?php

namespace Allian\Http\Controllers;

use Allian\Models\CustLogin;

class CustLoginController extends Controller {

	/**
     * @ApiDescription(section="Login", description="Autenticate customer within database")
     * @ApiMethod(type="post")
     * @ApiRoute(name="/testgauss/login")
     * @ApiParams(name="email", type="string", nullable=false, description="Users email address for authentication")
     * @ApiParams(name="password", type="string", nullable=false, description="Users password for authentication")
     * @ApiReturnHeaders(sample="HTTP 200 OK")
     * @ApiReturnHeaders(sample="HTTP 402 OK")
     * @ApiReturn(type="object", sample="{
     *  'Street':'string',
     *  'transaction_status':'string'
     * }")
     */
	public function postLogin($request, $response, $service, $app) {

        	$service->validateParam('email', 'Invalid Email')->isLen(3,200)->isEmail();
        	$service->validateParam('password', 'Invalid Password')->notNull();
        	$email = $request->email;
        	$password = $request->password;



		$customer = CustLogin::authenticate($email, $password);
		if(!$customer){
			return "Nema usera";
		}
		// return $customer;

		$novi = $customer->getValueEncoded('Street');
		header('Content-type: application/json');
		$response->json($novi);
		// $ll = json_encode($novi);
		// return $ll;
	}

	/**
     * @ApiDescription(section="Customer", description="Autenticate customer")
     * @ApiMethod(type="post")
     * @ApiRoute(name="/user/get/{id}")
     * @ApiParams(name="id", type="integer", nullable=false, description="User id")
     * @ApiParams(name="data", type="object",sample="{'user_id':'int','user_name':'string','profile':{'email':'string','age':'integer'}}")
     * @ApiHeaders(name="Test", type="test", description="Testing the header")
     * @ApiReturnHeaders(sample="HTTP 200 OK")
     * @ApiReturn(type="object", sample="{
     *  'transaction_id':'int',
     *  'transaction_status':'string'
     * }")
     */
	public function postRegister($request, $response, $service, $app) {
		//ENCODE IOS JSON,
		// TODO POGLEDATI PHPEMAILER
		$customer = CustLogin::register($request->data);

		header('Content-type: application/json');
		return $request->data;
	}

	/**
     * @ApiDescription(section="Customer", description="Autenticate customer")
     * @ApiMethod(type="post")
     * @ApiRoute(name="/user/get/{id}")
     * @ApiParams(name="id", type="integer", nullable=false, description="User id")
     * @ApiParams(name="data", type="object",sample="{'user_id':'int','user_name':'string','profile':{'email':'string','age':'integer'}}")
     * @ApiHeaders(name="Test", type="test", description="Testing the header")
     * @ApiReturnHeaders(sample="HTTP 200 OK")
     * @ApiReturn(type="object", sample="{
     *  'transaction_id':'int',
     *  'transaction_status':'string'
     * }")
     */
	public function postForgotPassword($request, $response, $service, $app) {
		return "TEsting";
	}


// $CustomerID = isset( $_GET["CustomerID"] ) ? (int)$_GET["CustomerID"] : 0;
		// $CustomerID = 304;
		// if ( !$customer = CustLogin::getCustLogin($CustomerID)) {
		//   echo 'Error: Customer not found.';
		//   exit;
		// }
		// header('Content-type: application/json');
		// return json_encode($customer);

}