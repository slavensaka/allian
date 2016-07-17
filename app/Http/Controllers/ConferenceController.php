<?php

namespace Allian\Http\Controllers;

use \Dotenv\Dotenv;
use Services_Twilio;
use Services_Twilio_Twiml;
use Services_Twilio_Capability;
use Allian\Models\CustLogin;
use Allian\Models\OrderOnsiteInterpreter;
use Allian\Models\ConferenceSchedule;
use Allian\Models\TranslationOrders;
use Services_Twilio_TinyHttp;
use Allian\Helpers\TwilioConference\DatabaseAccess;
use Allian\Helpers\TwilioConference\ConferenceFunctions as ConfFunc;

class ConferenceController extends Controller {

	/**
	 *
	 * Schedule conference
	 *
	 */
	public function shedule_conference($order_id,$start_datetime,$end_datetime){
		$date = new \DateTime($start_datetime);
		$date->modify('-1 day');
		$start_datetime = $date->format('Y-m-d H:i');
		$date = new \DateTime($end_datetime);
		$date->modify('+1 day');
		$end_datetime = $date->format('Y-m-d H:i');
		$interpreter_code = $this->create_secret_code();
		$user_code = $this->create_secret_code();
		$conf_tag = strval($user_code);
		$conf_tag .= strval($interpreter_code);
		$query="INSERT INTO `conference_shedule`(`orderID`, `conf_tag`, `user_code`, `interpreter_code`, `start_datetime`, `end_datetime`) VALUES ('$order_id','$conf_tag','$user_code','$interpreter_code','$start_datetime','$end_datetime')";
		$db = new DatabaseAccess();
		$id = $db->db_insert($query);
	    $data['conf_id'] = $id;
		$data['user_code'] = $user_code;
		$data['interpreter_code'] = $interpreter_code;
		$data['conf_starts'] = $start_datetime;
		$data['conf_ends'] = $end_datetime;
		return $data;
	}

	/**  NOVI BROJ TREBA ZA CLIENTA ZA APP KOJI NAZIVOM SE SPAJA NA conf_tag na kojem je već interperter
     * @ApiDescription(section="Conference", description="Join the twilio scheduled session conference call.")
     * @ApiMethod(type="post")
     * @ApiRoute(name="/testgauss/conference")
     * @ApiBody(sample="{'token': 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzUxMiJ9.eyJpYXQiOjE0NjQ2MDE1MTUsImp0aSI6InAwaFpucWxqaUpqWStDdmdrb3c0MjJITTQ1TkYweFVobCtHU2lWZFwvUlN3PSIsImlzcyI6ImxvY2FsaG9zdCIsIm5iZiI6MTQ2NDYwMTUxNSwiZXhwIjoxNDY1ODExMTE1LCJkYXRhIjp7IlN1Y2Nlc3MiOiJTdWNjZXNzIn19.wwxlnjSCmInwNYinJ-LIyHMOys3oYTeoQem2MJTfgNREFZ8rcDB9uZ61Hw6vHIVMh_8BKzJUKS-_0nwhfrJVxQ'}")
     @ApiParams(name="token", type="string", nullable=false, description="Autentication token for users autentication.")
     * @ApiReturnHeaders(sample="HTTP 200 OK")
     * @ApiReturn(type="string", sample="{
     *  'data': {
	    'status': 1,
	    'userMessage': 'Twilio token',
	    'twilioToken': 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJzY29wZSI6InNjb3BlOmNsaWVudDpvdXRnb2luZz9hcHBTaWQ9QVBlMDAwOTk4ZjhiZGQ4MDhhNjA3NTllYjMyZjc5MmMxMiZhcHBQYXJhbXM9JmNsaWVudE5hbWU9NzQ3IHNjb3BlOmNsaWVudDppbmNvbWluZz9jbGllbnROYW1lPTc0NyIsImlzcyI6IkFDOTFjNDlkZjQ0MDZlNWMwOGE3NTQ2YWJhNDgwYTg5YjkiLCJleHAiOjE0Njc5Nzc4ODB9.hqimm091r4MnywuPzqq39uPyjleNUecgDn9XMwJuvjc'
	  	}
     * }")
     */
	public function conference($request, $response, $service, $app){
		if($request->token){
			// Validate token if not expired, or tampered with
			$this->validateToken($request->token);
			// Decrypt input data
			$data = $this->decryptValues($request->data);
			// Validate input data
			$service->validate($data['CustomerID'], 'Error: No customer id is present.')->notNull()->isInt();
			//Validate token in database
			$validated = $this->validateTokenInDatabase($request->token, $data['CustomerID']);
			if(!$validated){
	     		$ret = $this->errorJson("Authentication problems present");
	     		return $response->json(array('data' => $ret));
			}
			// Get a customer from the database based on the CustomerID
			$customer = CustLogin::get_customer($data['CustomerID']);
			// Generate a twilio capability token
			$token = ConfFunc::generateCapabilityToken($data['CustomerID']);
			// Return that Token
	    	return $response->json(array('data' => array('status' => 1, 'userMessage' => 'Twilio token', 'twilioToken' => $token)));
	    } else {
     		$ret = $this->errorJson("No token provided in request");
     		return $response->json(array('data' => $ret));
     	}
	}

	/**
     * @ApiDescription(section="ConferenceOut", description="When connected with twilioToken, generates the TwiML response.")
     * @ApiMethod(type="post")
     * @ApiRoute(name="/testgauss/conferenceOut")
     * @ApiBody(sample="{ 'CustomerID': '800', 'orderId': '5265'}")
     * @ApiParams(name="CustomerID", type="string", nullable=false, description="Customers id")
     * @ApiParams(name="orderId", type="string", nullable=false, description="Order")
     * @ApiReturnHeaders(sample="HTTP 200 OK")
     * @ApiReturn(type="string", sample="{'data': { 'twiML': 'twiML code for enqueue for a session with linguist'} }")
     */
	public function conferenceOut($request, $response, $service, $app){
		$service->validate($request->CustomerID, 'Error: No customer id is present.')->notNull()->isInt();
		$service->validate($request->orderId, 'Error: No order id is present.')->notNull()->isInt();
		$CallSid = $request->CallSid;

		// $customer = CustLogin::get_customer($request->CustomerID);
		// $order = OrderOnsiteInterpreter::get_interpret_order($request->orderId, '*');
		// $transOrder = TranslationOrders::getTranslationOrder($request->orderId, '*');
		$conference = ConferenceSchedule::get_conference("TODO REMOVE", $request->orderId, '*');
		// Is client verified, by getting the customer by id and then checking it's orderId in the conference_schedule
		// $verified = ConfFunc::verify_caller($conference['user_code']);

		// $limit=ConfFunc::chech_limit($code,$count); // NE vidim $code

		// ConfFunc::set_pre_log($conference['user_code'], $CallSid);

		// $service->verified = $verified;
		// $service->CustomerID = $request->CustomerID;
		// $service->orderId = $request->orderId;
		$service->v_code = $conference['user_code'];

		$service->render('./resources/views/twilio/conference/confOut.php');
		// $service->render('./resources/views/twilio/conference/conferenceOut.php');
	}

	/**
	 *
	 * Block comment
	 *
	 */
	public function conferenceCallback($request, $response, $service, $app){
		$user_code =  $request->user_code;
		ConfFunc::set_pre_log($user_code, $request->CallSid); // WHY is here
		ConfFunc::set_post_log($_REQUEST['CallSid']);
		ConfFunc::remove_expired_shedule();
	}

}

