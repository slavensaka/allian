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
use Allian\Helpers\Allian\HotlineFunctions;
use Allian\Helpers\TwilioConference\ConferenceFunctions as ConfFunc;

class HotlineController extends Controller {

	/**
	 *
	 * Block comment TODO
	 *
	 */
	public function hotline($request, $response, $service, $app){
		$data = $this->decryptValues($request->data);
		$service->validate($data['CustomerID'], 'Error: No customer id is present.')->notNull()->isInt();
		$service->validate($data['phoneNumber'], 'Error: No phoneNumber id is present.');
		$service->validate($data['lang'], 'Error: No lang is present.')->notNull();
		$service->validate($data['translationTo'], 'Error: No translationTo id is present.')->notNull();

		$sid = getenv('S_TWILIO_SID');
		$token = getenv('S_TWILIO_TOKEN');
		$version = '2010-04-01';
		$http = new Services_Twilio_TinyHttp('https://api.twilio.com', array('curlopts' => array(CURLOPT_SSL_VERIFYPEER => false)));
		$client = new Services_Twilio($sid, $token, $version, $http);
		$capability = new Services_Twilio_Capability($sid, $token);
		$appSid = getenv('S_TEST_TWILIO_APP_SID');
		$capability->allowClientOutgoing($appSid);

		if($data['phoneNumber']){
			$urlBuild = array(
		    	"CustomerID" => $data['CustomerID'],
		    	"phoneNumber" => $data['phoneNumber'],
		    	"lang" => $data['lang'],
		    	"translationTo" => $data['translationTo']
			);
		} else {
			$urlBuild = array(
		    	"CustomerID" => $data['CustomerID'],
		    	'phoneNumber' => "+385919249906", // MOJ BROJ VERIFIED TESTING
		    	"lang" => $data['lang'],
		    	"translationTo" => $data['translationTo']
			);
		}

		$fallbackUrl = array(
	    	"CustomerID" => $data['CustomerID'],
	    	"lang" => $data['lang'],
	    	"translationTo" => $data['translationTo']
	    );

		$server = $this->serverEnv();
		$urlFirst = 'https://af3d0846.ngrok.io/';
		if($server=="alliantranslate"){
			$urlFirst = 'https://alliantranslate.com/';
		}

		$url = $urlFirst . 'testgauss/hotlineOut' . '?' . http_build_query($urlBuild, '', '&');

		$fallbackUrl = $urlFirst . 'testgauss/hotlineFallbackOut' . '?' . http_build_query($fallbackUrl, '', '&'); // TODO

		if($data['phoneNumber']){
			$call  = $client->account->calls->create(
				'+385919249906', $urlBuild['phoneNumber'], $url, array('StatusCallback' => $fallbackUrl));
		} else {
			$call  = $client->account->calls->create(
				'+385919249906', '+12014642721', $url, array('StatusCallback' => $fallbackUrl));
		}

		return $call;
	}

	/**
	 * ADD TO out.php
	 * <!-- TODO<Response>
		<Client>jenny</Client>
		<Say voice="woman">
			Welcome back to Alliance Business Solutions phone interpreting line.
		</Say>
	</Response> -->
	 *
	 */
	public function hotlineOut($request, $response, $service, $app){

		$service->validate($request->CustomerID, 'Error: No customer id is present.')->notNull()->isInt();
		$service->validate($request->lang, 'Error: No lang is present.')->notNull();
		$service->validate($request->translationTo, 'Error: No translationTo is present.')->notNull();

		$customer = CustLogin::get_customer($request->CustomerID);
		$token = ConfFunc::generateCapabilityToken($request->CustomerID);

		$customerID = $request->customerID;
		$sid = $request->CallSid;
		$from=$request->From;
		$from=str_replace('+',"", $from);

		$lang = $request->lang;
		$translationTo = $request->translationTo;

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
		$numrows= mysqli_num_rows ($result);
		if($numrows != 0){
			$flag=1;
		}
		$result1 = mysqli_query($con,"SELECT * FROM LangRate WHERE L1= '$l2' and L2='$l1' ");
		$numrows1= mysqli_num_rows ($result1);
		if($numrows1 != 0){
			$flag=2;
		}
		if($flag==1){
			$row = mysqli_fetch_array($result);
			$queue=$row['PairID'];
		}else if($flag==2){
			$row = mysqli_fetch_array($result1);
			$queue=$row['PairID'];
		}


		if($customer['Type'] == 2){ // Invoice
			$service->customer = $customer;
			$service->queue = $queue;
			$service->from = $from;
			$service->render('./resources/views/twilio/hotline/out.php');
		} else if($customer['Type'] == 1){ // Stripe
			$token = $customer['token'];
			// TODO STRIPE_KEY JE SADA NA MOJ PROMJENI NA ALEN
			$id = StripeController::preAuthCustomer($token);
			if(isset($id)){
				$service->customer = $customer;
				$service->from = $from;
				$service->stripe_queue = $id;
				$service->render('./resources/views/twilio/hotline/out.php');
			} else {
				// <Response>
				// 	<Gather numDigits="1" action="errorcard.php" method="POST">
				// 		<Say>
				// 			Your Credit Card could not be authorized. Press 1 to enter another credit card. press 2 to end this call.
				// 		</Say>
				// 	</Gather>
				// </Response>

			}

		}
	}

	public function waitForInterpreter($request, $response, $service, $app){
		$pairid=$request->pairid;
		$sid=$request->CallSid;
		$service->pairid = $pairid;
		$service->sid = $sid;
		$service->render('./resources/views/twilio/hotline/waitForInterpreter.php');
	}
	/**
	 *
	 * Block comment
	 *
	 */
	public function getStripeKey(){
		$server = trim($_SERVER['HTTP_HOST']);
		$server=trim($server);
		if($server=="localhost"){
			return Stripe::setApiKey(getenv('STRIPE_KEY'));
		} else if($server=="alliantranslate.com"){
			return Stripe::setApiKey(getenv('STRIPE_KEY_ALLIAN_TEST'));
		}
	}

	public function hotlineFallbackOut($request, $response, $service, $app){
		return "queuecallback.php";
	}



}

