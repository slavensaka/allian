<?php

namespace Allian\Http\Controllers;

use Database\Connect;
use \Dotenv\Dotenv;
use Stripe\Stripe;
use Services_Twilio;
use Allian\Helpers\Mail;
use Services_Twilio_Twiml;
use Services_Twilio_Capability;
use Allian\Models\CustLogin;
use Allian\Models\OrderOnsiteInterpreter;
use Allian\Models\ConferenceSchedule;
use Allian\Models\TranslationOrders;
use Allian\Models\LangList;
use Allian\Models\LangRate;
use Services_Twilio_TinyHttp;
use Allian\Http\Controllers\StripeController;
use Allian\Http\Controllers\DeveloperController;
use Allian\Helpers\TwilioConference\DatabaseAccess;
use Allian\Helpers\Allian\ConnectNowFunctions;
use Allian\Helpers\TwilioConference\ConferenceFunctions as ConfFunc;

class ConnectNowController extends Controller {

	/**
     * @ApiDescription(section="ConnectNow", description="Retrieve the twilioToken. Note: it's not encrypted with RNCencryptor. Simple json. Generate the twilio token for connection to connectNow interpreting.")
     * @ApiMethod(type="post")
     * @ApiRoute(name="/testgauss/connectNow")
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
	public function connectNow($request, $response, $service, $app){
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
			$token = ConfFunc::generateCapabilityTokenConnectNow($data['CustomerID']);
			// Return that Token
	    	return $response->json(array('data' => array('status' => 1, 'userMessage' => 'Twilio token', 'twilioToken' => $token)));
	    } else {
     		$ret = $this->errorJson("No token provided in request");
     		return $response->json(array('data' => $ret));
     	}
	}

	/**
     * @ApiDescription(section="ConnectOut", description="When connected with twilioToken, generates the TwiML response.")
     * @ApiMethod(type="post")
     * @ApiRoute(name="/testgauss/connectOut")
     * @ApiBody(sample="{ 'CustomerID': '800', 'lang': 'English', 'translationTo': 'Arabic'}")
     * @ApiParams(name="CustomerID", type="string", nullable=false, description="Customers id")
     * @ApiParams(name="lang", type="string", nullable=false, description="language 1")
     * @ApiParams(name="translationTo", type="string", nullable=false, description="language 2")
     * @ApiReturnHeaders(sample="HTTP 200 OK")
     * @ApiReturn(type="string", sample="{'data': {
        'twiML': 'twiML code for enqueue for a session with linguist'} }")
     */
	public function connectOut($request, $response, $service, $app){
		$service->validate($request->CustomerID, 'Error: No customer id is present.')->notNull()->isInt();
		$service->validate($request->lang, 'Error: No lang is present.')->notNull();
		$service->validate($request->translationTo, 'Error: No translationTo is present.')->notNull();
		// Retrieve the request parameters
		$CustomerID = $request->CustomerID;
		$lang = $request->lang;
		$translationTo = $request->translationTo;
		//Twilio customer request params
		$sid = $request->CallSid;
		// Make a flag for langauge order
		$flag = 0;
		//Format the request From number to fit the + convention of twilio
		$from = $request->From;
		$from = str_replace('+',"", $from);
		// Get the customer information in database by CustomerID
		$customer = CustLogin::get_customer($CustomerID);
		// Add the customerID and Type to customertype by sid
		$data['type'] = $customer['Type'];
		$data['CustomerID'] = $customer['CustomerID'];
		ConnectNowFunctions::addCustomerIdType($sid, $data);
		// Retrieve the id language by it's name
		$l1 = LangList::langIdByName($lang);
		$l2 = LangList::langIdByName($translationTo);
		// Select chinese and mandarin languages ids in database
		$chinese = LangList::selectChinese();
		$mandarin = LangList::selectMandarin();
		// If the first selected language is chinese change to mandarin as correct
		$chid = $chinese["LangId"];
		if($l1 == $chid){
			$l1 = $mandarin["LangId"];
		}
		// If the second select language is chinese change to mandarin as selected
		if($l2 == $chid){
			$l2 = $mandarin["LangId"];
		}
		// Select the amount rate for the L1 & L2 pair language from database
		$con = Connect::con();
		$result = mysqli_query($con,"SELECT * FROM LangRate WHERE L1= '$l1' and L2='$l2' ");
		$numrows= mysqli_num_rows($result);
		//If result rate for the pair language if found, set the flag to 1
		if($numrows != 0){
			$flag = 1;
		}
		// Select the amount rate for the L2 & L1 pair language from database
		$result1 = mysqli_query($con,"SELECT * FROM LangRate WHERE L1= '$l2' and L2='$l1' ");
		$numrows1= mysqli_num_rows ($result1);
		//If result rate for the pair language if found, set the flag to 2
		if($numrows1 != 0){
			$flag = 2;
		}
		// If the flag=1 then the L1 & L2 rate is found. Retrieve the PairID from the database for the pair into $queue
		if($flag == 1){
			$row = mysqli_fetch_array($result);
			$queue = $row['PairID'];
		// Else If the flag=2 then the L2 & L1 rate is found. Retrieve the PairID from the database for the pair int $queue.
		}else if($flag == 2){
			$row = mysqli_fetch_array($result1);
			$queue = $row['PairID'];
		} else if($flag == 0){
			$response = new Services_Twilio_Twiml;
			$response->say('The interpreting service is not available between the selected two language pairs at this time.');
			$response->hangup();
			return $response;
		}
		// If customers type is 2(invoice), then get the TwiML, pass customer, queue, from info
		if($customer['Type'] == 2){ // Invoice
			$service->customer = $customer;
			$service->queue = $queue;
			$service->from = $from;
			$service->render('./resources/views/twilio/connect/connectOut.php');
		// If customers type is 1(stripe), then preauth(Uncapture) the customer for 30$
		} else if($customer['Type'] == 1){ // Stripe
			// Get the customers stripe token
			$token = $customer['token'];
			// Preauth the customer with stripe for 30$ (uncapture)
			$id = StripeController::preAuthCustomer($token);
			// Logging on localhost
			$server = trim($_SERVER['HTTP_HOST']);
			$server=trim($server);
			if($server == "localhost"){
				// Gather the information for the call and request
				$addtofile['CallSid'] = $sid;
				$addtofile['CustomerID'] = $CustomerID;
				$addtofile['From'] = $from;
				$addtofile['lang'] = $lang;
				$addtofile['translationTo'] = $translationTo;
				DeveloperController::addtofile($sid, $addtofile);
				$addtofilePairQueue['queue'] = $queue;
				DeveloperController::addtofilePairQueue($sid, $addtofilePairQueue);
				//Log the token that was preauth and the result of the operation of preauthing for debugging
				$addtofilePrepayment['token'] = $token;
				$addtofilePrepayment['id'] = $id;
				DeveloperController::addtofilePrepayment($sid, $addtofilePrepayment);
			} elseif($server == "alliantranslate.com"){
				// Nothing
			} else{
				// Gather the information for the call and request
				$addtofile['CallSid'] = $sid;
				$addtofile['CustomerID'] = $CustomerID;
				$addtofile['From'] = $from;
				$addtofile['lang'] = $lang;
				$addtofile['translationTo'] = $translationTo;
				DeveloperController::addtofile($sid, $addtofile);
				$addtofilePairQueue['queue'] = $queue;
				DeveloperController::addtofilePairQueue($sid, $addtofilePairQueue);
				//Log the token that was preauth and the result of the operation of preauthing for debugging
				$addtofilePrepayment['token'] = $token;
				$addtofilePrepayment['id'] = $id;
				DeveloperController::addtofilePrepayment($sid, $addtofilePrepayment);
			}
			// IF the stripe result if found, then get the twiml, pass customer, queue, from info
			if(isset($id)){
				$service->customer = $customer;
				$service->from = $from;
				$service->queue = $queue;
				$service->render('./resources/views/twilio/connect/connectOut.php');
			} else {
				// Else return twiML that the card could not be preauthorized
				$response = new Services_Twilio_Twiml;
				$response->say('Your Credit Card could not be authorized. Please change your credit card information. Thank you for calling our phone interpreting line. Good bye.');
				$response->hangup();
				return $response;
			}
		}
	}

	/**
	 *
	 * Block comment
	 *
	 */
	public function connectNowQueueCallback($request, $response, $service, $app){
		// Retrieve hardcoded parameters from request callback
		$langpair = $request->id;
		$CustomerID = $request->CustomerID;
		$customerType = $request->customerType;
		$from = $request->from;
		// If the first char is not +, add it
		if($from[0] != '+'){
			$number = '+' . trim($from);
		}else{
			$number = trim($from);
		}
		// Retrieve twilio parameters
		$queueresult = $request->QueueResult;
		  $queuetime = $request->QueueTime;
		// IF result is the user hangup in 55 sec
		if($queuetime > 55 && $queueresult == "hangup"){
			$queueresult = "Agent Unavailable";
		}else if($queueresult == "hangup"){
			$queueresult = "Customer Hangup";
		}
		// Select the languages for the pairID
		$con = Connect::con();
		$result = mysqli_query($con,"SELECT L1,L2 FROM LangRate WHERE PairId = '$langpair'");
		$time = date("m/d/y G.i:s", time());
		$timestamp = time();
		// Retrieve the languages names
		if($row = mysqli_fetch_array($result)){
		  	$lang1 = mysqli_fetch_array(mysqli_query($con,"SELECT LangName FROM LangList WHERE LangId=" . $row['L1']));
			$lang2 = mysqli_fetch_array(mysqli_query($con,"SELECT LangName FROM LangList WHERE LangId=" . $row['L2']));
			$pair = trim($lang1['LangName'])."-".trim($lang2['LangName']);
			$pair = trim($pair);
		}
		// If not successfull result, store in the database CallIdeintify
		if($queueresult != 'bridged'){
			$sid = $request->CallSid;
		    $type = $customerType;
		  	$con = Connect::con();
		  	$query = "SELECT Email FROM CustLogin WHERE CustomerID = '$CustomerID'";
		  	$result = mysqli_fetch_assoc(mysqli_query($con,$query));
		  	$email = $result['Email'];
		  	$mailcontent="A call from the user with mail id :". $email . " (from" . $number . ") failed in the queue of " . $pair . " with reason " . $queueresult . " on " . $time . "\n\n-admin";
		    $param = $number . "," . $pair . "," . $queueresult . "," . $time . "," . $email;
		    // Send email to staff, regular failed
		    $server = $this->serverEnv();
			if($server=="localhost"){
				$sendToEmail = "slavensakacic@gmail.com";
				Mail::sendStaffMail($sendToEmail, "staffRegularFailed", $param);
			} else if($server=="alliantranslate"){
				//TODO FOR PRODUCTION DONE
				$sendToEmail = getenv('ALEN_EMAIL');
				//$sendToEmail = "orders@alliancebizsolutions.com";
				Mail::sendStaffMailProduction($sendToEmail, "staffRegularFailed", $param);
			}
			mysqli_query($con,"INSERT INTO CallIdentify(Type, starttime, CustomerId, FromNumber, state, duration, PairId) values ('$type', '$timestamp', '$CustomerID', '$number', '$queueresult', '0', '$langpair')");
			ConnectNowFunctions::removeCustomerIdType($sid);
		}
		$response = new Services_Twilio_Twiml;
		$response->hangup();
		return $response;
	}

	/**
     * @ApiDescription(section="AddNewMemberConnectNow", description="Add new member when a ina connect now call.")
     * @ApiMethod(type="post")
     * @ApiRoute(name="/testgauss/addNewMemberConnectNow")
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
	public function addNewMemberConnectNow($request, $response, $service, $app){
		// Todo dodati column za restrikciju
		// Decrypt data
		$data = $this->decryptValues($request->data);
		// Validate CustomerId
		$service->validate($data['CustomerID'], 'Error: No customer id is present.')->notNull()->isInt();
		$service->validate($data['phones'], 'Error: No phones array is present.')->notNull();

		$customer_id = $data['CustomerID'];
		$phones = $data['phones'];
		$query = "SELECT orderID FROM `order_onsite_interpreter` WHERE assg_frm_date = CURDATE() AND customer_id = $customer_id ORDER BY autoID DESC LIMIT 1";
		$con = Connect::con();
		$query_result = mysqli_query($con, $query);
		$row = mysqli_fetch_array($query_result);
		$conf_queue = $row['orderID'];

		$queue = ConferenceSchedule::get_conference($conf_queue, 'user_code');

		$http = new Services_Twilio_TinyHttp('https://api.twilio.com', array('curlopts' => array(CURLOPT_SSL_VERIFYPEER => false)));
		$version = '2010-04-01';
		$sid = getenv('S_TEST_TWILIO_SID');
		$token = getenv('S_TEST_TWILIO_TOKEN');
		$client = new Services_Twilio($sid, $token, $version, $http);
		$url = "localhost/testgauss/addNewMemberOut?vcode=$queue";
		foreach($phones as $phone){
			// TODO FOR PRODUCTION DONE
			$call = $client->account->calls->create(getenv('TWILIO_CONF_OB_NUMBER'), $phone, $url, array());
			// $call = $client->account->calls->create("+15005550006", "+14108675309", $url, array());
		}
		$rArray['status'] = 1;
		$rArray['userMessage'] = 'Added new Member';
		$base64Encrypted = $this->encryptValues(json_encode($rArray));
     	return $response->json(array('data' => $base64Encrypted));
	}

	/**
	 *
	 * Block comment
	 *
	 */
	public function waitForInterpreter($request, $response, $service, $app){
		$pairid = $request->pairid;
		$response = new Services_Twilio_Twiml;
		$response->say("Please wait while we attempt to reach an interpreter for your call.");
		$response->say("Please continue to wait while we find the first available interpreter.");
		$response->redirect("https://alliantranslate.com/linguist/phoneapp/callout.php?pairid=$pairid");
		return $response;
	}

}