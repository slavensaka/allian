<?php

namespace Allian\Http\Controllers;

use Allian\Models\OrderOnsiteInterpreter;
use \Dotenv\Dotenv;
use Database\Connect;
use Allian\Models\LangList;
use Allian\Models\CustLogin;
use Allian\Helpers\Push\PushNotification;
use Allian\Helpers\TwilioConference\ConferenceFunctions;
use Allian\Helpers\TwilioConference\ConferenceFunctions as ConfFunc;


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
			// Encrypt format json response
			$base64Encrypted = $this->encryptValues(json_encode($new));
	     	return $response->json(array('data' => $base64Encrypted));
		} else {
			$base64Encrypted = $this->encryptValues(json_encode($this->errorJson("No token provided in request")));
     		return $response->json(array('data' => $base64Encrypted));
		}
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
			$rArray['twilioToken'] = ConfFunc::generateCapabilityToken($data['CustomerID']);
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
	 *
	 * Block comment
	 *
	 */
	public function gaussAppScheduleCronJob($request, $response, $service, $app){
		$con = Connect::con();
		$server = trim($_SERVER['HTTP_HOST']);
		$server = trim($server);
		if($server == "localhost"){
		$query = "SELECT orderID, scheduling_type, frm_lang, to_lang, customer_id, amount, onsite_con_phone, assg_frm_date, assg_frm_st, timezone FROM `order_onsite_interpreter` WHERE scheduling_type IN ('conference_call', 'get_call') AND push_notification_sent =0 AND is_phone =1 AND DATE_SUB(CONVERT_TZ(DATE_FORMAT(FROM_UNIXTIME(assg_frm_timestamp), '%Y-%c-%d %T:%f'), '+2:00', '-0:00'),INTERVAL 5 MINUTE) BETWEEN CONVERT_TZ(DATE_SUB(NOW(), INTERVAL 5 MINUTE), '+2:00', '-0:00') AND CONVERT_TZ(NOW(), '+2:00', '-0:00')";
		} else if($server == "alliantranslate.com"){
			$query = "SELECT orderID, scheduling_type, frm_lang, to_lang, customer_id, amount, onsite_con_phone, assg_frm_date, assg_frm_st, timezone FROM `order_onsite_interpreter` WHERE scheduling_type IN ('conference_call', 'get_call') AND push_notification_sent =0 AND is_phone =1 AND DATE_SUB(CONVERT_TZ(DATE_FORMAT(FROM_UNIXTIME(assg_frm_timestamp), '%Y-%c-%d %T:%f'), '-7:00', '-0:00'),INTERVAL 5 MINUTE) BETWEEN CONVERT_TZ(DATE_SUB(NOW(), INTERVAL 5 MINUTE), '-7:00', '-0:00') AND CONVERT_TZ(NOW(), '-7:00', '-0:00')";
		}
		$queryResult = mysqli_query($con, $query);
		while($rows = mysqli_fetch_assoc($queryResult)) {
			$orderID = $rows['orderID'];
		    $scheduling_type = $rows["scheduling_type"];
		    $frm_lang = LangList::get_language_name($rows['frm_lang'], 'LangName');
		    $to_lang = LangList::get_language_name($rows['to_lang'], 'LangName');
		    $CustomerID = $rows['customer_id'];
		    $twilioToken = ConferenceFunctions::generateCapabilityToken($CustomerID);
		    $amount = $rows['amount'];
		    $date = $rows['assg_frm_st'] ." ". date('l', strtotime($$rows['assg_frm_date'])) . ' ' . $rows['assg_frm_date'] . ' ' . $rows['timezone'];
		    if($scheduling_type == 'conference_call'){
		    	$message = "Your scheduled conference call is about to start in 5 minutes. Phone interpreting:" . trim($frm_lang) . " <> " . trim($to_lang) . ". On date: $date.";
		    } elseif($scheduling_type == 'get_call'){
		    	$onsite_con_phone = $rows['onsite_con_phone'];
		    	$message = "Your scheduled interpreters call is about to start in 5 minutes. Phone interpreting:" . trim($frm_lang) . " <> " . trim($to_lang) . ". On date: $date. Call will be to $onsite_con_phone.";
			} else{
				exit();
			}
		    mysqli_query($con, "UPDATE `order_onsite_interpreter` SET push_notification_sent = 1 WHERE orderID = $orderID");
		    $customer = CustLogin::get_customer($CustomerID);
		    $deviceToken = (string)$customer['deviceToken'];
			if($deviceToken == null){
				exit();
			}
			// The private key's passphrase
			$passphrase = getenv('PUSH_PASS_PHRASE');
			// Put your alert message here:
			$ctx = stream_context_create();
			stream_context_set_option($ctx, 'ssl', 'local_cert', '/home/alliantranslate/public_html/testgauss/cert/allianpushcertifikatprod.pem');
			// stream_context_set_option($ctx, 'ssl', 'local_cert', '/home/alliantranslate/public_html/testgauss/cert/allianpushcertfikat.pem');
			stream_context_set_option($ctx, 'ssl', 'passphrase', $passphrase);
			// Open a connection to the APNS server
			$fp = stream_socket_client('ssl://gateway.push.apple.com:2195', $err, $errstr, 60, STREAM_CLIENT_CONNECT|STREAM_CLIENT_PERSISTENT, $ctx);
			// $fp = stream_socket_client('ssl://gateway.sandbox.push.apple.com:2195', $err, $errstr, 60, STREAM_CLIENT_CONNECT|STREAM_CLIENT_PERSISTENT, $ctx);
			if(!$fp){
				exit();
			}
			stream_set_blocking($fp, 0);
			// Create the payload body
			$body = array('aps' => array('alert' => $message, 'sound' => 'default', 'badge' => 1), 'orderID' => $orderID, 'twilioToken' => $twilioToken);
			// Encode the payload as JSON
			$payload = json_encode($body);
			// Build the binary notification
			// $msg = chr(0) . pack('n', 32) . pack('H*', str_replace(' ', '', sprintf('%u', CRC32($deviceToken)))) . pack('n', strlen($payload)) . $payload;
			$msg = chr(0) . pack('n', 32) . pack('H*', $deviceToken) . pack('n', strlen($payload)) . $payload;
			// Send it to the server
			$result = fwrite($fp, $msg, strlen($msg));
			mail('slavensakacic@gmail.com',"TestGauss Push CronJob FIRED!", "MESSAGE: $message ||| PAYLOAD JSON_ENCODED: $payload ||| DEVICE TOKEN: $deviceToken");
		}
	}

}