<?php

namespace Allian\Http\Controllers;

use Database\Connect;
use \Dotenv\Dotenv;
use Stripe\Stripe;
use Services_Twilio;
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
use Allian\Helpers\TwilioConference\DatabaseAccess;
use Allian\Helpers\Allian\ConnectNowFunctions;
use Allian\Helpers\TwilioConference\ConferenceFunctions as ConfFunc;

class ConnectNowController extends Controller {

	/**
     * @ApiDescription(section="ConnectNow", description="Generate the twilio token for connection to connectNow interpreting.")
     * @ApiMethod(type="post")
     * @ApiRoute(name="/testgauss/connectNow")
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
			$token = ConfFunc::generateCapabilityToken($data['CustomerID']);
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

		$customer = CustLogin::get_customer($request->CustomerID);

		$CustomerID = $request->CustomerID;
		$lang = $request->lang;
		$translationTo = $request->translationTo;
		$sid = $request->CallSid;
		$from=$request->From;
		$from=str_replace('+',"", $from);

		// $addtofile['customer'] = $customer;
		$addtofile['CallSid'] = $sid;
		$addtofile['CustomerID'] = $CustomerID;
		$addtofile['From'] = $from;
		$addtofile['lang'] = $lang;
		$addtofile['translationTo'] = $translationTo;

		ConnectNowFunctions::addtofile($sid, $addtofile); // TODO remove in prod

		$l1 = LangList::langIdByName($lang);
		$l2 = LangList::langIdByName($translationTo);
		$chinese = LangList::selectChinese();
		$mandarin=LangList::selectMandarin();
		$chid=$chinese["LangId"];
		if($l1==$chid){
			$l1=$mandarin["LangId"];
		}
		if($l2==$chid){
			$l2=$mandarin["LangId"];
		}

		$con = Connect::con();
		$result = mysqli_query($con,"SELECT * FROM LangRate WHERE L1= '$l1' and L2='$l2' ");
		$numrows= mysqli_num_rows($result);
		if($numrows != 0){
			$flag = 1;
		}
		$result1 = mysqli_query($con,"SELECT * FROM LangRate WHERE L1= '$l2' and L2='$l1' ");
		$numrows1= mysqli_num_rows ($result1);
		if($numrows1 != 0){
			$flag = 2;
		}
		if($flag == 1){
			$row = mysqli_fetch_array($result);
			$queue = $row['PairID'];
		}else if($flag == 2){
			$row = mysqli_fetch_array($result1);
			$queue = $row['PairID'];
		}
		$addtofilePairQueue['queue'] = $queue;
		ConnectNowFunctions::addtofilePairQueue($sid, $addtofilePairQueue); // TODO remove in prod

		if($customer['Type'] == 2){ // Invoice
			$service->customer = $customer;
			$service->queue = $queue;
			$service->from = $from;
			$service->render('./resources/views/twilio/connect/connectOut.php');
		} else if($customer['Type'] == 1){ // Stripe
			$token = $customer['token'];
			// TODO STRIPE_KEY JE SADA NA MOJ PROMJENI NA ALEN
			$id = StripeController::preAuthCustomer($token);
			$addtofilePrepayment['token'] = $token;
			$addtofilePrepayment['id'] = $id;
			ConnectNowFunctions::addtofilePrepayment($sid, $addtofilePrepayment); // TODO remove in prod
			if(isset($id)){
				$service->customer = $customer;
				$service->from = $from;
				$service->queue = $queue;
				$service->render('./resources/views/twilio/connect/connectOut.php');
			} else {
				$service->render('./resources/views/twilio/connect/cardNotAuth.php');
			}
		}
	}

	/**
	 *
	 * Block comment
	 *
	 */
	public function connectNowQueueCallback($request, $response, $service, $app){
		$langpair = $request->id;
		$CustomerID = $request->CustomerID;
		$customerType = $request->customerType;
		$from=$request->from;
		if($from[0]!='+'){
			$number='+'.trim($from);
		}else{
			$number=trim($from);
		}
		$queueresult=$request->QueueResult;
		$queuetime=$request->QueueTime;
		if($queuetime>55 && $queueresult=="hangup"){
			$queueresult="Agent Unavailable";
		}else if($queueresult=="hangup"){
			$queueresult="Customer Hangup";
		}
		$con = Connect::con();
		$result = mysqli_query($con,"SELECT L1,L2 FROM LangRate WHERE PairId='$langpair'");
		$time=date("m/d/y G.i:s", time());
		$timestamp=time();
		if($row = mysqli_fetch_array($result)){
		  	$lang1 = mysqli_fetch_array(mysqli_query($con,"SELECT LangName FROM LangList WHERE LangId=".$row['L1']));
			$lang2 = mysqli_fetch_array(mysqli_query($con,"SELECT LangName FROM LangList WHERE LangId=".$row['L2']));
			$pair = trim($lang1['LangName'])."-".trim($lang2['LangName']);
			$pair = trim($pair);
		}
		if($queueresult!='bridged'){
			$sid=$request->CallSid;
			$type=0;
		  	// if(file_exists("customertype/".$sid.".txt")){
		  		// $data=getfromfile("customertype/".$sid);
		    $type = $customerType;
		  	// }
		  	$con = Connect::con();
		  	if($type == 0){
		  		$query="SELECT CustomerID FROM CustLogin WHERE Phone = '$number'";
		  		$result=mysqli_fetch_row(mysqli_query($con,$query));
				$customerid = $result[0];
				$mailcontent="A call from".$number." failed in the queue of ".$pair." with reason ".$queueresult." on ".$time."\n\n-admin";
		      	$param = $number.",".$pair.",".$queueresult.",".$time;
			    ConnectNowFunctions::sendstaffmail("staff_onetimecallfailed", $param);
		  	}else{
		  		$customerid = $CustomerID;
		  		$query="SELECT Email FROM CustLogin WHERE CustomerID = '$customerid'";
		  		$result=mysqli_fetch_assoc(mysqli_query($con,$query));
		  		$email=$result['Email'];
		  		$mailcontent="A call from the user with mail id :".$email." (from".$number.") failed in the queue of ".$pair." with reason ".$queueresult." on ".$time."\n\n-admin";
		    	$param=$number.",".$pair.",".$queueresult.",".$time.",".$email;
	      		ConnectNowFunctions::sendstaffmail("staff_regcallfailed", $param);
		  	}
			mysqli_query($con,"INSERT INTO CallIdentify(Type,starttime,CustomerId,FromNumber,state,duration,PairId) values ('$type','$timestamp','$customerid','$number','$queueresult','0','$langpair')");
			$sidfile="customertype/".$sid.".txt"; //TODO
			if(file_exists($sidfile)){
				unlink($sidfile);
			}
		}
		$service->render('./resources/views/twilio/connect/connectNowQueueCallback.php');
	}

	/**
	 *
	 * Block comment
	 *
	 */
	public function waitForInterpreter($request, $response, $service, $app){
		$pairid=$request->pairid;
		$service->pairid = $pairid;
		$service->render('./resources/views/twilio/connect/waitForInterpreter.php');
	}

}

// SELECT PairID FROM LangRate WHERE L1= '$l1' and L2='$l2
// ZA jezik langrate