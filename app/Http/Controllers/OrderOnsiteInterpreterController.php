<?php

namespace Allian\Http\Controllers;

use Allian\Models\OrderOnsiteInterpreter;
use \Dotenv\Dotenv;
use Allian\Models\LangList;
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
	    'scheduledSessions': [
	      {
	        'date': '2015-05-24',
	        'schedulingType': 'Conference Call',
	        'upcoming': 'upcoming'
	      },

	      {
	        'date': '2015-05-13',
	        'schedulingType': 'Interpreters call',
	        'upcoming': 'completed'
	      }

	    ]
		} }")
     */
	public function ScheduledSessions($request, $response, $service, $app) {
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
			$getCall = array();
			$conferenceCall = array();
			while ($row = mysqli_fetch_array($result)) {
				if($row['scheduling_type'] == 'get_call'){
					$date = $row['assg_frm_date'];
					$schedulingType = 'Interpreters call';
					$orderId = $row['orderID'];
				} elseif($row['scheduling_type'] == 'conference_call'){
					$date = $row['assg_frm_date'];
					$schedulingType = 'Conference Call';
					$orderId = $row['orderID'];
				} else {
					// return "No telephonic info found";
				}

				$frmT  = new \DateTime($row['assg_frm_date'] . ' ' . $row['assg_frm_st'], new \DateTimeZone($row['timezone']));
				// $ne = strtotime($frmT['date']);
				$n = $frmT->getTimestamp();
				//check if it has expired (600 = 60*10 seconds = 10 minutes)
				if ((time() - $n) < 600){
				  $upcoming = 'upcoming';
				}else{
				  $upcoming = 'completed';
				}
				$arr[] = array('date' => $date, 'schedulingType' => $schedulingType, 'upcoming' => $upcoming, 'orderId' => $orderId);
			}
			// foreach($arr as $key =>  $r){
			// 	if($r['date'] == null){
			// 		unset($arr[$key], $arr[$key]);
			// 	}
			// }
			$new = array('status' => 1, 'scheduledSessions' => $arr);
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
	    'date': 'US/Central',
	    'schedulingType': 'regular',
	    'timeStarts': '01:00 PM',
	    'timeEnds': '04:00 PM',
	    'langFrom': 'Spanish',
	    'langTo': 'English',
	    'status': '1'
	  } }")
     */
	public function ScheduledSessionsDetails($request, $response, $service, $app) {
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

			// $result = OrderOnsiteInterpreter::getOrderOnsiteInterpreters($data['CustomerID']);

			$result = OrderOnsiteInterpreter::get_interpret_order($orderId, "*");
			if(!$result) {

			}
			$rArray['timezone'] =  $result['timezone'];
			$rArray['date'] =  $result['timezone'];
			$rArray['schedulingType'] =  $result['scheduling_type'];
			$rArray['timeStarts'] =  date('h:i A', strtotime($result["assg_frm_st"]));
			$rArray['timeEnds'] =  date('h:i A', strtotime($result["assg_frm_en"]));
			$rArray['langFrom'] =  trim(LangList::get_language_name($result["frm_lang"]));
			$rArray['langTo'] =  trim(LangList::get_language_name($result["to_lang"]));
			$rArray['status'] = 1;


		// 	"timezone": "", // order_onsite
		// "date": "",
		// "schedulingType": "Interpreters call", // Interpreters call, Confernce call
		// "timeStarts": "08:00 AM" //$project_start_time = date('h:i A', strtotime($interpret_order["assg_frm_st"]))
		// "timeEnds": "08:30 AM"	 //$project_end_time = date('h:i A', strtotime($interpret_order["assg_frm_en"]));"
		// "langFrom": "Arabic", //LangList::get_language_name($interpret_order["frm_lang"])
		// "langTo": "Spanish", //LangList::get_language_name($interpret_order["frm_lang"])

			// Encrypt format json response
			$base64Encrypted = $this->encryptValues(json_encode($rArray));
	     	return $response->json(array('data' => $base64Encrypted));
		} else {
			$base64Encrypted = $this->encryptValues(json_encode($this->errorJson("No token provided in request")));
     		return $response->json(array('data' => $base64Encrypted));
		}
	}
}