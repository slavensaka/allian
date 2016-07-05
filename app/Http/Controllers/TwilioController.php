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
	 *
	 * Block comment
	 *
	 */
	public function twilioConf($request, $response, $service, $app){
		$service->render('./resources/views/twilio/conference/twilioConf.php');
	}

	/**
     * NOVI BROJ TREBA ZA CLIENTA ZA APP KOJI NAZIVOM SE SPAJA NA conf_tag na kojem je već interperter
     * @ApiDescription(section="TwilioConference", description="Join the twilio conference call.")
     * @ApiMethod(type="get")
     * @ApiRoute(name="/testgauss/twilioConference")
     * @ApiBody(sample="{ 'data': ''}")
     * @ApiReturnHeaders(sample="HTTP 200 OK")
     * @ApiReturn(type="object", sample="{'nesto': '', 'nesto': '' }")
     */
	public function twilioConference($request, $response, $service, $app){
		if($request->token){
			// Validate token if not expired, or tampered with
			$this->validateToken($request->token);
			// Decrypt data
			// Validate CustomerId
			$data = $this->decryptValues($request->data);
			$service->validate($data['CustomerID'], 'Error: No customer id is present.')->notNull()->isInt();
			$service->validate($data['orderId'], 'Error: No order id is present.')->notNull()->isInt();
			$service->validate($data['phoneNumber'], 'Error: No phoneNumber id is present.');

			$validated = $this->validateTokenInDatabase($request->token, $data['CustomerID']);
			// If error validating token in database
			if(!$validated){
				$base64Encrypted = $this->encryptValues(json_encode($this->errorJson("Authentication problems present")));
	     		return $response->json(array('data' => $base64Encrypted));
			}

			$conference = ConferenceSchedule::get_conference("TODO REMOVE", $data['orderId'], '*');

			$sid = getenv('S_TWILIO_SID');
			$token = getenv('S_TWILIO_TOKEN');
			$version = '2010-04-01';
			$http = new Services_Twilio_TinyHttp('https://api.twilio.com', array('curlopts' => array(CURLOPT_SSL_VERIFYPEER => false)));
			$client = new Services_Twilio($sid, $token, $version, $http);

			$appSid = getenv('S_TEST_TWILIO_APP_SID');
			$capability = new Services_Twilio_Capability($sid, $token);
			$capability->allowClientOutgoing($appSid);

			if($data['phoneNumber']){
				$urlBuild = array(
			    	"CustomerID" => $data['CustomerID'],
			    	"orderId" => $data['orderId'],
			    	"phoneNumber" => $data['phoneNumber']
				);
			} else {
				$urlBuild = array(
			    	"CustomerID" => $data['CustomerID'],
			    	'phoneNumber' => "+385919249906", // MOJ BROJ VERIFIED TESTING
			    	"orderId" => $data['orderId']
				);
			}

			$fallbackUrl = array(
		    	"CustomerID" => $data['CustomerID'],
		    	"orderId" => $data['orderId'],
		    	"user_code" => $conference['user_code']
			);

			$server = $this->serverEnv();
			$urlFirst = 'https://af3d0846.ngrok.io/';
			if($server=="alliantranslate"){
				$urlFirst = 'https://alliantranslate.com/';
			}

			$url = $urlFirst . 'testgauss/incomingInbound' . '?' . http_build_query($urlBuild, '', '&');
			$fallbackUrl = $urlFirst . 'testgauss/fallbackUrl' . '?' . http_build_query($fallbackUrl, '', '&');

			if($data['phoneNumber']){
				$call  = $client->account->calls->create(
					'+385919249906', $urlBuild['phoneNumber'], $url, array('StatusCallback' => $fallbackUrl));
			} else {
				$call = $client->account->calls->create(
					'+385919249906', '+12014642721', $url, array('StatusCallback' => $fallbackUrl));
			}
			$base64Encrypted = $this->encryptValues($call);
	     	return $response->json(array('data' => $base64Encrypted));
		} else {
			$base64Encrypted = $this->encryptValues(json_encode($this->errorJson("No token provided in request")));
     		return $response->json(array('data' => $base64Encrypted));
		}
	}

	/**
	 Add na
	 https://www.twilio.com/user/account/voice/dev-tools/twiml-apps/APe000998f8bdd808a60759eb32f792c12
	 https://6b618a9f.ngrok.io/testgauss/incomingInbound

	 https://8c01a10c.ngrok.io/testgauss/incomingInbound?poruka=Poruka
	 */
	public function incomingInbound($request, $response, $service, $app){

		$service->validate($request->CustomerID, 'Error: No customer id is present.')->notNull()->isInt();
		$service->validate($request->orderId, 'Error: No order id is present.')->notNull()->isInt();
		$customer = CustLogin::get_customer($request->CustomerID);
		$order = OrderOnsiteInterpreter::get_interpret_order($request->orderId, '*');
		$transOrder = TranslationOrders::getTranslationOrder($request->orderId, '*');
		$conference = ConferenceSchedule::get_conference("TODO REMOVE", $request->orderId, '*');
		$verified = ConfFunc::verify_caller($conference['user_code']);
		$token = ConfFunc::generateCapabilityToken($request->CustomerID);
		// $limit=ConfFunc::chech_limit($code,$count); // NE vidim $code
		ConfFunc::set_pre_log($conference['user_code'], $request->CallSid);
		// $service->token = $token; // NE TREBA

		$service->verified = $verified;
		$service->CustomerID = $request->CustomerID; // NE treba
		$service->orderId = $request->orderId; // NE treba
		$service->poruka = $request->poruka;
		$service->render('./resources/views/twilio/conference/incomingInbound.php');
		// $service->render('./resources/views/twilio/conference/twilioConference.php');
	}

	public function callbackUrl($request, $response, $service, $app){
		$user_code =  $request->user_code;
		ConfFunc::set_pre_log($user_code, $request->CallSid);
		ConfFunc::set_post_log($_REQUEST['CallSid']);
		ConfFunc::remove_expired_shedule();
	}
	/**
	 *
	 * Block comment
	 *
	 */
	public function addNewMember($request, $response, $service, $app){
		$conf_tag = $request->conference;
	}

	/**
	 *
	 * +385 91 924 9906 MOJ BROJ
	 * Twilio tester Twiml poziv
	 *
	 */
	public function incoming($request, $response, $service, $app){
		$accountSid = getenv('S_TWILIO_SID');
		$authToken  = getenv('S_TWILIO_TOKEN');
		$appSid     = getenv('S_TEST_TWILIO_APP_SID');
		$capability = new Services_Twilio_Capability($accountSid, $authToken);
		$capability->allowClientOutgoing($appSid);
		$capability->allowClientIncoming('jenny');
		$token = ConfFunc::generateCapabilityToken('jenny');
		$service->token = $token;
		$service->render('./resources/views/twilio/conference/incoming.php');
	}

	/**
	 *
	 * Block comment
	 *
	 */
	public function twilioCall($request, $response, $service, $app){
		$http = new Services_Twilio_TinyHttp('https://api.twilio.com', array('curlopts' => array(CURLOPT_SSL_VERIFYPEER => false)));
		$version = '2010-04-01';
		$sid = getenv('S_TEST_TWILIO_SID');
		$token = getenv('S_TEST_TWILIO_TOKEN');
		$testPhone = getenv('S_TEST_TWILIO_NO_E_CONF_CALL');
		$client = new Services_Twilio($sid, $token, $version, $http);
		$twiml_url = 'https://alliantranslate.com/testgauss/twilioConference';
		$call = $client->account->calls->create("+15005550006", "+14108675309", $twiml_url, array());
		return $call;
	}

	public function sendSms(){

		$http = new Services_Twilio_TinyHttp('https://api.twilio.com', array('curlopts' => array(CURLOPT_SSL_VERIFYPEER => false)));
		$version = '2010-04-01';
		$accountSid = getenv('S_TWILIO_SID');
		$authToken  = getenv('S_TWILIO_TOKEN');
		$appSid     = getenv('S_TEST_TWILIO_APP_SID');
		$client = new Services_Twilio($accountSid, $authToken, $version, $http);
		$client->account->messages->create(array(
		    'To' => '+385919249906',
		    'From' => '+12014642721',
		    'Body' => "Hey Jenny! Good luck on the bar exam!",
		));

		// $service->render('./resources/views/twilio/conference/sendSms.php');
	}
}

