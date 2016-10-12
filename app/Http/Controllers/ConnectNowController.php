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
use Allian\Helpers\Allian\ScheduleFunctions;
use Services_Twilio_TinyHttp;
use Allian\Http\Controllers\StripeController;
use Allian\Http\Controllers\DeveloperController;
use Allian\Helpers\TwilioConference\DatabaseAccess;
use Allian\Helpers\TwilioConnectNow\ConnectNowFunctions;

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
			$CustomerID = $data['CustomerID'];
			$connectNowDate = gmdate("Y-m-d H:i:s", time());
			$customer = CustLogin::get_customer($data['CustomerID']);
			// Generate a twilio capability token
			$token = ConnectNowFunctions::generateCapabilityTokenConnectNow($data['CustomerID']);

			$con = Connect::con();
			$result = mysqli_query($con,"SELECT * FROM `connect_now_log` WHERE CustomerID = $CustomerID");
			$numrows = mysqli_num_rows($result);
			if($numrows != 0){// If record found
				mysqli_query($con, "UPDATE `connect_now_log` SET CustomerID = $CustomerID, twilioToken = $token, connectNowDate = $connectNowDate WHERE CustomerID = $CustomerID");
			} else {
				mysqli_query($con, "INSERT INTO `connect_now_log`(`CustomerID`, `twilioToken`, `connectNowDate`) VALUES ('$CustomerID','$token','$connectNowDate')");
			}
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
		$numrows = mysqli_num_rows($result);
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
			$real_queue = $row['PairID'] . $row['L1'] . $row['L2'];
		// Else If the flag=2 then the L2 & L1 rate is found. Retrieve the PairID from the database for the pair int $queue.
		}else if($flag == 2){
			$row = mysqli_fetch_array($result1);
			$queue = $row['PairID'];
			$real_queue = $row['PairID'] . $row['L1'] . $row['L2']; // TODO SHOULD IT BE ROW['L2'] . ROW['L1'] ??
		} else if($flag == 0){
			$response = new Services_Twilio_Twiml;
			$response->say('The interpreting service is not available between the selected two language pairs at this time.');
			$response->hangup();
			return $response;
		}
		$con = Connect::con();
		mysqli_query($con, "UPDATE `connect_now_log` SET lang = $l1, translationTo = $l2, queue = $queue, real_queue = $real_queue WHERE CustomerID = $CustomerID");

		// If customers type is 2(invoice), then get the TwiML, pass customer, queue, from info
		if($customer['Type'] == 2){ // Invoice
			$service->customer = $customer;
			$service->queue = $queue; // 73
			$service->real_queue = $real_queue; //738268
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
				$service->real_queue = $real_queue;
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
	public function waitForInterpreter($request, $response, $service, $app){
		$PairID = $request->pairid;
		$real_queue = $request->real_queue;
		$php_path= getenv('PHP_PATH');
		$command = $php_path . " app/Helpers/TwilioConnectNow/CallRandom.php $PairID $real_queue";
		ConnectNowFunctions::spawn($command, "app/Helpers/TwilioConnectNow/NotifyLog.txt", "pid");
		$response = new Services_Twilio_Twiml;
		// $response->say("Please wait while we attempt to reach an interpreter for your call.");
		// $response->say("Please continue to wait while we find the first available interpreter.");
		$response->say("Welcome back to ALLIAN phone interpreting line.");
		$response->say("Please wait while we connect you to the first available interpreter.");
		$response->play('app/Helpers/TwilioConnectNow/twilioaudio.mp3', array("loop" => 5));
		$response->say('Sorry, All of our agents are busy right now. Our customer service will be in touch with you shortly. Thank you for calling our phone interpreter services. Good bye.');
		$response->hangup();
		return $response;
	}

	/**
	 *
	 * Block comment
	 *
	 */
	public function callRandomHandle($request, $response, $service, $app){
		$PairID = $request->PairID;
		$real_queue = $request->real_queue;
		$IPID = $request->IPID;

		$con = Connect::con();
		$result1 = mysqli_query($con,"SELECT L1,L2,PairID FROM LangRate WHERE PairID='$PairID'");

		$row1 = mysqli_fetch_array($result1);
		$lang1 = mysqli_fetch_array(mysqli_query($con,"SELECT LangName FROM LangList WHERE LangId=".$row1['L1']));
		$lang2 = mysqli_fetch_array(mysqli_query($con,"SELECT LangName FROM LangList WHERE LangId=".$row1['L2']));

		$Pairname = trim($lang1['LangName'])."  to  ".trim($lang2['LangName']);

		$service->PairID = $PairID;
		$service->real_queue = $real_queue;
		$service->Pairname = $Pairname;
		$service->IPID = $IPID;
		$service->render('./resources/views/twilio/connect/callRandomHandle.php');
	}

	/**
	 *
	 * Block comment
	 *
	 */
	public function interpreter($request, $response, $service, $app){
		// Dolazi iz view-a callRandomHandle.php kada je cron pozvao interpretera
		if($request->Direction == "outbound-api"){
		 	$from = $request->To;
		 // Kada intepreter nazove direktno
		}else{
		    $from = $request->From;
		}
		if($from == 'client:Anonymous'){
			$from = $request->Phone;
		}
		// Ako se poziva od negdje drugdje, da nema twilio varijablu, nevjerujem da ce ikad
		if($from == null){
			$response = new Services_Twilio_Twiml;
			$response->say('Dear Interpreter, we are sorry the phone number that you are calling from does not match the phone number that is registered in our system. To be able to accept calls you must call from the number that you have registered with. Please call again from the phone number that you registered in the system or update your number in the linguist portal.');
 			$response->hangup();
 			return $response;
		}
		$IPID = false;
		$con = Connect::con();
		// Ako je došao iz cronjoba, sa svojim ipom
		//interpreter?PairID=73&real_queue=738268&Phone=385919249906&IPID=28749
		$IPID = $request->IPID;
		// if(ConnectNowFunctions::isTwilioClient($from)){ // NE trebam
		if(isset($IPID)){
			$IPID = $request->IPID;
 			// $IPID = str_replace("client:", "", $from); // FUTURE
 		// Ako je direktno nazvao, onda pronadi njegovi IPID preko Phone u twilio from variajabli
 		}else{
	 		$from = substr($from, 1);
	 		$result = mysqli_query($con, "SELECT IPID FROM Login WHERE Phone= '$from'");
	 		if(mysqli_num_rows($result)>0){
	 			$row = mysqli_fetch_assoc($result);
	 			$IPID = $row['IPID'];
 			}
 		}

 		if($IPID){
 			$query = mysqli_query($con,"SELECT * FROM LangPair WHERE IPID=" . $IPID);
 			if(mysqli_num_rows($query)<1){
 				$response = new Services_Twilio_Twiml;
				$response->say('Sorry, the language pair that you are trying to select is currently not offered by Alliance Business Solutions phone interpreter services.');
 				$response->hangup();
 				return $response;
 			}else if(mysqli_num_rows($query) == 1){
 				$row1 = mysqli_fetch_array($query);
	    		$pair1 = $row1['PairID'];
	    		$array = $pair1;
	    		$real_queue = $row1['PairID'] . $row1['L1'] . $row1['L2'];
	    		if(isset($request->PairID)){
					$PairID = $request->PairID;
					$service->PairID = $PairID;
					$service->real_queue = $real_queue;
					$service->IPID = $IPID;
					$service->pair1 = $pair1;
					$service->array = $array;
		    		$service->render('./resources/views/twilio/connect/interpreter.php');
				} else {
					$service->real_queue = $real_queue;
					$service->IPID = $IPID;
					$service->pair1 = $pair1;
					$service->array = $array;
		    		$service->render('./resources/views/twilio/connect/nextCustomer.php');
				}
				// if(isset($request->real_queue)){
				// 	$real_queue = $request->real_queue;
				// }

 			}else if(mysqli_num_rows($query)>1){
 				$row1 = mysqli_fetch_array($query);
		    	$pair1= $row1['PairID'];
		    	$real_queue = $row1['PairID'] . $row1['L1'] . $row1['L2'];
		    	while($row1 = mysqli_fetch_array($query)){
	    			$array.=$row1['PairID'].",";
	    		}
	        	if(isset($request->PairID)){
	        		$PairID = $request->PairID;

	        		$service->PairID = $PairID;
					$service->real_queue = $real_queue;
					$service->IPID = $IPID;
					$service->array = $array;
					$service->pair1 = $pair1;
		        	$service->render('./resources/views/twilio/connect/interpreter.php');
	        	} else {
	        		$service->real_queue = $real_queue;
					$service->IPID = $IPID;
					$service->pair1 = $pair1;
					$service->array = $array;
		    		$service->render('./resources/views/twilio/connect/nextCustomer.php');
	        	}
	   			//if(isset($request->real_queue)){
				// 	$real_queue = $request->real_queue;
				// }

	       	}

	    }else {
	       	$response = new Services_Twilio_Twiml;
			$response->say('Dear Interpreter, we are sorry the phone number that you are calling from does not match the phone number that is registered in our system. To be able to accept calls you must call from the number that you have registered with. Please call again from the phone number that you registered in the system or update your number in the linguist portal.');
 			$response->hangup();
 			return $response;
		}
	}

	/**
	 *
	 * Block comment
	 *
	 */
	public function redirectToConference($request, $response, $service, $app){
		if(!isset($request->PairID)){
			// nextCustomer.php ako je queue empty, povecaj times i odi na handleNextCustomer
			if ($request->DequeueResult == "queue-empty" || $request->DequeueResult == "queue-not-found"){
				if(isset($request->times) && !($request->times >= 5)){
					$IPID = $request->IPID;
					$pairarray = $request->pairarray;
					$times = $request->times;
					$real_queue = $request->real_queue;
					$Previous = $request->Previous;
					$parray = explode(",", $pairarray);
					array_pop($parray);
					$currentindex = array_search($Previous, $parray);
					$length = count($parray) - 1;
					if($currentindex == $length){
						$next = $parray[0];
					} else {
						$next = $parray[$currentindex+1];
					}
					$times++;

					$service->IPID = $IPID;
		        	$service->pairarray = $pairarray;
		        	$service->real_queue = $real_queue;
					$service->times = $times;
					$service->next = $next;
					return $service->render('./resources/views/twilio/connect/handleNextCustomer.php');
				}

				if(isset($request->times) && $request->times >= 5){
					$response = new Services_Twilio_Twiml;
					$response->say('Sorry, No calls in queue right now. Please try again after some time.');
					$response->hangup();
					return $response;
				}
			}
		}

		// Normal redirect to conference from API
		if(isset($request->PairID)){
			$PairID = $request->PairID;
		}
		if(isset($request->real_queue)){
			$real_queue = $request->real_queue;
		}
		$IPID = $request->IPID;
		$array = $request->array;
		$pair1 = $request->pair1;
		$con = Connect::con();
		if(!isset($PairID)){
			$result = mysqli_query($con,"SELECT * FROM LangRate WHERE PairID= '$pair1'");
			$row = mysqli_fetch_array($result);
			$real_queue = $row['PairID'] . $row['L1'] . $row['L2'];
		} else {
			$result = mysqli_query($con,"SELECT * FROM LangRate WHERE PairID= '$PairID'");
			$row = mysqli_fetch_array($result);
			$real_queue = $row['PairID'] . $row['L1'] . $row['L2'];
		}
		$query_string = array( 'real_queue' => $real_queue, 'IPID' => $IPID , 'array' => $array, 'pair1' => $pair1);
		$urlCallback =  'http://alliantranslate.com/testgauss/connectNowConference' . '?' . http_build_query($query_string, '', '&');

		$sid = getenv('LIVE_TWILIO_ALLIAN_SID');
		$token = getenv('LIVE_TWILIO_ALLIAN_TOKEN');
		$client = new Services_Twilio($sid, $token);
		$call = $client->account->calls->get($request->CallSid);
		$call->update(array("Url" => $urlCallback));
	}

	/**
	 *
	 * Block comment
	 *
	 */
	public function connectNowConference($request, $response, $service, $app){
		// $PairID = $request->PairID;// Look at the connectNowQueueCallback
		$real_queue = $request->real_queue;
		$IPID = $request->IPID;
		$array = $request->array;
		$pair1 = $request->pair1;

		$service->real_queue = $real_queue;
		$service->IPID = $IPID;
		$service->array = $array;
		$service->pair1 = $pair1;
		$service->render('./resources/views/twilio/connect/connectNowConference.php');
	}

	public function handlePaymentTest($request, $response, $service, $app){
		$all = $request->paramsGet()->all();
		foreach( $all as $key => $value ) $message .= "$key: $value\n";
		Mail::simpleLocalMail("HandlePaymentTest All form request", $message); // For testing

		$IPID = $request->IPID;
		$pairarray = $request->pairarray;
		$times = $request->times;
		$Previous = $request->Previous;
		$CustomerID = $request->CustomerID;
		$real_queue = $request->real_queue;

		if(isset($CustomerID)){
			$con = Connect::con();
			$result = mysqli_query($con,"DELETE FROM `connect_now_log` WHERE CustomerID = $CustomerID");
		}

		$log_file = "../linguist/phoneapp/log/main_log.txt";
		$dt = gmdate('Y-m-d H:i:s');
		$script = "@handlepayment.php";
		$sid = getenv('LIVE_TWILIO_ALLIAN_SID');
		$token = getenv('LIVE_TWILIO_ALLIAN_TOKEN');
		$client = new Services_Twilio($sid, $token);
		if(isset($request->DequeueResult) && $request->DequeueResult=="bridged"){
			$ipid=$request->IPID;
			$QueueSid=$request->QueueSid;
			$DequeueResult=$request->DequeueResult; // From Dial
			$DequeuedCallSid=$request->DequeuedCallSid; // From Dial
			$DequeuedCallDuration=$request->DequeuedCallDuration; // From Dial
			$queue = $client->account->queues->get($QueueSid);
			$langpair= $queue->friendly_name;
			$call = $client->account->calls->get($DequeuedCallSid);
			file_put_contents($log_file, $dt.$script.", Call Made: ".$call."\n",FILE_APPEND);
			$from = $call->from;
			$callmin=ceil($DequeuedCallDuration/60);
			$type=0;
			if(file_exists("customertype/".$DequeuedCallSid.".txt")){
		  		$data=getfromfile("../linguist/phoneapp/customertype/".$DequeuedCallSid); // MORAT CU STAVIT
		  		$type=$data['type'];
		  	}
		  	if($type==0){
				$query="SELECT * FROM CustLogin WHERE Phone = '$from'";
				file_put_contents($log_file, $dt.$script.", Type 0 select query1: ".$query."\n",FILE_APPEND);
				$select=(mysqli_query($con,$query));
				$result=mysqli_fetch_array($select);
				$customertoken=$result["token"];
				$query="SELECT CustomerId FROM CustLogin WHERE token = '$customertoken'";
				file_put_contents($log_file, $dt.$script.", Type 0 select query2: ".$query."\n",FILE_APPEND);
				$result=mysqli_fetch_row(mysqli_query($con,$query));
				$customerid=$result[0];
			}else if($type==1){
				$customerid=$data['CustomerID'];
				$query="SELECT * FROM CustLogin WHERE CustomerID = '$customerid'";
				file_put_contents($log_file, $dt.$script.", Type 1 select query: ".$query."\n",FILE_APPEND);
				$select=(mysqli_query($con,$query));
				$result=mysqli_fetch_array($select);
				$customertoken=$result["token"];
			}else{
				$customerid=$data['CustomerID'];
			}
			file_put_contents($log_file, $dt.$script.", customertoken: ".$customertoken.", CustomerID: ".$customerid."\n",FILE_APPEND);
			$query="SELECT Rate FROM LangRate WHERE PairID = '$langpair'";
			$result=mysqli_fetch_row(mysqli_query($con,$query));
			$rate=$result[0];
			$timestamp = time() - $DequeuedCallDuration;
			$actualrate = $callmin * $rate;
			if($actualrate >= 2000){
				$charged = $actualrate;
			}else{
				$charged = 2000;
			}
			$send_notification_alert = false;
        	$call_id="";
        	if($type!=2){ // is stripe
				$stripe=shell_exec ('curl https://api.stripe.com/v1/charges \
		   		-u '.getenv('STRIPE_KEY').' \
		   		-d amount='.$charged.' \
		   		-d currency=usd \
		   		-d capture=true \
		   		-d customer='.$customertoken.' \
		  		-d "description=charging for service"');
				$paymentresult=json_decode($stripe);
				file_put_contents("../linguist/phoneapp/paymentlog.txt", $stripe."\n",FILE_APPEND);
				file_put_contents($log_file, $dt.$script.", Final payment result: ".$stripe."\n",FILE_APPEND);
				if(isset($paymentresult->id)){
					if(mysqli_query($con,"INSERT INTO CallIdentify (type,starttime,CustomerID,FromNumber,state,duration,IPID,PairId,Rate,Charged) values ('$type','$timestamp','$customerid','$from','Success','$DequeuedCallDuration','$ipid','$langpair','$rate','$charged')")){
						$send_notification_alert = true;
		                $call_id = mysqli_insert_id($con);
		            }
		            mysqli_query($con,"UPDATE CustLogin  SET totalcharged=totalcharged+'$charged' WHERE CustomerId='$customerid'");
				}else{
					$charged=$charged."failure";
					mysqli_query($con,"INSERT INTO CallIdentify (type,starttime,CustomerID,FromNumber,state,duration,IPID,PairId,Rate,Charged) values ('$type','$timestamp','$customerid','$from','Success','$DequeuedCallDuration','$ipid','$langpair','$rate','$charged')");
					mysqli_query($con,"UPDATE CustLogin  SET totalcharged=totalcharged+'$charged' WHERE CustomerId='$customerid'");
				}
			}else{
				if(mysqli_query($con,"INSERT INTO CallIdentify (type,starttime,CustomerID,FromNumber,state,duration,IPID,PairId,Rate,Billed) values ('$type','$timestamp','$customerid','$from','Success','$DequeuedCallDuration','$ipid','$langpair','$rate','$charged')")){
           	 		$send_notification_alert = true;
            		$call_id = mysqli_insert_id($con);
        		}
        	}
			mysqli_query($con,"UPDATE CustLogin SET totalbilled=totalbilled+'$charged' WHERE CustomerID='$customerid'");
			if($send_notification_alert){
				// Send Reciept to client
			    $customerID = $data['CustomerID'];
			    $con = Connect::con();
			    $Customer=CustLogin::get_customer($customerID);
			    $CustomerEmail = $Customer["Email"];
			    $CustomerName=$Customer["FName"]." ".$Customer["LName"];
			    $subject="Receipt For Phone Interpreter Services (Call ID#$call_id) ";
			    $body= $this->order_its_details($call_id,"Email");
			    Mail::send_notification_handle_payment($subject,$body,$CustomerEmail);
			    $body = "<b>Dear $CustomerName</b>,<br><br><p>Thank you for using the Alliance Business Solutions phone interpreter line.</p><p>In order to help us improve our services please complete a brief survey about your experience with our phone interpreter system.</p><br><b>SURVEY LINK:</b><br><a href='http://allianinterpreter.com/en/telephonic-survey'>http://allianinterpreter.com/en/telephonic-survey</a><br><br><p>We thank you for choosing Alliance Business Solutions.</p><br>Kind Regards,<br>"."<p><span style='font-family: Arial; font-size: 12px; font-style: normal;'><strong>Alliance Business Solutions LLC<br /></strong><em>--Quality Assurance Team</em><strong><br />Email:</strong> cs@".getenv('TRANSLATION_DOMAIN')."<br /><strong>Toll Free:</strong> 1.877.512.1195<br /><strong>International:</strong> 1.615.866.5542<br /><strong>Fax:</strong> 1.615.472.7924<br /></span><a href='".getenv('HOME_SECURE')."' target='_blank'>Translate</a><span style='font-family: Arial; font-size: 12px; font-style: normal;'>. | </span><a href=".get('INTERPRETING_HOME_SECURE')."/' target='_blank'>Interpret</a><span style='font-family: Arial; font-size: 12px; font-style: normal;'>. | </span><a href='".getenv('TRANSCRIPTION_HOME_SECURE')."' target='_blank'>Transcribe</a><span style='font-family: Arial; font-size: 12px; font-style: normal;'>.<br /></span><img style='font-family: Arial; font-size: 12px; font-style: normal;' src='".getenv('HOME_SECURE')."logos/alliancebizsolutions_logo.png' alt='' width='150' height='58' /></p>";
			    $subject = "Tell us about your Phone Interpreter Experience";
			    $from = "Alliance Business Solutions LLC Client Services <feedback@alliancebizsolutions.com>";
			    $reply_to = "feedback@alliancebizsolutions.com";
			    Mail::send_notification_handle_payment($subject,$body,$CustomerEmail,$from,$reply_to);
			}
		} else if($request->DequeueResult == "queue-empty" || $request->DequeueResult == "queue-not_found"){
			// if($request->times >= 30){
			// 	$response = new Services_Twilio_Twiml;
			// 	$response->say('Sorry, No calls in queue right now. Please try again after some time.');
			// 	$response->hangup();
			// 	return $response;
			// }
			// $IPID = $request->IPID;
			// $langpair = $request->Previous;
			// $pairarray = $request->pairarray;
			// $parray = explode(",", $request->pairarray);
			// $length = count($parray) - 1;
			// if($currentindex == $length){
			// 	$next = $parray[0];
			// } else {
			// 	$next = $parray[$currentindex+1];
			// }
			// $times = $request->times;
			// $times++;

			// $service->IPID = $IPID;
			// $service->pairarray = $pairarray;
			// $service->times = $times;
			// $service->next = $next;
   			// $service->render('./resources/views/twilio/connect/nextCustomer.php');
		}

		if($request->times >= 30){
			$response = new Services_Twilio_Twiml;
			$response->say('Sorry, No calls in queue right now. Please try again after some time.');
			$response->hangup();
			return $response;
		}

		$parray = explode(",", $pairarray);
		array_pop($parray);
		$currentindex = array_search($Previous, $parray);
		$length = count($parray) - 1;
		if($currentindex == $length){
			$next = $parray[0];
		} else {
			$next = $parray[$currentindex+1];
		}
		$times++;

		$service->real_queue = $real_queue;
		$service->IPID = $IPID;
		$service->pairarray = $pairarray;
		$service->times = $times;
		$service->next = $next;
    	$service->render('./resources/views/twilio/connect/handleNextCustomer.php');
	}

	/**
	 *
	 * Enqueue action in connectOut.php
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
		$time = date("d/m/y G.i:s", time());
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
				$sendToEmail = getenv('SLAVEN_EMAIL');
				Mail::sendStaffMail($sendToEmail, "staffRegularFailed", $param);
			} else if($server=="alliantranslate"){
				$sendToEmail = getenv('ORDERS_EMAIL');
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
		$data = $this->decryptValues($request->data);
		$service->validate($data['CustomerID'], 'Error: No customer id is present.')->notNull()->isInt();
		$service->validate($data['phones'], 'Error: No phones array is present.')->notNull();
		$service->validate($request->twilioToken, 'Error: No twilioToken is present.')->notNull();

		$CustomerID = $data['CustomerID'];
		$customer = CustLogin::get_customer($CustomerID);
		$fullname = $customer['FName'] . ' ' . $customer['LName'];

		$phones = $data['phones'];
		$twilioToken = $request->twilioToken;
		$con = Connect::con();
		$get = mysqli_query($con, "SELECT real_queue FROM `connect_now_log` WHERE CustomerID = $CustomerID");
		if(!$get){
			return $this->encryptValues(json_encode($this->errorJson("No order in table was found for that CustomerID.")));
		}
		$cnl = mysqli_fetch_array($get);
		$http = new Services_Twilio_TinyHttp('https://api.twilio.com', array('curlopts' => array(CURLOPT_SSL_VERIFYPEER => false)));
		$sid = getenv('LIVE_TWILIO_ALLIAN_SID');
		$token = getenv('LIVE_TWILIO_ALLIAN_TOKEN');
		$client = new Services_Twilio($sid, $token, '2010-04-01', $http);
		$query_string = array('real_queue' => $cnl['real_queue'], 'fullname' => $fullname);
		$url = 'http://alliantranslate.com/testgauss/addNewMemberConnectNowOut'. '?' . http_build_query($query_string, '', '&');
		foreach($phones as $phone){
			$call = $client->account->calls->create(getenv('ADD_NEW_MEMBER'), $phone, $url, array());
		}

		$rArray['status'] = 1;
		$rArray['userMessage'] = 'Added new Member.';
		$base64Encrypted = $this->encryptValues(json_encode($rArray));
     	return $response->json(array('data' => $base64Encrypted));
	}

	public function addNewMemberConnectNowOut($request, $response, $service, $app){
		$service->real_queue = $request->real_queue;
		$service->fullname = $request->fullname;
		$service->render('./resources/views/twilio/connect/addNewMemberConnectNowOut.php');
	}

	function order_its_details($order_id,$type = ""){
	    $con = Connect::con();
	   	$result = mysqli_query($con,"SELECT * FROM CallIdentify WHERE UnqID = $order_id");
		$row = mysqli_fetch_array($result);
		$IPID1=$row['IPID'];
		CustLogin::get_customer($row["CustomerID"]);
		$CustomerName=$Customer["FName"]." ".$Customer["LName"];
	    $query = mysqli_query($con,"SELECT L1,L2 FROM LangRate WHERE PairID=".$row['PairID']);
		$row1 = mysqli_fetch_array($query);
		$lang1 = LangList::get_language_name($row1['L1']);
		$lang2 = LangList::get_language_name($row1['L2']);
		$PairID=$lang1." to ".$lang2;
		$starttime=date("Y-m-d H:i:s",$row['starttime']);
	    $msg_head="";
	    $msg_foot="";
		if($type=="Email"){
		    $msg_head = "<b>Dear $CustomerName</b>,<br><br>". "Thank you for scheduling an ITS services with Alliance Business Solutions LLC.<br><br>". "Scheduling details are as below.<br><br>";
		    $login_link = URL_SECURE."clientportal/loginform.php";
			$msg_foot = "<br>You can view your scheduling details by loging into your online portal by clicking at the link below.<br><a href='$login_link' target='_blank'>$login_link</a><br><p  style='font-weight:bold'>Thank you for choosing Alliance Business Solutions LLC Language Services.</p><p><span style='font-family: Arial; font-size: 12px; font-style: normal;'><strong>Alliance Business Solutions LLC Language Services<br /></strong><em>--Client Services</em><strong><br />Email:</strong> cs@".TRANSLATION_DOMAIN."<br /><strong>Toll Free:</strong> 1.877.512.1195<br /><strong>International:</strong> 1.615.866.5542<br /><strong>Fax:</strong> 1.615.472.7924<br /></span><a href='".HOME_SECURE."' target='_blank'>Translate</a><span style='font-family: Arial; font-size: 12px; font-style: normal;'>. | </span><a href=".INTERPRETING_HOME_SECURE."/' target='_blank'>Interpret</a><span style='font-family: Arial; font-size: 12px; font-style: normal;'>. | </span><a href='".TRANSCRIPTION_HOME_SECURE."' target='_blank'>Transcribe</a><span style='font-family: Arial; font-size: 12px; font-style: normal;'>.<br /></span><img style='font-family: Arial; font-size: 12px; font-style: normal;' src='".HOME_SECURE."logos/alliancebizsolutions_logo.png' alt='' width='150' height='58' /></p>";
		}
		$msg = $msg_head;
	    $duration = ceil($row["duration"]/60);
	    $s = $duration > 1 ?"s":"";
	    $msg .= "<table id='its_detail'><tr><td class='head'><b>Call ID: </b></td><td>$order_id</td></tr><tr><td class='head'><b>Start Time: </b></td><td>$starttime</td></tr><tr><td class='head'><b>Duration: </b></td><td>$duration Minute$s</td></tr><tr><td class=head><b>Language Pair: </b></td><td>$PairID</td></tr><tr><td class='head'><b>Rate: </b></td><td>$".ScheduleFunctions::amt_format($row['Rate']/100)." USD</td></tr><tr><td class='head'><b>Amount Charged: </b></td><td>";
		$charged = ($row['Charged']>0)?$row['Charged']:$row['Billed'];
		$msg .=  "$". ScheduleFunctions::amt_format($charged/100)." USD</td></tr></table>";
		$msg .= $msg_foot;
		return $msg;
	}

}