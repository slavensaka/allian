<?php

namespace Allian\Http\Controllers;

use \Dotenv\Dotenv;
use Services_Twilio;
use Database\Connect;
use Services_Twilio_Twiml;
use Allian\Models\CustLogin;
use Services_Twilio_TinyHttp;
use Services_Twilio_Capability;
use Allian\Models\TranslationOrders;
use Allian\Models\ConferenceSchedule;
use Allian\Models\OrderOnsiteInterpreter;
use Allian\Helpers\TwilioConference\DatabaseAccess;
use Allian\Helpers\TwilioConference\ConferenceFunctions as ConfFunc;

class ConferenceController extends Controller {

	/**
     * @ApiDescription(section="Conference", description="Retrieve the twilioToken. Note: it's not encrypted with RNCencryptor. Simple json")
     * @ApiMethod(type="post")
     * @ApiRoute(name="/testgauss/conference")
     * @ApiBody(sample="{'data': {
    	'CustomerID': '800'
  		},
     'token': 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzUxMiJ9.eyJpYXQiOjE0NjUyODA1MDIsImp0aSI6IlVheUZlOUJTcEE5empHWUNneVpnNTJEVFYzRXZ4NFE5YXNKdTQ4MHdEY289IiwiaXNzIjoibG9jYWxob3N0IiwibmJmIjoxNDY1MjgwNTAyLCJleHAiOjE0NjY0OTAxMDIsImRhdGEiOnsiU3VjY2VzcyI6IlN1Y2Nlc3MifX0.qkGUG0WdaW_Q1aysAgfaEC5300Hk4X9VFEZRGsTOxE4X-P27EdCEfAnDPY0SaXD_VfsHiVYaGwwKxO-Bz0N8Yg'}")
     @ApiParams(name="token", type="string", nullable=false, description="Autentication token for users autentication.")
     @ApiParams(name="data", type="string", nullable=false, description="Encrypted customers email & password as json used for authentication.")
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
     * @ApiDescription(section="ConferenceOut", description="When connected with /conference twilioToken, generates the TwiML response. Not called directly. Twilio call this route to retrieve the Twiml code needed once the call is initilised.")
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
		$conference = ConferenceSchedule::get_conference($request->orderId, '*');
		$conf_queue = $conference['user_code'];

		$response = new Services_Twilio_Twiml;
		$response->say("Welcome to Allian interpreter conference service.");
		$response->redirect("http://alliantranslate.com/linguist/twilio-conf-enhanced/conference.php?Digits=1&vcode=" . trim($conf_queue));// TODO THE EASY WAY
		return $response;
		// $CallSid = $request->CallSid;
		// $customer = CustLogin::get_customer($request->CustomerID);
		// $order = OrderOnsiteInterpreter::get_interpret_order($request->orderId, '*');
		// $transOrder = TranslationOrders::getTranslationOrder($request->orderId, '*');
		// Is client verified, by getting the customer by id and then checking it's orderId in the conference_schedule
		// $verified = ConfFunc::verify_caller($conference['user_code']);
		// $limit=ConfFunc::chech_limit($code,$count); // NE vidim $code
		// ConfFunc::set_pre_log($conference['user_code'], $CallSid);
		// $service->verified = $verified;
		// $service->CustomerID = $request->CustomerID;
		// $service->orderId = $request->orderId;
		// Set the conf_queue on the user
		// $query = "UPDATE `order_onsite_interpreter` set conf_queue='$conf_queue' WHERE orderID='" . $request->orderId . "'";
		// $con = Connect::con();
		// $query_result = mysqli_query($con, $query);
		// $service->render('./resources/views/twilio/conference/conferenceOut.php'); // THE HARD WAY
	}

	/**
     * @ApiDescription(section="AddNewMember", description="Add new member when a schedule sesion is a conference_call.")
     * @ApiMethod(type="post")
     * @ApiRoute(name="/testgauss/addNewMember")
     * @ApiBody(sample="{'data': {
    	'CustomerID': '800',
    	 'phones': ['+123456788', '+5454534534']
  		},
     'token': 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzUxMiJ9.eyJpYXQiOjE0NjUyODA1MDIsImp0aSI6IlVheUZlOUJTcEE5empHWUNneVpnNTJEVFYzRXZ4NFE5YXNKdTQ4MHdEY289IiwiaXNzIjoibG9jYWxob3N0IiwibmJmIjoxNDY1MjgwNTAyLCJleHAiOjE0NjY0OTAxMDIsImRhdGEiOnsiU3VjY2VzcyI6IlN1Y2Nlc3MifX0.qkGUG0WdaW_Q1aysAgfaEC5300Hk4X9VFEZRGsTOxE4X-P27EdCEfAnDPY0SaXD_VfsHiVYaGwwKxO-Bz0N8Yg'}")
     @ApiParams(name="token", type="string", nullable=false, description="Autentication token for users autentication.")
     @ApiParams(name="data", type="string", nullable=false, description="Encrypted customers email & password as json used for authentication.")
     * @ApiReturnHeaders(sample="HTTP 200 OK")
     * @ApiReturn(type="string", sample="{
     *  'data': {
	    'status': 1,
	    'userMessage': 'Added New Member.'

	  	}
     * }")
     */
	public function addNewMember($request, $response, $service, $app){
		// Decrypt data
		$data = $this->decryptValues($request->data);
		// Validate CustomerId
		$service->validate($data['CustomerID'], 'Error: No customer id is present.')->notNull()->isInt();
		$service->validate($data['phones'], 'Error: No phones array is present.')->notNull();
		// TODO dodati column za restrikciju,orderID treba poslat u ovom requestu
		// $service->validate($data['orderId'], 'Error: No orderId is present.')->notNull();
		$customer_id = $data['CustomerID'];
		$phones = $data['phones'];
		$query = "SELECT orderID FROM `order_onsite_interpreter` WHERE assg_frm_date = CURDATE() AND customer_id = $customer_id ORDER BY autoID DESC LIMIT 1";
		$con = Connect::con();
		$query_result = mysqli_query($con, $query);
		$row = mysqli_fetch_array($query_result);
		$orderID = $row['orderID'];
		$queue = ConferenceSchedule::get_conference($orderID, 'conf_tag');
		$http = new Services_Twilio_TinyHttp('https://api.twilio.com', array('curlopts' => array(CURLOPT_SSL_VERIFYPEER => false)));
		$sid = getenv('LIVE_TWILIO_ALLIAN_SID');
		$token = getenv('LIVE_TWILIO_ALLIAN_TOKEN');
		$client = new Services_Twilio($sid, $token, '2010-04-01', $http);
		$url = "http://alliantranslate.com/testgauss/addNewMemberOut?vcode=$queue";
		foreach($phones as $phone){
			// TODO FOR PRODUCTION DONE
			$call = $client->account->calls->create(getenv('ADD_NEW_MEMBER'), $phone, $url, array());
			// $call = $client->account->calls->create("+15005550006", "+14108675309", $url, array());
		}
		$rArray['status'] = 1;
		$rArray['userMessage'] = 'Added new member.';
		$base64Encrypted = $this->encryptValues(json_encode($rArray));
     	return $response->json(array('data' => $base64Encrypted));
	}

	/**
     * @ApiDescription(section="AddNewMemberOut", description="Used for twilio to add new member when a schedule sesion is a conference_call.")
     * @ApiMethod(type="post")
     * @ApiRoute(name="/testgauss/addNewMemberOut")
     *	@ApiParams(name="vcode", type="string", nullable=false, description="Name of the conference.")
     * @ApiReturnHeaders(sample="HTTP 200 OK")
     */
	public function addNewMemberOut($request, $response, $service, $app){
		$service->vcode = $request->vcode;
		$service->render('./resources/views/twilio/conference/addNewMemberOut.php');
	}

}

