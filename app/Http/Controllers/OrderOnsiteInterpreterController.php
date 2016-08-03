<?php

namespace Allian\Http\Controllers;

use Allian\Models\OrderOnsiteInterpreter;
use \Dotenv\Dotenv;
use Database\Connect;
use Allian\Models\LangList;
use Allian\Models\CustLogin;
use Allian\Helpers\Push\PushNotification;
use Allian\Helpers\TwilioConference\ConferenceFunctions;

class OrderOnsiteInterpreterController extends Controller {

	/**
     * @ApiDescription(section="ScheduledSessions", description="Retrieve scheduled session.")
     * @ApiMethod(type="post")
     * @ApiRoute(name="/testgauss/scheduledSessions")
     * @ApiBody(sample="{'data': {
    	'CustomerID': '800'
  		}, 'token': 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzUxMiJ9.eyJpYXQiOjE0NjQ1OTMzNjcsImp0aSI6IlJoOGpiMVhUZHFvUDVDVUVSQ29VY3pWR0dnSVFsQWJ1bFwvRFp1U2pcL050OD0iLCJpc3MiOiJsb2NhbGhvc3QiLCJuYmYiOjE0NjQ1OTMzNjcsImV4cCI6MTQ2NTgwMjk2NywiZGF0YSI6eyJTdWNjZXNzIjoiU3VjY2VzcyJ9fQ.JDwNdycstmqNC0dyrNgNuik_zXCYbx3PwbIkdTX7is3oDrQr6CKQ6mREUt-9tbOys361mcH1kyXaahn9Y2tTRg'}")
	     @ApiParams(name="token", type="string", nullable=false, description="Autentication token for users autentication.")
	     @ApiParams(name="data", type="string", nullable=false, description="Customer ID.")
	     * @ApiReturnHeaders(sample="HTTP 200 OK")
	     * @ApiReturn(type="string", sample="{ 'data': {
	    'status': 1,
	    'userMessage': 'Scheduled Sessions',
	    'scheduledSessions': [
	      {
	        'date': '2015-05-24',
	        'schedulingType': 'Conference Call',
	        'upcoming': 'upcoming',
	        'orderId': '5266'
	      },

	      {
	        'date': '2015-05-13',
	        'schedulingType': 'Interpreters call',
	        'upcoming': 'completed',
	        'orderId': '5267'
	      }

	    ]
		} }")
     */
	public function scheduledSessions($request, $response, $service, $app) {
		// if($request->token){
		// 	// Validate token if not expired, or tampered with
		// 	$this->validateToken($request->token);
		// 	// Decrypt data
			$data = $this->decryptValues($request->data);
		// 	// Validate CustomerId
		// 	$service->validate($data['CustomerID'], 'Error: No customer id is present.')->notNull()->isInt();
		// 	// Validate token in database for customer stored
		// 	$validated = $this->validateTokenInDatabase($request->token, $data['CustomerID']);
		// 	// If error validating token in database
		// 	if(!$validated){
		// 		$base64Encrypted = $this->encryptValues(json_encode($this->errorJson("Authentication problems present")));
	 //     		return $response->json(array('data' => $base64Encrypted));
		// 	}
			$result = OrderOnsiteInterpreter::getOrderOnsiteInterpreters($data['CustomerID']);

			$arr = array();
			while ($row = mysqli_fetch_array($result)) {
				if($row['scheduling_type'] == 'get_call'){
					$date = date('m.d.Y', strtotime($row['assg_frm_date']));
					$schedulingType = 'Interpreters call';
					$orderId = $row['orderID'];
				} elseif($row['scheduling_type'] == 'conference_call'){
					$date = date('m.d.Y', strtotime($row['assg_frm_date']));
					$schedulingType = 'Conference Call';
					$orderId = $row['orderID'];
				} else {
					// return "No telephonic info found";
				}
				$frmT  = new \DateTime($row['assg_frm_date'] . ' ' . $row['assg_frm_st'], new \DateTimeZone($row['timezone']));
				$n = $frmT->getTimestamp();
				//check if it has expired
				if ((time() - $n) < 600){
				  $upcoming = 'upcoming';
				}else{
				  $upcoming = 'completed';
				}
				$arr[] = array('date' => $date, 'schedulingType' => $schedulingType, 'upcoming' => $upcoming, 'orderId' => $orderId);
			}
			$new = array('userMessage' => 'Scheduled Sessions', 'status' => 1, 'scheduledSessions' => $arr);
			return $response->json($new);
			// Encrypt format json response
			$base64Encrypted = $this->encryptValues(json_encode($new));
	     	return $response->json(array('data' => $base64Encrypted));
		// } else {
		// 	$base64Encrypted = $this->encryptValues(json_encode($this->errorJson("No token provided in request")));
  //    		return $response->json(array('data' => $base64Encrypted));
		// }
	}

	/**
     * @ApiDescription(section="ScheduledSessionsDetails", description="Retrieve scheduled session.")
     * @ApiMethod(type="post")
     * @ApiRoute(name="/testgauss/scheduledSessionsDetails")
     * @ApiBody(sample="{'data': {
    	'CustomerID': '800' ,
    	'orderId' : '2345'
  		}, 'token': 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzUxMiJ9.eyJpYXQiOjE0NjQ1OTMzNjcsImp0aSI6IlJoOGpiMVhUZHFvUDVDVUVSQ29VY3pWR0dnSVFsQWJ1bFwvRFp1U2pcL050OD0iLCJpc3MiOiJsb2NhbGhvc3QiLCJuYmYiOjE0NjQ1OTMzNjcsImV4cCI6MTQ2NTgwMjk2NywiZGF0YSI6eyJTdWNjZXNzIjoiU3VjY2VzcyJ9fQ.JDwNdycstmqNC0dyrNgNuik_zXCYbx3PwbIkdTX7is3oDrQr6CKQ6mREUt-9tbOys361mcH1kyXaahn9Y2tTRg'}")
     	@ApiParams(name="token", type="string", nullable=false, description="Autentication token for users autentication.")
     	@ApiParams(name="data", type="string", nullable=false, description="Customer ID.")
     	* @ApiReturnHeaders(sample="HTTP 200 OK")
     	* @ApiReturn(type="string", sample="{ 'data': {
	    'timezone': 'US/Central',
	    'date': '12.05.2016',
	    'schedulingType': 'Interpreters Call',
	    'userMessage': 'Scheduled Session',
	    'timeStarts': '01:00 PM',
	    'timeEnds': '04:00 PM',
	    'langFrom': 'Spanish',
	    'langTo': 'English',
	    'status': '1',
	    'orderId': '5267',
	    'userMessage': 'Scheduled Session'
	  } }")
     */
	public function scheduledSessionsDetails($request, $response, $service, $app) {
		if($request->token){
			// Validate token if not expired, or tampered with
			$this->validateToken($request->token);
			// Decrypt data
			$data = $this->decryptValues($request->data);
			// Validate CustomerId
			$service->validate($data['CustomerID'], 'Error: No customer id is present.')->notNull()->isInt();
			$service->validate($data['orderId'], 'Error: No order id is present.')->notNull()->isInt();
			// Validate token in database for customer stored
			$validated = $this->validateTokenInDatabase($request->token, $data['CustomerID']);
			// If error validating token in database
			if(!$validated){
	     		$base64Encrypted = $this->encryptValues(json_encode($this->errorJson("Authentication problems present")));
	     		return $response->json(array('data' => $base64Encrypted));
			}
			$result = OrderOnsiteInterpreter::get_interpret_order($data['orderId'], "*");
			if(!$result) {
 				$base64Encrypted = $this->encryptValues(json_encode($this->errorJson("Order specified was not found in our database catalog.")));
	     		return $response->json(array('data' => $base64Encrypted));
			}
		/* ==========================================================================
		   Return Array
		   ========================================================================== */
			$rArray['timezone'] =  $result['timezone'];
			$rArray['date'] =  str_replace("/", ".", date("m/d/Y", strtotime($result['assg_frm_date'])));
			if($result['scheduling_type'] == 'get_call'){
				$rArray['schedulingType'] = 'Interpreters call';
			} elseif($result['scheduling_type'] == 'conference_call'){
				$rArray['schedulingType'] = 'Conference Call';
			} else {
				$rArray['schedulingType'] = 'Other';
			}
			$rArray['timeStarts'] = date('h:i A', strtotime($result["assg_frm_st"]));
			$rArray['orderId'] = $result['orderID'];
			$rArray['timeEnds'] = date('h:i A', strtotime($result["assg_frm_en"]));
			$rArray['langFrom'] = trim(LangList::get_language_name($result["frm_lang"]));
			$rArray['langTo'] = trim(LangList::get_language_name($result["to_lang"]));
			$rArray['status'] = 1;
			$rArray['userMessage'] = 'Scheduled Session';
		/* ==========================================================================
	   End Return Array
	   ========================================================================== */
			// Encrypt format json response
			$base64Encrypted = $this->encryptValues(json_encode($rArray));
	     	return $response->json(array('data' => $base64Encrypted));
		} else {
			$base64Encrypted = $this->encryptValues(json_encode($this->errorJson("No token provided in request")));
     		return $response->json(array('data' => $base64Encrypted));
		}
	}

	/**
     * @ApiDescription(section="StoreDeviceToken", description="Store the devices token for the Customer identified by his ID.")
     * @ApiMethod(type="post")
     * @ApiRoute(name="/testgauss/storeDeviceToken")
     * @ApiBody(sample="{'data': {
    	'CustomerID': '800' ,
    	'deviceToken' : '8d0ed95e5eb05864d270f11c196e7871d0d685162597ffa721aba50745e'
  		}, 'token': 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzUxMiJ9.eyJpYXQiOjE0NjQ1OTMzNjcsImp0aSI6IlJoOGpiMVhUZHFvUDVDVUVSQ29VY3pWR0dnSVFsQWJ1bFwvRFp1U2pcL050OD0iLCJpc3MiOiJsb2NhbGhvc3QiLCJuYmYiOjE0NjQ1OTMzNjcsImV4cCI6MTQ2NTgwMjk2NywiZGF0YSI6eyJTdWNjZXNzIjoiU3VjY2VzcyJ9fQ.JDwNdycstmqNC0dyrNgNuik_zXCYbx3PwbIkdTX7is3oDrQr6CKQ6mREUt-9tbOys361mcH1kyXaahn9Y2tTRg'}")
     	@ApiParams(name="token", type="string", nullable=false, description="Autentication token for users autentication.")
     	@ApiParams(name="data", type="string", nullable=false, description="Customer ID.")
     	* @ApiReturnHeaders(sample="HTTP 200 OK")
     	* @ApiReturn(type="string", sample="{ 'data': {
			'status': 1,
    		'userMessage': 'Device token stored succesfully.'
	  } }")
     */
	public function storeDeviceToken($request, $response, $service, $app){
		if($request->token){
			// Validate token if not expired, or tampered with
			$this->validateToken($request->token);
			// Decrypt data
			$data = $this->decryptValues($request->data);
			// Validate CustomerId
			$service->validate($data['CustomerID'], 'Error: No customer id is present.')->notNull()->isInt();
			$service->validate($data['deviceToken'], 'Error: No device token is present.')->notNull();
			// Validate token in database for customer stored
			$validated = $this->validateTokenInDatabase($request->token, $data['CustomerID']);
			// If error validating token in database
			if(!$validated){
	     		$base64Encrypted = $this->encryptValues(json_encode($this->errorJson("Authentication problems present")));
	     		return $response->json(array('data' => $base64Encrypted));
			}
			// Store in database deviceToken
			$result = CustLogin::setDeviceToken($data['deviceToken'], $data['CustomerID']);
			if(!$result){
				$base64Encrypted = $this->encryptValues(json_encode($this->errorJson("Problem storing device token in database.")));
	     		return $response->json(array('data' => $base64Encrypted));
			}
			$rArray['status'] = 1;
			$rArray['userMessage'] = 'Device token stored successfully.';
			$base64Encrypted = $this->encryptValues(json_encode($rArray));
	     	return $response->json(array('data' => $base64Encrypted));
		} else {
			$base64Encrypted = $this->encryptValues(json_encode($this->errorJson("No token provided in request")));
     		return $response->json(array('data' => $base64Encrypted));
		}
	}

	/**
     * @ApiDescription(section="TestDeviceToken", description="Test the push notification for the device token if it's working. By receiveing a push notification on your ios device based on the deviceToken stored in the database.")
     * @ApiMethod(type="post")
     * @ApiRoute(name="/testgauss/testDeviceToken")
     * @ApiBody(sample="{'data': {
    	'CustomerID': '800'
  		}, 'token': 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzUxMiJ9.eyJpYXQiOjE0NjQ1OTMzNjcsImp0aSI6IlJoOGpiMVhUZHFvUDVDVUVSQ29VY3pWR0dnSVFsQWJ1bFwvRFp1U2pcL050OD0iLCJpc3MiOiJsb2NhbGhvc3QiLCJuYmYiOjE0NjQ1OTMzNjcsImV4cCI6MTQ2NTgwMjk2NywiZGF0YSI6eyJTdWNjZXNzIjoiU3VjY2VzcyJ9fQ.JDwNdycstmqNC0dyrNgNuik_zXCYbx3PwbIkdTX7is3oDrQr6CKQ6mREUt-9tbOys361mcH1kyXaahn9Y2tTRg'}")
     	@ApiParams(name="token", type="string", nullable=false, description="Autentication token for users autentication.")
     	@ApiParams(name="data", type="string", nullable=false, description="Customer ID.")
     	* @ApiReturnHeaders(sample="HTTP 200 OK")
     	* @ApiReturn(type="string", sample="{ 'data': {
			'status': 1,
    		'userMessage': 'Device token stored succesfully.'
	  } }")
     */
	public function testDeviceToken($request, $response, $service, $app){
		// if($request->token){
		// 	// Validate token if not expired, or tampered with
		// 	$this->validateToken($request->token);
			// Decrypt data
			// $data = $this->decryptValues($request->data);
			// Validate CustomerId
			// $service->validate($data['CustomerID'], 'Error: No customer id is present.')->notNull()->isInt();
			// // Validate token in database for customer stored
			// $validated = $this->validateTokenInDatabase($request->token, $data['CustomerID']);
			// // If error validating token in database
			// if(!$validated){
		 // 	 	$base64Encrypted = $this->encryptValues(json_encode($this->errorJson("Authentication problems present")));
		 // 	   	return $response->json(array('data' => $base64Encrypted));
			// }
			/*======  Seeds  ======*/
			$deviceToken = 'c585419b29a7bd1a94c7d3b220ad7b6e6de5cdffa6366d70f83c07681587d238';
			// $deviceToken = '1095b49dcdbc66320';
			$message = "Your scheduled conference call is about to start in 5 minutes. Translation: English <> Arabic. On date: 31.12.2017. Cost: 100$.";
			$twilioToken = ConferenceFunctions::generateCapabilityToken('784');
			$orderID = "5153";
			/*=====  End of Seeds  ======*/
			$result = PushNotification::push($deviceToken, $message, $orderID, $twilioToken, false);
			if(!$result){
				$base64Encrypted = $this->encryptValues(json_encode($this->errorJson("Problem sending push notification to device.")));
	     		return $response->json(array('data' => $base64Encrypted));
			}
			$rArray['status'] = 1;
			$rArray['userMessage'] = 'Test completed successfully';
			return $response->json($rArray['userMessage']);
			// $base64Encrypted = $this->encryptValues(json_encode($rArray));
	     	// return $response->json(array('data' => $base64Encrypted));
		// } else {
		// 	$base64Encrypted = $this->encryptValues(json_encode($this->errorJson("No token provided in request")));
  		//    		return $response->json(array('data' => $base64Encrypted));
		// }
	}

	/**
     * @ApiDescription(section="GaussAppScheduleCronJob", description="Every minute cron job on allian server, to find all scheduled sessions that will begin in 5 minutes * from now() GMT. And for all send them an
	 * push notification as an alert and narrow if conference_call or get_call")
     * @ApiMethod(type="post")
     * @ApiRoute(name="/testgauss/gaussAppScheduleCronJob")
     *
     */
	public function gaussAppScheduleCronJob($request, $response, $service, $app){
		$query = "SELECT orderID, scheduling_type, frm_lang, to_lang, customer_id, amount, onsite_con_phone, assg_frm_date, assg_frm_st, timezone FROM `order_onsite_interpreter` WHERE scheduling_type IN ('conference_call', 'get_call') AND push_notification_sent =0 AND is_phone =1 AND DATE_SUB(CONVERT_TZ(DATE_FORMAT(FROM_UNIXTIME(assg_frm_timestamp), '%Y-%c-%d %T:%f'), '-7:00', '-0:00'),INTERVAL 5 MINUTE) BETWEEN CONVERT_TZ(DATE_SUB(NOW(), INTERVAL 5 MINUTE), '-7:00', '-0:00') AND CONVERT_TZ(NOW(), '-7:00', '-0:00')";
		$con = Connect::con();
		$query_result = mysqli_query($con, $query);
		while ($rows = mysqli_fetch_assoc($query_result)) {
			$orderID = $rows['orderID'];
		    $scheduling_type = $rows["scheduling_type"];
		    $frm_lang = LangList::get_language_name($rows['frm_lang'], 'LangName');
		    $to_lang = LangList::get_language_name($rows['to_lang'], 'LangName');
		    $CustomerID = $rows['customer_id'];
		    $twilioToken = ConferenceFunctions::generateCapabilityToken($CustomerID);
		    $amount = $rows['amount'];
		    $date = $rows['assg_frm_st'] ." ". date('l', strtotime($$rows['assg_frm_date'])) . ' '. $rows['assg_frm_date'] . ' ' . $rows['timezone'];
		    if($scheduling_type == 'conference_call'){
		    	$message = "Your scheduled conference call is about to start in 5 minutes. Translation: $frm_lang <> $to_lang. On date: $date. Cost: $amount.";
		    } elseif($scheduling_type == 'get_call'){
		    	$onsite_con_phone = $rows['onsite_con_phone'];
		    	$message = "Your scheduled interpreter\'s call is about to start in 5 minutes. Translation: $frm_lang <> $to_lang. On date: $date. Cost: $amount. Call will be to $onsite_con_phone.";
			} else{
				exit();
			}
		    $customer = CustLogin::get_customer($CustomerID);
		    mysqli_query($con, "UPDATE `order_onsite_interpreter` SET push_notification_sent = 1 WHERE orderID = $orderID");
		    PushNotification::push($customer['deviceToken'], $message, $orderID, $twilioToken, false);
		    // PushNotification::push($customer['deviceToken'], $message, $orderID, true); //TODO FOR PRODUCTION
		}
	}

	/**
	 *
	 * Used for testing, Not used
	 *
	 */
	public function deviceToken($request, $response, $service, $app){
		if($request->token){
			// Validate token if not expired, or tampered with
			$this->validateToken($request->token);
			// Decrypt data
			$data = $this->decryptValues($request->data);
			// Validate CustomerId
			$service->validate($data['CustomerID'], 'Error: No customer id is present.')->notNull()->isInt();
			// Validate token in database for customer stored
			$validated = $this->validateTokenInDatabase($request->token, $data['CustomerID']);
			// If error validating token in database
			if(!$validated){
	     		$base64Encrypted = $this->encryptValues(json_encode($this->errorJson("Authentication problems present")));
	     		return $response->json(array('data' => $base64Encrypted));
			}
			$customer = CustLogin::get_customer($data['CustomerID']);

			$result = PushNotification::push();
			return $result;
			$base64Encrypted = $this->encryptValues(json_encode($result));
	     	return $response->json(array('data' => $base64Encrypted));
		} else {
			$base64Encrypted = $this->encryptValues(json_encode($this->errorJson("No token provided in request")));
     		return $response->json(array('data' => $base64Encrypted));
		}
	}
}