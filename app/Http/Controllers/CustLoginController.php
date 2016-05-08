<?php

namespace Allian\Http\Controllers;

use Allian\Models\CustLogin;

class CustLoginController extends Controller {
	 /**
     * @ApiDescription(section="Login", description="Autenticate user")
     * @ApiMethod(type="post")
     * @ApiRoute(name="/testgauss/login")
     * @ApiParams(name="email", type="string", nullable=false, description="Email to authenticate")
     * @ApiParams(name="password", type="string", nullable=false, description="Passwrod to authenticate")
     * @ApiReturnHeaders(sample="HTTP 200 OK")
     * @ApiReturn(type="object", sample="{
     *  'transaction_id':'int',
     *  'transaction_status':'string'
     * }")
     */
	public function postLogin($request, $response, $service, $app) {
        	$service->validateParam('email', 'Invalid Email')->isLen(3,200)->isEmail();
        	$service->validateParam('password', 'Invalid Password')->notNull();
        	$email = $request->email;
        	$password = $request->password;
        	header('Content-type: application/json');
		$customer = CustLogin::authenticate($email, $password);
		if(!$customer){
			return $response->json("Nema Korisnika");
		}
		// return $customer;

		$novi = $customer->getValueEncoded('Street');

		$response->json($novi);
		// $ll = json_encode($novi);
		// return $ll;
	}


	public function postRegister($request, $response, $service, $app) {

	}


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