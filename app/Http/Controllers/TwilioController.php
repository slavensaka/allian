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

class TwilioController extends Controller {

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

	/**
     * @ApiDescription(section="TwilioConference", description="Join the twilio conference call.")
     * @ApiMethod(type="get")
     * @ApiRoute(name="/testgauss/twilioConference")
     * @ApiBody(sample="{ 'data': ''}")
     * @ApiReturnHeaders(sample="HTTP 200 OK")
     * @ApiReturn(type="object", sample="{'nesto': '', 'nesto': '' }")
     */
	public function twilioConference($request, $response, $service, $app){
		// 	if($request->token){
		// 		// Validate token if not expired, or tampered with
		// 		$this->validateToken($request->token);
		// 		// Decrypt data
		// 		$data = $this->decryptValues($request->data);
		// 		// Validate CustomerId
		// 		$service->validate($data['CustomerID'], 'Error: No customer id is present.')->notNull()->isInt();
		// 		// Validate token in database for customer stored
		// 		$validated = $this->validateTokenInDatabase($request->token, $data['CustomerID']);
		// 		// If error validating token in database
		// 		if(!$validated){
		// 			$base64Encrypted = $this->encryptValues(json_encode($this->errorJson("Authentication problems present")));
		//      		return $response->json(array('data' => $base64Encrypted));
		// 		}

		$data = $this->decryptValues($request->data);

		$service->validate($data['CustomerID'], 'Error: No customer id is present.')->notNull()->isInt();
		$service->validate($data['orderId'], 'Error: No order id is present.')->notNull()->isInt();



		$customer = CustLogin::get_customer($data['CustomerID']);
		$order = OrderOnsiteInterpreter::get_interpret_order($data['orderId'], '*');
		$transOrder = TranslationOrders::getTranslationOrder($data['orderId'], '*');
		$conference = ConferenceSchedule::get_conference("TODO REMOVE", $data['orderId'], '*');
		$conferenceSecretCode =  $order['orderID'];

		$verified = ConfFunc::verify_caller($conferenceSecretCode);
		return $response->json($conferenceSecretCode);




		$token = ConfFunc::generateCapabilityToken($customer['CustomerID']);
		$service->render('./resources/views/twilio/conference/twilioConference.php');

		// } else {
		// 	$base64Encrypted = $this->encryptValues(json_encode($this->errorJson("No token provided in request")));
  		//return $response->json(array('data' => $base64Encrypted));
		// }
	}

	public function conferenceEntryPoint($request, $response, $service, $app){

		$data = $this->decryptValues($request->data);

		$service->validate($data['CustomerID'], 'Error: No customer id is present.')->notNull()->isInt();
		$service->validate($data['orderId'], 'Error: No order id is present.')->notNull()->isInt();


		$customer = CustLogin::get_customer($data['CustomerID']);
		$order = OrderOnsiteInterpreter::get_interpret_order($data['orderId'], "*");
		$transOrder = TranslationOrders::getTranslationOrder($data['orderId'], '*');


		$token = ConfFunc::generateCapabilityToken($customer['CustomerID']);

		return $response->json(array('data' => array('twilioToken' => $token)));
		// $service->token = $token;
		// $service->render('./resources/views/twilio/conference/hello.php');
	}

	/**
	 *
	 * +385 91 924 9906 MOJ BROJ
	 *
	 */
	public function incoming($request, $response, $service, $app){
		$token = ConfFunc::generateCapabilityToken('jenny');
		$service->token = $token;
		$service->render('./resources/views/twilio/conference/hello.php');
	}


}