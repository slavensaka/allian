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
	public function testing($request, $response, $service, $app) {
		$service->render('./docs/index.html');
	}

    public function postTesting($request, $response, $service, $app){
    	try {
        $service->validateParam('email')->isLen(3,200);
	    } catch (\Klein\Exceptions\ValidationException $e) {
	        echo 'Got ya!';
	    }
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