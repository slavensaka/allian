<?php

namespace Allian\Http\Controllers;

use Allian\Models\CustLogin;

class CustLoginController extends Controller {

	/**
     * @ApiDescription(section="User", description="Get information about user")
     * @ApiMethod(type="get")
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
	public function postLogin($request, $response, $service, $app) {
		// $lorem = json_encode($request->id(true));
		try { // Za sada TODO smanjit kod
        	$service->validate($request->email)->isLen(3,200);
        	$email = $request->email;
	    } catch (\Klein\Exceptions\ValidationException $e) {
	        return "Invalid Email";
	    }
	    try {
        	$service->validate($request->password)->notNull();
        	$password = $request->password;
	    } catch (\Klein\Exceptions\ValidationException $e) {
	        return "Invalid Password";
	    }

		$customer = CustLogin::authenticate($email, $password);
		if(!$customer){
			return "Nema usera";
		}
		// return $customer;
		$novi = $customer->getValueEncoded('Street');
		$ll = json_encode($novi);
		return $ll;
	}

    public function postTesting($request, $response, $service, $app){

    	header('Content-type: application/json');


    	// $CustomerID = isset( $_GET["CustomerID"] ) ? (int)$_GET["CustomerID"] : 0;
		// $CustomerID = 304;
		// if ( !$customer = CustLogin::getCustLogin($CustomerID)) {
		//   echo 'Error: Customer not found.';
		//   exit;
		// }
		// header('Content-type: application/json');
		// return json_encode($customer);
    }


}