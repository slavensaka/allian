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
     * NOVI BROJ TREBA ZA CLIENTA ZA APP KOJI NAZIVOM SE SPAJA NA conf_tag na kojem je već interperter
     * @ApiDescription(section="TwilioConference", description="Join the twilio conference call.")
     * @ApiMethod(type="get")
     * @ApiRoute(name="/testgauss/twilioConference")
     * @ApiBody(sample="{ 'data': ''}")
     * @ApiReturnHeaders(sample="HTTP 200 OK")
     * @ApiReturn(type="object", sample="{'nesto': '', 'nesto': '' }")
     */
	public function twilioConference($request, $response, $service, $app){
		$sid = getenv('S_TWILIO_SID');
		$token = getenv('S_TWILIO_TOKEN');
		$version = '2010-04-01';
		$http = new Services_Twilio_TinyHttp('https://api.twilio.com', array('curlopts' => array(CURLOPT_SSL_VERIFYPEER => false)));
		$client = new Services_Twilio($sid, $token, $version, $http);

		$appSid = getenv('S_TEST_TWILIO_APP_SID');
		$capability = new Services_Twilio_Capability($sid, $token);
		$capability->allowClientOutgoing($appSid);

		$query_string = array(
	    	'param1' => "This is parameter 1",
	    	'param2' => "This is parameter 2"
		);

		$url ='https://8c01a10c.ngrok.io/testgauss/incomingInbound' . '?' . http_build_query($query_string, '', '&');
		$call  = $client->account->calls->create(
		"+385919249906", '+12014642721', $url, array());

	}

	/**
	 Add na
	 https://www.twilio.com/user/account/voice/dev-tools/twiml-apps/APe000998f8bdd808a60759eb32f792c12
	 https://6b618a9f.ngrok.io/testgauss/incomingInbound

	 https://8c01a10c.ngrok.io/testgauss/incomingInbound?poruka=Poruka
	 */
	public function incomingInbound($request, $response, $service, $app){
		$service->poruka = $request->poruka;
		$service->render('./resources/views/twilio/conference/incomingInbound.php');
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


}