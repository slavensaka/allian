<?php

namespace Allian\Http\Controllers;

use \Dotenv\Dotenv;
use Database\Connect;
use Allian\Models\Login;
use Allian\Models\LangList;
use Allian\Models\TranslationOrders;
use Allian\Models\OrderOnsiteInterpreter;
use Allian\Helpers\Allian\TranslationFunctions;
use Allian\Helpers\Allian\ScheduleFunctions;

class TranslationOrdersController extends Controller {

	/**
     * @ApiDescription(section="OrderSummary", description="Retrieve the orders summary json.")
     * @ApiMethod(type="post")
     * @ApiRoute(name="/testgauss/orderSummary")
     * @ApiBody(sample="{'data': {
	    'CustomerID': '406'
	  	},
     	'token': 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzUxMiJ9.eyJpYXQiOjE0NjU1NDY1MjcsImp0aSI6IklGSTJTcmxlbWtQck1ncUZNSmV1RDZYYTlUTzRQbm02TmVGdThyK1VLV2c9IiwiaXNzIjoibG9jYWxob3N0IiwibmJmIjoxNDY1NTQ2NTI3LCJleHAiOjE1OTE2OTA1MjcsImRhdGEiOnsiU3VjY2VzcyI6IlN1Y2Nlc3MifX0.ff_JJqrXL1HLsTGRo7HA6q9YQJWiLaQRoVy0RcQYnDPpFQu-0HH1bYQ8PLHnyaOzSm3yYXkCle0gLd1O80vREg'}")
     *@ApiParams(name="data", type="string", nullable=false, description="Data")
     @ApiParams(name="token", type="string", nullable=false, description="Autentication token for users autentication.")
     * @ApiReturnHeaders(sample="HTTP 200 OK")
     * @ApiReturn(type="string", sample="{
     *  'data': {
	     'orderSummary': [
	     {
		'orderId': '3763',
        'orderTime': '2015-05-23',
        'cost': 'Total: 185$'
        }], '...': ''
	  	}
     * }")
     */
	public function orderSummary($request, $response, $service, $app) {
		if($request->token){
			// 	Validate token if not expired, or tampered with
			$this->validateToken($request->token);
			// 	Decrypt data
			$data = $this->decryptValues($request->data);
			// Validate input data
			$service->validate($data['CustomerID'], 'Error: No customer id is present.')->notNull()->isInt();
			//Validate the jwt token in the database
			$validated = $this->validateTokenInDatabase($request->token, $data['CustomerID']);
			// If error validating token in database
			if(!$validated){
	     		$base64Encrypted = $this->encryptValues(json_encode($this->errorJson("Authentication problems. CustomerID doesn't match that with token.")));
	     		return $response->json(array('data' => $base64Encrypted));
			}

			$interpret_order = OrderOnsiteInterpreter::getOrderOnsiteInterpretersSummary($data['CustomerID']);
			while ($row = mysqli_fetch_array($interpret_order)) {
				$order_id = $row["orderID"];
				$scheduling_type = $row["scheduling_type"];
				$assg_frm_timestamp = $row["assg_frm_timestamp"];
				if($scheduling_type == 'get_call'){
					if ((time() - $assg_frm_timestamp) < 600){
					  $ooo[] = array('order_id' =>$order_id, 'scheduling_type' => $scheduling_type);
					}
				} else{
					$ooo[] = array('order_id' =>$order_id, 'scheduling_type' => $scheduling_type);
				}
			}

			// If no orders are found
			if(empty($ooo)){
				$orders[] = array('orderId' => "", 'orderTime' => "", 'cost' => "");
				$base64Encrypted = $this->encryptValues(json_encode(array('orderSummary' => $orders, 'status' => 1, 'userMessage' => 'Orders Summary')));
				return $response->json(array('data' => $base64Encrypted));
			}

			foreach($ooo as $key => $value){
					$or[] =  TranslationOrders::getTranslationOrder($value['order_id'], '*');
			}

			foreach($or as $key => $value){
				$order_id = $value["order_id"];
				$order_time = str_replace("/", ".", date("d/m/Y", strtotime($value['order_time']))) . '.';
				$order_type = $value["order_type"];
				$cost = ($value["total_price"] - $value['discount']);
					$orders[] = array('orderId' => $order_id, 'orderTime' => $order_time, 'cost' => 'Total: ' . $cost . '$');
			}
			// return $response->json(array('data' => $orders));

			// Retrieve orders that are already paid, and are telephonic
			// $result = TranslationOrders::getTranslationOrders($data['CustomerID'], "*");
			// $orders = array();
			// while ($row = mysqli_fetch_array($result)) {
			// 	$order_id = $row["order_id"];
			// 	$order_time = str_replace("/", ".", date("d/m/Y", strtotime($row['order_time']))) . '.';
			// 	$order_type = $row["order_type"];
			// 	$cost = ($row["total_price"] - $row['discount']);
			// 		$orders[] = array('orderId' => $order_id, 'orderTime' => $order_time, 'cost' => 'Total: ' . $cost . '$');
			// }

			// Return response json
			$base64Encrypted = $this->encryptValues(json_encode(array('orderSummary' => $orders, 'status' => 1, 'userMessage' => 'Orders Summary')));
			return $response->json(array('data' => $base64Encrypted));
		} else {
			$base64Encrypted = $this->encryptValues(json_encode($this->errorJson("No token provided in request.")));
     		return $response->json(array('data' => $base64Encrypted));
		}
	}

	/**
     * @ApiDescription(section="OrderSummaryDetails", description="Retrieve the orders summaries details json.")
     * @ApiMethod(type="post")
     * @ApiRoute(name="/testgauss/orderSummaryDetails")
     * @ApiBody(sample="{'data': {
	    'CustomerID': '406',
	    'orderID': '3763'
	  },
     'token': 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzUxMiJ9.eyJpYXQiOjE0NjU1NDY1MjcsImp0aSI6IklGSTJTcmxlbWtQck1ncUZNSmV1RDZYYTlUTzRQbm02TmVGdThyK1VLV2c9IiwiaXNzIjoibG9jYWxob3N0IiwibmJmIjoxNDY1NTQ2NTI3LCJleHAiOjE1OTE2OTA1MjcsImRhdGEiOnsiU3VjY2VzcyI6IlN1Y2Nlc3MifX0.ff_JJqrXL1HLsTGRo7HA6q9YQJWiLaQRoVy0RcQYnDPpFQu-0HH1bYQ8PLHnyaOzSm3yYXkCle0gLd1O80vREg'}")
     *@ApiParams(name="data", type="string", nullable=false, description="Data")
     @ApiParams(name="token", type="string", nullable=false, description="Autentication token for users autentication.")
     * @ApiReturnHeaders(sample="HTTP 200 OK")
     * @ApiReturn(type="string", sample="{
     *  'data': {
        'projectDescription': 'TEST',
        'projectId': '3763',
        'projectLangs': 'Test <> English',
        'projectStartDate': '2015-05-24 Sunday',
        'projectEndDate': '2015-05-24 Sunday',
        'minutesScheduled': '60 Minutes',
        'timezone': 'US/Pacific',
        'timeStarts': '08:00 AM',
        'timeEnds': '09:00 AM',
        'conferenceDialNumber': '+18555129043',
        'conferenceSecretCode': '39118',
        'daily': 'ATS - Regular Telephonic Scheduling ($3/Min) for 60 minutes',
        'dailyPrice': '$180.00',
        'conferencePresent': 'Conference Calling Fee $5.00',
        'grandTotal': '185.00'
    	}
     * }")
     */
	public function orderSummaryDetails($request, $response, $service, $app) {
		if($request->token){
			// Validate token if not expired, or tampered with
			$this->validateToken($request->token);
			// Decrypt data
			$data = $this->decryptValues($request->data);
			// Validate inputed data
			$service->validate($data['orderId'], 'Error: No order id is present.')->notNull();
			$service->validate($data['CustomerID'], 'Error: No customer id is present.')->notNull();
			//Validate the jwt token in the database
			$validated = $this->validateTokenInDatabase($request->token, $data['CustomerID']);
			// If error validating token in database
			if(!$validated){
	     		$base64Encrypted = $this->encryptValues(json_encode($this->errorJson("Authentication problems. CustomerID doesn't match that with token.")));
	     		return $response->json(array('data' => $base64Encrypted));
			}



			$display_order = TranslationFunctions::order_onsite_template($data['orderId'], 'account');

			if(!$display_order){
				$base64Encrypted = $this->encryptValues(json_encode($this->errorJson("Order not Found.")));
	     		return $response->json(array('data' => $base64Encrypted));
			}
			$base64Encrypted = $this->encryptValues(json_encode($display_order));
			return $response->json(array('data' => $base64Encrypted));
		} else {
			$base64Encrypted = $this->encryptValues(json_encode($this->errorJson("No token provided in request.")));
     		return $response->json(array('data' => $base64Encrypted));
		}
	}
}