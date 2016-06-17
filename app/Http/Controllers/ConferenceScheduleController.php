<?php

namespace Allian\Http\Controllers;

use \Dotenv\Dotenv;
use Database\Connect;
use Firebase\JWT\JWT;
use RNCryptor\Encryptor;
use RNCryptor\Decryptor;
use Allian\Helpers\Mail;
use Allian\Models\LangList;
use Allian\Models\CustLogin;
use Allian\Helpers\ArrayValues;
use Firebase\JWT\DomainException;
use Firebase\JWT\ExpiredException;
use Allian\Models\TranslationOrders;
use Firebase\JWT\BeforeValidException;
use Allian\Models\OrderOnsiteInterpreter;
use Allian\Http\Controllers\StripeController;

class ConferenceScheduleController extends Controller {

	/**
     * @ApiDescription(section="GetTimezones", description="Retrieve json too populate schedule session form fields. Timezones, langFrom, langTo, countries, schedulingType, neededFor")
     * @ApiMethod(type="get")
     @ApiParams(name="data", type="string", nullable=false, description="Data")
     * @ApiParams(name="token", type="string", nullable=false, description="Autentication token for users autentication.")
     * @ApiRoute(name="/testgauss/getTimezones")
     * @ApiBody(sample="{ 'data': {'CustomerID': '800'},
     'token': 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzUxMiJ9.eyJpYXQiOjE0NjUzODMxNjUsImp0aSI6IlJpRW16NzRHSGhGR043QzEzT1JpQ1FuWXRnOHJ4bk9YVHRRZ002NnBDN1E9IiwiaXNzIjoiYWxsaWFudHJhbnNsYXRlLmNvbSIsIm5iZiI6MTQ2NTM4MzE2NSwiZXhwIjoxNDY2NTkyNzY1LCJkYXRhIjp7IlN1Y2Nlc3MiOiJTdWNjZXNzIn19.DvPdwcIGybU3zs5NH4NRmldNbhrer8AgvSSwi9lBY6SwJ-WKegETMRQmXZvtLu5-qrAx5hwBkEKXqG80zTqByw'}")
     * @ApiReturnHeaders(sample="HTTP 200 OK")
     * @ApiReturn(type="string", sample="{'data': {'timezonesTop':'...', 'timezones':'...', 'langFrom': '...', 'langTo': '...', 'countries': '...', 'schedulingType': '...', 'neededFor': '...'}}")
     */
	public function getTimezones($request, $response, $service, $app){
		if($request->token){
			// Validate token if not expired, or tampered with
			$this->validateToken($request->token);
			//Decrypt input data
			$data = $this->decryptValues($request->data);
			// Validate input data
			$service->validate($data['CustomerID'], 'Error: No customer id is present.')->notNull()->isInt();
			// Validate token in database for customer stored
			$validated = $this->validateTokenInDatabase($request->token, $data['CustomerID']);
			// If error validating token in database
			if(!$validated){
	     		return $response->json(array('data' => $this->errorJson("Authentication problems. CustomerID doesn't match that with token")));
			}
			// Retrieve array values for populating scheduling form
			$timezonesTop =  ArrayValues::timezonesTop();
			$timezones =  ArrayValues::timezones();
			$langFrom = ArrayValues::langFrom();
			$langTo = ArrayValues::langTo();
			$countries =  ArrayValues::countries();
			$schedulingType = ArrayValues::schedulingType();
			$neededFor =  ArrayValues::neededFor();
			// Merge all arrays into one
			$result = array_merge($timezonesTop, $timezones, $langFrom, $langTo, $countries, $schedulingType, $neededFor);
			// Format & return response
	     	return $response->json(array('data' => $result));
	    } else {
	    	return $response->json($this->errorJson("No token provided"));
	    }
	}

	/**
     * @ApiDescription(section="SchedulePartOne", description="Retrieve the first part of the payment after user selects end time.")
     * @ApiMethod(type="post")
     * @ApiRoute(name="/testgauss/schedulePartOne")
     * @ApiBody(sample="{ 'data': {
			    'CustomerID': '800',
			    'fromDate': '2016-06-07',
			    'timeStarts': '3:00:00 AM',
			    'timeEnds': '3:05:00 AM'
			  },
     'token': 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzUxMiJ9.eyJpYXQiOjE0NjUzODMxNjUsImp0aSI6IlJpRW16NzRHSGhGR043QzEzT1JpQ1FuWXRnOHJ4bk9YVHRRZ002NnBDN1E9IiwiaXNzIjoiYWxsaWFudHJhbnNsYXRlLmNvbSIsIm5iZiI6MTQ2NTM4MzE2NSwiZXhwIjoxNDY2NTkyNzY1LCJkYXRhIjp7IlN1Y2Nlc3MiOiJTdWNjZXNzIn19.DvPdwcIGybU3zs5NH4NRmldNbhrer8AgvSSwi9lBY6SwJ-WKegETMRQmXZvtLu5-qrAx5hwBkEKXqG80zTqByw'}")
     * @ApiParams(name="data", type="string", nullable=false, description="Data")
     * @ApiParams(name="token", type="string", nullable=false, description="Autentication token for users autentication.")
     * @ApiReturnHeaders(sample="HTTP 200 OK")
     * @ApiReturn(type="string", sample="{'data': {
	    'totalPrice': '30.00', 'status': 1,
	    'daily': 'ATS - Short Notice Telephonic Scheduling ($3/Min) for 5 minutes',
	    'minimumText': 'Minimum Short Notice telephonic scheduling price is $30'
	  	}}")
     */
	public function schedulePartOne($request, $response, $service, $app){
		if($request->token){
			// Validate token if not expired, or tampered with
			$this->validateToken($request->token);
			// Decrypt input data
			$data = $this->decryptValues($request->data);
			// Validate input data
			$service->validate($data['CustomerID'], 'Error: No customer id is present.')->notNull()->isInt();
			$service->validate($data['fromDate'], 'Error: from date not present.')->notNull();
			$service->validate($data['timeStarts'], 'Error: timeStarts not present.')->notNull();
			$service->validate($data['timeEnds'], 'Error: timeEnds not present.')->notNull();
			//Validate the jwt token in the database
			$validated = $this->validateTokenInDatabase($request->token, $data['CustomerID']);
			// If error validating token in database
			if(!$validated){
				$base64Encrypted = $this->encryptValues(json_encode($this->errorJson("Authentication problems. CustomerID doesn't match that with token")));
	     		return $response->json(array('data' => $base64Encrypted));
			}
			$frm_time = $data['fromDate'] . ' ' . $data['timeStarts'];
			$to_time = $data['fromDate'] . ' ' . $data['timeEnds'];

			$details = array();
			$amount = 0;
			$timing = $this->get_assignment_time($frm_time,$to_time);

			$hours_left = $timing["hours_to_start"];
			$minimum_rate = 30;
			$conference_fee = $this->amt_format(5);
			if($hours_left<24) {
			    $minimum_minutes = 10;
			    $rate_per_min=3;
			    $scheduling_type="Short Notice";
			}else{
			    $minimum_minutes = 15;
			    $rate_per_min=1.75;
			    $scheduling_type="Regular";
			}
	 		$minutes = $this->telephonic_duration($frm_time, $to_time);
			$actual_minutes = $minutes;
			$minimum_appied = ($minutes <= $minimum_minutes) ? true : false;
			$minutes = ($minimum_appied) ? $minimum_minutes : $minutes;

			$minimum_text = ($minimum_appied) ? "Minimum $scheduling_type telephonic scheduling price is $$minimum_rate" : "";
			$amount += ($minimum_appied) ? $minimum_rate : $rate_per_min * $minutes;
			$amount = $this->amt_format($amount);

			$rArray = array();
			$rArray['totalPrice'] = $this->amt_format($amount);
			$rArray['daily'] = "ATS - $scheduling_type Telephonic Scheduling ($$rate_per_min/Min) for $actual_minutes minutes";
			$rArray['status'] = 1;
			if($minimum_text){
				$rArray['minimumText'] = $minimum_text ;
			} else {
				$rArray['minimumText'] = null;
			}
			$base64Encrypted = $this->encryptValues(json_encode($rArray));
			// Return response json
			return $response->json(array('data' => $base64Encrypted));
		} else {
	    	$base64Encrypted = $this->encryptValues(json_encode($this->errorJson("No token provided in request")));
     		return $response->json(array('data' => $base64Encrypted));
	    }
	}

	/**
     * @ApiDescription(section="SchedulePartTwo", description="Retrieve the second part of the payment after user selects or diselects scheduling type.")
     * @ApiMethod(type="post")
     * @ApiRoute(name="/testgauss/schedulePartTwo")
     * @ApiBody(sample="{ 'data': {
	    'CustomerID': '800',
	    'fromDate': '2016-06-07',
	    'timeStarts': '3:00:00 AM',
	    'timeEnds': '3:05:00 AM',

	    'schedulingType': 'conference_call'
	  },
     'token': 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzUxMiJ9.eyJpYXQiOjE0NjUzODMxNjUsImp0aSI6IlJpRW16NzRHSGhGR043QzEzT1JpQ1FuWXRnOHJ4bk9YVHRRZ002NnBDN1E9IiwiaXNzIjoiYWxsaWFudHJhbnNsYXRlLmNvbSIsIm5iZiI6MTQ2NTM4MzE2NSwiZXhwIjoxNDY2NTkyNzY1LCJkYXRhIjp7IlN1Y2Nlc3MiOiJTdWNjZXNzIn19.DvPdwcIGybU3zs5NH4NRmldNbhrer8AgvSSwi9lBY6SwJ-WKegETMRQmXZvtLu5-qrAx5hwBkEKXqG80zTqByw'}")
     * @ApiParams(name="data", type="string", nullable=false, description="CustomerId.")
     * @ApiParams(name="token", type="string", nullable=false, description="Autentication token for users autentication.")
     * @ApiReturnHeaders(sample="HTTP 200 OK")
     * @ApiReturn(type="string", sample="{'data': {
	    'daily': 'ATS - Short Notice Telephonic Scheduling ($3/Min) for 5 minutes',
	    'minimumText': 'Minimum Short Notice telephonic scheduling price is $30',
	    'status': 1,
	    'conferencePresent': 'Conference Calling Fee:: $5.00',
	    'totalPrice': '35.00'
  		}}")
     */
	public function schedulePartTwo($request, $response, $service, $app){
		if($request->token){
			// Validate token if not expired, or tampered with
			$this->validateToken($request->token);
			// Decrypt input data
			$data = $this->decryptValues($request->data);
			// Validate input data
			$service->validate($data['CustomerID'], 'Error: No customer id is present.')->notNull()->isInt();
			// $service->validate($data['timezone'], 'Error: timezone not present.')->notNull();
			$service->validate($data['fromDate'], 'Error: from date not present.')->notNull();
			$service->validate($data['timeStarts'], 'Error: timeStarts not present.')->notNull();
			$service->validate($data['timeEnds'], 'Error: timeEnds not present.')->notNull();
			$service->validate($data['schedulingType'], 'Error: from date not present.')->notNull();
			// Validate token in database
			$validated = $this->validateTokenInDatabase($request->token, $data['CustomerID']);
			// If error validating token in database
			if(!$validated){
				$base64Encrypted = $this->encryptValues(json_encode($this->errorJson("Authentication problems. CustomerID doesn't match that with token")));
	     		return $response->json(array('data' => $base64Encrypted));
			}
			$frm_time = $data['fromDate'] . ' ' . $data['timeStarts'];
			$to_time = $data['fromDate'] . ' ' . $data['timeEnds'];

			$details = array();
			$amount = 0;
			$timing = $this->get_assignment_time($frm_time,$to_time);

			$hours_left = $timing["hours_to_start"];
			$minimum_rate = 30; // $30
			$conference_fee = $this->amt_format(5);
			if($hours_left<24) {
			    $minimum_minutes = 10; // 10 minutes
			    $rate_per_min=3; // $3
			    $scheduling_type="Short Notice";
			}else{
			    $minimum_minutes = 15;  // 30 minutes
			    $rate_per_min=1.75; // $1.75
			    $scheduling_type="Regular";
			}
	 		$minutes = $this->telephonic_duration($frm_time, $to_time);
			$actual_minutes = $minutes;
			$minimum_appied = ($minutes <= $minimum_minutes) ? true : false;
			$minutes = ($minimum_appied) ? $minimum_minutes : $minutes;

			$minimum_text = ($minimum_appied) ? "Minimum $scheduling_type telephonic scheduling price is $$minimum_rate" : "";
			$amount += ($minimum_appied) ? $minimum_rate : $rate_per_min * $minutes;
			$amount = $this->amt_format($amount);

			$rArray = array();
			$rArray['daily'] = "ATS - $scheduling_type Telephonic Scheduling ($$rate_per_min/Min) for $actual_minutes minutes";
			if($minimum_text){
				$rArray['minimumText'] = $minimum_text ;
			} else {
				$rArray['minimumText'] = null;
			}

			if ($data['schedulingType'] == 'conference_call') {
			    $amount+= $conference_fee;
			    $rArray['conferencePresent'] = "Conference Calling Fee:: $$conference_fee";
			} else{
				$rArray['conferencePresent'] = null;
			}
			$rArray['totalPrice'] = $this->amt_format($amount);
			$rArray['status'] = 1;
			// Return response json
			$base64Encrypted = $this->encryptValues(json_encode($rArray));
			return $response->json(array('data' => $base64Encrypted));
		} else {
	    	$base64Encrypted = $this->encryptValues(json_encode($this->errorJson("No token provided in request")));
     		return $response->json(array('data' => $base64Encrypted));
	    }
	}

	/**
     * @ApiDescription(section="ScheduleFinal", description="Send everything in form. Store in database, schedule new conference, update...")
     * @ApiMethod(type="post")
     * @ApiRoute(name="/testgauss/scheduleFinal")
     * @ApiBody(sample="{ 'data': {
	    'CustomerID': '800',
	    'timezone': 'US/Central',
	    'fromDate': '2016-06-07',
	    'timeStarts': '3:00:00 AM',
	    'timeEnds': '3:05:00 AM',
	    'langFrom': 'Spanish',
	    'langTo': 'Arabic',
	    'country': 'Canada',
	    'schedulingType': 'get_call',
	    'clients': [
	      '+16757568578'
	    ],
	    'neededFor': 'Court',
	    'description': 'Opis zašto treba prijevod'
  		},
     'token': 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzUxMiJ9.eyJpYXQiOjE0NjUzODMxNjUsImp0aSI6IlJpRW16NzRHSGhGR043QzEzT1JpQ1FuWXRnOHJ4bk9YVHRRZ002NnBDN1E9IiwiaXNzIjoiYWxsaWFudHJhbnNsYXRlLmNvbSIsIm5iZiI6MTQ2NTM4MzE2NSwiZXhwIjoxNDY2NTkyNzY1LCJkYXRhIjp7IlN1Y2Nlc3MiOiJTdWNjZXNzIn19.DvPdwcIGybU3zs5NH4NRmldNbhrer8AgvSSwi9lBY6SwJ-WKegETMRQmXZvtLu5-qrAx5hwBkEKXqG80zTqByw'}")
     * @ApiParams(name="data", type="string", nullable=false, description="CustomerId.")
     * @ApiParams(name="token", type="string", nullable=false, description="Autentication token for users autentication.")
     * @ApiReturnHeaders(sample="HTTP 200 OK")
     * @ApiReturn(type="string", sample="{'data': {
	    'timezone': 'US/Central',
	    'status': 1,
	    'confStarts': '2016-06-07 3:00:00 AM',
	    'confEnds': '2016-06-07 3:05:00 AM',
	    'confCode': '12345',
	    'confDialNumber': '+18555129043'
	  	}}")
     */
	public function scheduleFinal($request, $response, $service, $app){
		if($request->token){
			// Validate token if not expired, or tampered with
			$this->validateToken($request->token);
			// Decrypt input data
			$data = $this->decryptValues($request->data);
			// Validate input data
			$service->validate($data['CustomerID'], 'Error: No customer id is present.')->notNull()->isInt();
			$service->validate($data['timezone'], 'Error: timezone not present.')->notNull();
			$service->validate($data['fromDate'], 'Error: from date not present.')->notNull();
			$service->validate($data['timeStarts'], 'Error: time starts not present.')->notNull();
			$service->validate($data['timeEnds'], 'Error: timeEnds not present.')->notNull();
			$service->validate($data['langFrom'], 'Error: lang from not present.')->notNull();
			$service->validate($data['langTo'], 'Error: lang to present.')->notNull();
			$service->validate($data['country'], 'Error: country not present.')->notNull();
			$service->validate($data['schedulingType'], 'Error: schedulingType not present.')->notNull();
			$service->validate($data['clients'], 'Error: clients not present.')->notNull();
			// $service->validate($data['neededFor'], 'Error: neededFor not present.')->notNull();
			$service->validate($data['description'], 'Error: description not present.')->notNull();

			$validated = $this->validateTokenInDatabase($request->token, $data['CustomerID']);
			// If error validating token in database
			if(!$validated){
				$base64Encrypted = $this->encryptValues(json_encode($this->errorJson("Authentication problems. CustomerID doesn't match that with token")));
	     		return $response->json(array('data' => $base64Encrypted));
			}

			$frm_time = $data['fromDate'] . ' ' . $data['timeStarts'];
			$to_time = $data['fromDate'] . ' ' . $data['timeEnds'];

			$assg_frm_st = $data['timeStarts'];
			$assg_frm_en = $data['timeEnds'];

			$frmT = new \DateTime($data['fromDate'].' '.$data['timeStarts'],new \DateTimeZone($data['timezone']));
			$frmT->setTimezone(new \DateTimeZone('GMT'));

		    $toT = new \DateTime($data['fromDate'].' '.$data['timeEnds'],new \DateTimeZone($data['timezone']));
			$toT->setTimezone(new \DateTimeZone('GMT'));

			$frm_lang = LangList::langIdByName($data['langFrom']);
			$to_lang = LangList::langIdByName($data['langTo']);

			$details = array();
			$amount = 0;
			$timing = $this->get_assignment_time($frm_time,$to_time);

			$hours_left = $timing["hours_to_start"];
			$minimum_rate = 30;
			$conference_fee = $this->amt_format(5);
			if($hours_left<24) {
			    $minimum_minutes = 10;
			    $rate_per_min=3;
			    $scheduling_type="Short Notice";
			}else{
			    $minimum_minutes = 15;
			    $rate_per_min=1.75;
			    $scheduling_type="Regular";
			}
	 		$minutes = $this->telephonic_duration($frm_time, $to_time);
			$actual_minutes = $minutes;
			$minimum_appied = ($minutes <= $minimum_minutes) ? true : false;
			$minutes = ($minimum_appied) ? $minimum_minutes : $minutes;

			$minimum_text = ($minimum_appied) ? "Minimum $scheduling_type telephonic scheduling price is $$minimum_rate" : "";
			$amount += ($minimum_appied) ? $minimum_rate : $rate_per_min * $minutes;
			$amount = $this->amt_format($amount);

			if ($data['schedulingType'] == 'conference_call') {
			    $amount+= $conference_fee;

			} elseif($data['schedulingType'] == 'get_call'){

			}
			$sArray = array();
			$sArray['customer_id'] = $data['CustomerID'];
			$sArray['assg_frm_date'] = $data['fromDate'];
			$sArray['assg_frm_st'] = date("H:i:s",strtotime($assg_frm_st));
			$sArray['assg_frm_en'] = date("H:i:s",strtotime($assg_frm_en));
			$sArray['assg_to_date'] = $data['fromDate'];
			$sArray['timezone'] = $data['timezone'];
			$sArray['assg_frm_timestamp'] =$frmT->format('U');
			$sArray['assg_to_timestamp'] =$toT->format('U');
			// $sArray['interpreting_dur'] =$NESTO['interpreting_duration'];
			$sArray['scheduling_type'] = $data['schedulingType'];
			$sArray['frm_lang'] = $frm_lang; // broj languagea
			$sArray['to_lang'] = $to_lang; // broj Languagea
			$sArray['country'] = $data['country'];
			if ($data['schedulingType'] == 'conference_call') {
				$sArray['onsite_con_phone'] = $data['clients']; //TODO pogledaj order_telephonic_notification 2393 telephonic_conference_recipients
			} elseif($data['schedulingType'] == 'get_call'){
				$sArray['onsite_con_phone'] = $data['clients'][0];
			}
			$sArray['intr_needed_for'] = $data['neededFor'];
			$sArray['description'] = $data['description'];
			$sArray['orderID'] = md5(time().$data['description']);
			$sArray['currency_code']='usd';
			$sArray['interpreting_type'] = '';
			$sArray['service_region'] = '';
			$sArray['service_area'] = '';
			$sArray['dist_frm_reg_srvc_area'] = 0;
			$sArray['address_line_2'] = '';
			$sArray['onsite_con_name'] = '';
			$sArray['onsite_con_email'] = '';
			$sArray['headsets_needed'] = 0;
			$sArray['scheduling_type'] = $data['schedulingType'];
			if(intval($data['amount'])<=29){ // Amount check if less than 29$ error
				$all_data_valid=false;
			}
			$sArray['amount'] = $amount;
			if($data['schedulingType'] == 'get_call'){
				$sArray['interpreter_amt'] = (25/100)*$amount;  // caluculate_price() TODO
			} elseif($data['schedulingType'] = 'conference_call'){
				$sArray['interpreter_amt'] = (25/100)*($amount-5); // caluculate_price() TODO
			}

			$sArray['interpreting_dur']= $this->telephonic_duration(
										$data['fromDate'].'T'.$sArray['assg_frm_st'],
										$data['fromDate'].'T'.$sArray['assg_frm_en']);

			$onsiteAutoId = OrderOnsiteInterpreter::insertScheduleOrder($sArray);
			if(!$onsiteAutoId){
				return $response->json("Onsite order not inserted"); // TODO encrypt
			}
			$orderID = TranslationOrders::insertTransationOrder($sArray);
			if(!$orderID){
				return $response->json("Order not inserted"); // TODO encrypt
			}

			$updated = OrderOnsiteInterpreter::updateScheduleOrderID($orderID, $onsiteAutoId);

			if(!$updated){
				return $response->json("Problem with updating order_onsite_interpreter table"); // TODO encrypt
			}

			$customer = CustLogin::getCustomer($sArray['customer_id']);

			/*=============================================
			=     CALCULATE DISCOUNT PROMOTIONAL CODE TODO =
			=============================================*/
			$dArray = array();// orders_scritp/get_discoutn.php
			$dArray['usps_fee'] = 0;
			$dArray['shipping_fee'] = 0;
			$dArray['Apostille_Price'] = 0;
			$dArray['DTP_Price'] = 0;
			$dArray['RP_Price'] = 0;
			$dArray['Copies_Price'] = 0;
			$dArray['Verbatim_Price'] = 0;
			$dArray['TS_Price'] = 0;
			$dArray['TS_Type'] = 0;
			$dArray['additional_fee'] = 0;
			$dArray['add_RP']= "";
			if($RP_Price>0){
			    $turn_around = 8; // 8 Hours
			    $time_starts = "8:00 AM";
			    $time_ends   = "6:00 PM";
			    $est_time = $this->times($time_starts, $time_ends, $turn_around, "Sat");
			    $dArray['add_RP'] = ",rp_delivery='".$est_time."' ";
    		}
    		$charge_amount = number_format(($sArray['amount'] + $Apostille_Price + $usps_fee + $shipping_fee + $RP_Price + $DTP_Price + $Copies_Price + $additional_fee + $TS_Price + $Verbatim_Price), 2, ".", "") * 100;
    		// $discount_off = $this->apply_discount($charge_amount);
    		$charge_amount -= $discount_off;
    		$discount_off /= 100;
    		$desc = ($discount_off>0) ? "$email gets discount of $discount_off USD" : $email;
   			//$this->set_translation_order($con, $orderID, "discount", $discount_off);
     		//function set_translation_order($con, $order_id, $set, $value) { //
			//     return mysqli_query($con, "UPDATE translation_orders SET $set='$value' WHERE order_id =  '$order_id'");
			// }
			// function apply_discount($order_total) {
			//     $discount = 0;
			// 	// Discount Calculator
			//     if (isset($_SESSION["Discount"])) {
			//         $discount_session = explode("-", $_SESSION["Discount"]);
			//         $discount_type = $discount_session[0];
			//         $discount_amount = $discount_session[1];
			//         $discount = ($discount_type === "$") ? $discount_amount * 100 : ($discount_amount) * ($order_total / 100);
			//     }
			// 	// return discount in cents
			//     return ceil($discount);
			// }
			// function times($startTime="8:00 AM", $endTime="6:00 PM", $turn_around=8, $skipDay="Sat") {
			//     $turn_around *= 3600; // convert to seconds
			//     $oneDay = 86400; //Seconds in a Day
			//     $startTime = strtotime($startTime);
			//     $endTime = strtotime($endTime);
			//     $current_time = time(); // current timestamp
			//     $skipTomorrow = (date("D", $current_time + $oneDay) === $skipDay) ? $oneDay : 0; // Check if tomorrow is off
			//     $todayIsOFF = (date("D", $current_time) === $skipDay) ? 1 : 0; // Check if today is off
			//     if ($todayIsOFF) {
			//         // If today is OFF (sat) then Consider Next Delivery Day
			//         $est_time = $startTime + $turn_around + $oneDay;
			//     } else {
			//         $inTime = $current_time >= $startTime && $current_time <= $endTime;

			//         if ($inTime) {
			//             // Time has started today
			//         $est_time = $current_time + $turn_around;
			//         if ($est_time > $endTime) {
			//                 // Time frame does not complete today, add additional hours to next day
			//                 // Add tomorrow's time in start time
			//                 $startTime += ($oneDay + $skipTomorrow); // seconds in 24 hours
			//                 $est_time = $startTime + ($est_time - $endTime);
			//             }
			//         } else {
			//             // Time has not started today or has passed
			//             if ($current_time > $endTime) {
			//                 // Today's time frame has passed, Count turnaround from tomorrow
			//                 // Add tomorrow's time in start time
			//                 $startTime += ($oneDay + $skipTomorrow); // seconds in 24 hours
			//             }
			//             $est_time = $startTime + $turn_around;
			//         }
			//     }
			//     return $est_time;
			// }
			/*=====  End of Section comment block  ======*/

			// Charge the customer an amount
			$stripe = new StripeController();
			$stripe_id = $stripe->chargeCustomer($charge_amount, $customer->getValueEncoded('token'));
			// If stripe error where charge token is not returned
			if(!$stripe_id){
				return $response->json("Stripe error"); // TODO encrypt
			}

			$dArray['stripe_id'] = $stripe_id;
			$dArray['order_id'] = $orderID;

			// Update translation orders with new info, user paid & did or not had discount
			$complete = TranslationOrders::updateTranslationOrdersSch($dArray);


	    	if(!$complete){
	    		return $response->json("Problems with updating translation orders."); // TODO encrypt
	    	}

	    	$return = $this->order_telephonic_notification($orderID, $data['CustomerID'], $customer->getValueEncoded('Email'));
	    	return $response->json($return);

			$retArray = array();
			$retArray['timezone'] = $data['timezone'];
			$retArray['status'] = 1;
			$retArray['confStarts'] = $data['fromDate'] . ' ' . $data['timeStarts'];
			$retArray['confEnds'] = $data['fromDate'] . ' ' . $data['timeEnds'];
			$retArray['confCode'] = "12345"; //TODO
			$retArray['confDialNumber'] = "+18555129043";

			$base64Encrypted = $this->encryptValues(json_encode($retArray));
	 		return $response->json(array('data' => $base64Encrypted));
		} else {
	    	$base64Encrypted = $this->encryptValues(json_encode($this->errorJson("No token provided in request")));
	 		return $response->json(array('data' => $base64Encrypted));
    	}

	}

	/**
	 *
	 * Block comment
	 *
	 */
	function get_assignment_time($actual_starting_time, $actual_ending_time = "") {
	    $time_difference = strtotime($actual_starting_time) - time();
	    $duration = strtotime($actual_ending_time) - strtotime($actual_starting_time);
	    $hours = floor($time_difference / 3600);
	    $hours = ($hours < 10) ? "0" . $hours : $hours;
	    $minutes = floor(($time_difference / 60) % 60);
	    $minutes = ($minutes < 10) ? "0" . $minutes : $minutes;
	    $seconds = $time_difference % 60;
	    $seconds = ($seconds < 10) ? "0" . $seconds : $seconds;
	    $time_left = "$hours:$minutes:$seconds";
	    // True if start time is less than 24
	    // False if start time is greater than 24 hours
	    // False if start time is less than an Hour (no need to send 24hr prior notification when only 1 hour is left)
	    $notify = (86400 > $time_difference && $time_difference > 3600); // 24 hours left
	    $notify_checkout = ($actual_ending_time == "") ? false : time() > strtotime($actual_ending_time);
	    return array("notify_24_hours" => $notify, "notify_checkout" => $notify_checkout, "time_to_start" => $time_left, "hours_to_start" => $hours, "minutes_to_start" => $minutes, "time" => $time_difference, "duration" => $duration);
	}

	/**
	 *
	 * Block comment
	 *
	 */
	function amt_format($amt, $decimels = "2", $decimel_point = ".", $thousand_sep = "") {
		return number_format($amt, $decimels, $decimel_point, $thousand_sep);
	}

	/**
	 *
	 * Block comment
	 *
	 */
	function telephonic_duration($frm_time, $to_time) {

	    $start_date = new \DateTime($frm_time);
	    $since_start = $start_date->diff(new \DateTime($to_time));

	    $days = $since_start->d + 1;
	    $minutes += $since_start->h * 60;
	    $minutes += $since_start->i;
	    //echo "<br>".$minutes."<br>";
	    return $minutes * $days;
	}

	/* order_telephonic_notification function is responsible for sending notification to admin, to linguists and to customer who placed telephonic interpreting orders.
	@Param  $con: Required to create database connection. Required argument.
	@Param  $order_id: It is the Order Id against what the reports/reciept/template is generated and notification is sent. Required argument.
	Usage:
	1: Please press Ctrl+Shift+f
	2: A search Window asks to search specific function. You may search "order_telephonic_notification(" without double quotes and with opening parentheses.
	3: choose directory to search within and press "Find" Button
	4: The "Search Results" panel will search and display the pages where this functions has been used in the code.

	*/
	function order_telephonic_notification($order_id, $CustomerID, $CustEmail) {
	    // require_once '../emailhandler.php';
	    // require_once '../onsite_check_lang_pair.php';
	    //just double check and keep the order type RIGHT
	    // Telephonic order type is '5'
	    // mysqli_query($con, "UPDATE `translation_orders` SET `order_type` = '5' WHERE `order_id` = $order_id;");
	    $con = Connect::con();
	    $CustID = $CustomerID;

	    $Order_Discount = TranslationOrders::getTranslationOrder($order_id, "discount");
	    // $additional_fee = ($_SESSION["admin-order"] && isset($_SESSION['admin_logged']) && isset($_SESSION["Fee"])) ? $_SESSION["Fee"] : 0;
	    // $additional_fee_desc = ($_SESSION["admin-order"] && isset($_SESSION['admin_logged']) && isset($_SESSION["Fee_Desc"])) ? $_SESSION["Fee_Desc"] : "";
	    $order_summary_html = (isset($_SESSION["order_summary_html"])) ? mysqli_real_escape_string($con, $_SESSION["order_summary_html"]) : "";
	    $Interpreter_Discount = $this->calc_interpreter_compensation($order_id, $Order_Discount);
	    $additional_compensation = $this->calc_interpreter_compensation($order_id, $additional_fee);
	    $get_interpreter_query = "select * from order_onsite_interpreter where  orderID='$order_id'";
	    //write_mail_log($query);
		//mail("goharulzaman@yahoo.com","debugging  $Order_Discount",$update_query);
	    $result = mysqli_query($con, $get_interpreter_query);
		//$result = false;
	    if ($result && mysqli_num_rows($result) == 1) {
	        //var_dump($result);
	        $order = mysqli_fetch_assoc($result);
	        $fromDate = $order["assg_frm_date"];
	        $toDate = $order["assg_to_date"];
	        $fromTime = $order["assg_frm_st"];
	        $toTime = $order["assg_frm_en"];
	        $fromDateTime = $fromDate . " " . $fromTime;
	        $toDateTime = $toDate . " " . $toTime;
	        $timing = $this->get_assignment_time($fromDateTime, $toDateTime);
	        /*=================================================
	        =            hours_left nije potrebno            =
	        =================================================*/
	        // $hours_left = $timing["hours_to_start"];
	        // if ($hours_left < 24) {
	        //     $overage_amt_per_30min = get_setting_tbl($con, "tele_short_intrp_ovrg_comp_1m", 5);
	        // } else {
	        //     $overage_amt_per_30min = get_setting_tbl($con, "tele_regular_intrp_ovrg_comp_1m", 5);
	        // }
	        //echo $toDateTime; exit;
	        //debug("time left".$hours_left);
	        /*=====  End of hours_left nije potrebno  ======*/
	        $client_country = $order["country"];
	        $client_service_area = $order["service_area"];
	        $client_lang_from_id = $order["frm_lang"];
	        $client_lang_to_id = $order["to_lang"];
	        $filter_linguist_query = "SELECT * FROM Login WHERE Telephonic IN ('1','2') ";
	        $filter_linguist_query .= " AND Active = 1 AND Telephonic = 2 AND Status = 3";
	         $result = mysqli_query($con, $filter_linguist_query);
	        $bold = "style='font-weight:bold'";
	        $heading = "style='background-color:#f2f2f2; padding:5px 5px; margin:10px 0'";
	        $fromLang = trim(LangList::get_language_name($client_lang_from_id));
	        $toLang = trim(LangList::get_language_name($client_lang_to_id));
	        $interpreter_details = "<p $bold> New Telephonic project#$order_id scheduled </p><br><br>";
	        $interpreter_details .= "<h3 $heading>Project Date</h3>";
	        $interpreter_details .= "<p><span $bold>From:</span> $fromDate <br><span $bold>To:</span> $toDate</p>";
	        $interpreter_details .= "<h3 $heading>Project Languages</h3>";
	        $interpreter_details .= "<p><span $bold>From:</span> $fromLang <br><span $bold>To:</span> $toLang</p>";
	        $interpreter_details .= "<h3 $heading>Interpreters Notified</h3>";
	        $interpreter_details .= "<table>";
	        $interpreter_details .= "<tr><td $bold width='50px'>IPID</td><td $bold width='150px'>Name</td><td $bold width='200px'>Email</td><td $bold width='100px'>Phone</td><td $bold width='100px'>Enabled?</td></tr>";
	        $enabled_num = 0;
	        $disabled_num = 0;
	        $disabled_num_app = 0;
	        $sn = 0;
	        $telephonic_linguist = "0";
	        // Update onsite order id with current operating order ID
	         $update_query = "UPDATE `order_onsite_interpreter` set  customer_id='$CustID',amount='(amount-$Order_Discount+$additional_fee)',additional_fee = '$additional_fee', additional_fee_desc = '$additional_fee_desc', order_summary_html='$order_summary_html',  interpreter_amt = (interpreter_amt-$Interpreter_Discount+$additional_compensation), overage_amt_per_30min='$overage_amt_per_30min' WHERE orderID='" . $order_id . "'";
	        mysqli_query($con, $update_query);
	        while ($row = mysqli_fetch_assoc($result)) {
	            $sn++;
	            // Prepare the list of interpreter notified
	             $email = $row["Email"];
	            $name = $row["Fname"] . " " . $row["Lname"];
	            $phone = $row["Phone"];
	            $is_telephonic_linguist_lang_pair_approved = $this->check_lang_pair_telephonic($row['IPID'], $client_lang_from_id, $client_lang_to_id);
	            //var_dump($is_linguist_lang_pair) ;
	            $resources = false;
	            if ($is_telephonic_linguist_lang_pair_approved) {
	                // Telephonic Check
	                //sendlinguistmail('onsite_interpreter_alert',$row['IPID'],$order_id);
	                $enabled = "Yes";
	                $style = "style='background:darkred;color:white'";
	                $enabled_num++;
	                $resources = true;
	                // Prepare a list of telephonic interpreters that later on read by corn job to
	                // send periodic emails; send to 25 and wait for 20 minutes until it is accepted and so on.
	                $telephonic_linguist .= "|" . $row['IPID'];
	            }
	            if ($resources) {
	                $interpreter_details .= "<tr $style><td>{$row['IPID']}</td><td>$name</td><td>$email</td><td>$phone</td><td>$enabled</td></tr>";
	            }
	        }
	        // Developer's Customer ID for testing
	        if ($CustID == "305") {
	            //debug("telephonic",$Interpreter_Discount);
	            //echo $telephonic_linguist;
	            //exit();
	        }
	        $interpreter_details .= "</table>";
	        $interpreter_details .= "<br><br><strong>Total Enabled:</strong> $enabled_num <br><strong>Total Not-Enabled:</strong> $disabled_num <br><strong>Total Not-Enabled (LangPair Approved):</strong> $disabled_num_app <br>";
	        // Set telephonic linguists in table
	        // so that this is read by cronjob telephonic_interpreter_notification_cronjob.php file to
	        // send notifications to enabled linguists
	        $update_query = "UPDATE `order_onsite_interpreter` set telephonic_linguists_cj='$telephonic_linguist'  WHERE orderID='" . $order_id . "'";
	        mysqli_query($con, $update_query);
	        if ($order["scheduling_type"] == "conference_call") { // TODO za conference_call,
	            //set up conference
	            require_once '../twilio-conf-enhanced/shedule_conf.php'; // Koristi se enhanced twilio
	            $date = date_create_from_format('U', $order['assg_frm_timestamp']);
	            $conf_from = date_format($date, 'Y-m-d H:i');
	            $date = date_create_from_format('U', $order['assg_to_timestamp']);
	            $conf_to = date_format($date, 'Y-m-d H:i');
	            $conf = shedule_conference($order_id, $conf_from, $conf_to);
	            $conf_id = $conf['conf_id'];
	            if ($conf_id > 0) {
	                $conf['client_name'] = ucwords(get_translation_order($con, $order_id, "name"));
	                $conf['project_starts'] = $fromDateTime;
	                $conf['project_ends'] = $toDateTime;
	                $conf['timezone'] = get_interpret_order($con, $order_id, "timezone");
	                $conference_datails = get_conf_body($conf);
	                send_notification("Conference Access Codes for Telephonic Project $order_id", $conference_datails, $_SESSION["email"]);
	                // if client added recipients send emails
	                if (isset($_SESSION["telephonic_conference_recipients"])) {
	                    foreach ($_SESSION["telephonic_conference_recipients"] as $key => $recipient) {
	                        if ($recipient != "") {
	                            send_notification("Conference Access Codes for Telephonic Project $order_id", $conference_datails, $recipient);
	                        }
	                    }
	                    // remove recipients after sending emails
	                    unset($_SESSION["telephonic_conference_recipients"]);
	                }
	            }
	        }
	            //echo $conference_datails . " <br>" .date('Y-m-d H:i',$order['assg_frm_timestamp']) ;  exit();
		        // Ovaj za production site
		        // $to = "lalbescu@alliancebizsolutions.com,alen.brcic@alliancebizsolutions.com,ialbescu@alliancebizsolutions.com";
	        	$to = "slavensakacic@gmail.com";
		        // Ne treba u localhost, RADI TODO UNkomentiraj
		        // Mail::send_notification("Resources for Project ($fromDate / Telephonic)", $interpreter_details, $to);
		        $email = $CustEmail;
		        $email_body = $this->order_onsite_template($order_id, "login");
		        // Ne treba u localhost, RADI TODO UNkomentiraj
	        	// Mail::send_notification("Receipt for Telephonic Interpreter ID $order_id", $email_body, $email);
		        // Send notifications to Admins
		        return $email_body_admin = $this->order_onsite_template($order_id, "admin");

		        // $to = "orders@alliancebizsolutions.com,iorders@alliancebizsolutions.com,support@alliancebizsolutions.com,support2@alliancebizsolutions.com"; // TODO IN PRODUCTION SEND
		        $to = "slavensakacic@gmail";
		        $name = $_SESSION["name"];
		        $from = $name . "<orders@alliancebizsolutions.com>";
		        $reply_to = $email;
		        return Mail::send_notification("ORDER - Telephonic Interpreter (PAID)", $email_body_admin, $to, $from, $reply_to);
		        send_notification("ORDER#$order_id - Telephonic Interpreter (PAID)", $email_body_admin, "goharulzaman@yahoo.com", $from, $reply_to);
	       		 // return true;
	   	 } else {
	        echo "Order not saved";
	        exit();
	    }
	}

	/*
	 calc_interpreter_compensation function is used to calculate compensation for interpreter based on scheduling type

	 * @param $con: Connection to database. Required
	 * @param $order_id: This is order ID to get schedualing type from database. Required
	 * @param $amount: This is Order Total based on what and scheduling type the compensation is calculated. Required
	Usage:
	1: Please press Ctrl+Shift+f
	2: A search Window asks to search specific function. You may search "calc_interpreter_compensation(" without double quotes and with opening parentheses.
	3: choose directory to search within and press "Find" Button
	4: The "Search Results" panel will search and display the pages where this functions has been used in the code.
	*/
	function calc_interpreter_compensation($order_id, $amout) {
		$con = Connect::con();
	     $scheduling_type = OrderOnsiteInterpreter::get_interpret_order($order_id, "scheduling_type");
	    $interpreter_disc = 0;
	    switch ($scheduling_type) {
	        case 'regular': $interpreter_disc = (30 / 100) * $amout;
	            break; // 30% off from regular
	        case 'premium': $interpreter_disc = (29 / 100) * $amout;
	            break; // 29% off from premium
	        case 'platinum':$interpreter_disc = (28 / 100) * $amout;
	            break; // 28% off from platinum
	        case 'conference_call':$interpreter_disc = (25 / 100) * $amout;
	            break; // 25% off from conference call
	        case 'get_call':$interpreter_disc = (25 / 100) * $amout;
	            break; // 25% off from get call
	    }
	    return $interpreter_disc;
	}

	//var_dump(check_lang_pair('14256', '48','68' ));
	// This function returns true if telephonic language pair is approved for an interpreter.
	function check_lang_pair_telephonic($ipid,$lang1,$lang2){
		$con = Connect::con();
		$query="SELECT `IPID`, `PairID`, `Approved` FROM `LangPair` WHERE `PairID`=(SELECT `PairID` FROM `LangRate` WHERE (`L1`='$lang1' AND `L2`='$lang2') OR (`L1`='$lang2' AND `L2`='$lang1')) AND `IPID`='$ipid'";
		$check_q=mysqli_query($con,$query);
		$chck=mysqli_num_rows($check_q);
		if($chck){
			return TRUE;
		}else{
			return FALSE;
		}
	}

	/*
		order_onsite_template function renders order template for transcription orders so that it may be sent to Customer as a Receipt,
		to Linguist as an Order Detail and to Admin.
		@Param  $con: Required to create database connection. Required argument.
		@Param  $order_id: It is the Order Id against what the reports/reciept/template is generated. Required argument.
		@Param  $user_type: Different templates are generated for different users. This argument is provided at the time of calling this function to generate template. The possible users are as a follows.
		admin: to generate order details template for admins
		account: to generate order details template for Customers to view in their portals.
		guest: to generate order details template for customer who checkout as a guest.
		login: to generate order details template for customer who logs in with existing account before checkout.
		register: to generate order details template for customer who newly registers with Alliance Business Solutions before checkout.
		@Param  $view_type: Either editable view or Readable view. Possbile values are as follows
		user: For readable view. It is the default value
		admin: For editable view. when passed this value to displays textfields with actual values in them for admin to update them when required.
		NOTE: The Order details are almost same for all type of users but just changed the look and feel as well as provided different guidence for different users.
		Usage:
		1: Please press Ctrl+Shift+f
		2: A search Window asks to search specific function. You may search "order_onsite_template(" without double quotes and with opening parentheses.
		3: choose directory to search within and press "Find" Button
		4: The "Search Results" panel will search and display the pages where this functions has been used in the code.
	*/
	function order_onsite_template($order_id, $user_type, $view_type = "user") {

		$con = Connect::con();
	    $result = mysqli_query($con, "SELECT * FROM translation_orders WHERE order_id=$order_id");
	    $row = mysqli_fetch_assoc($result);
	    $interpret_order = OrderOnSiteInterpreter::get_interpret_order($order_id, "*");
	    // Check if results are found in translation orders table and onsite_order table
	    if(is_array($row) and is_array($interpret_order)){
		    $bold = "font-weight:bold";
		    $td_style = "style='color:#111;  vertical-align:top; padding:5px 0;  font-size:11px; border-bottom: 1px dotted #ccc;'";
		    $headStyle = "background-color:#f2f2f2; padding:5px 5px; margin:10px 0;";
		    $head_td_style = "style='color: #222; font-weight:bold; font-size:11px;   margin: 5px 0; border-bottom:1px dotted #ccc; padding: 5px 0'; ";
		    $foot_td_style = "style='color: darkgreen; font-weight:bold; font-size:14px;   margin: 5px 0; border-bottom:2px solid #ccc; padding: 5px 0'";
		    $order_type = $row["order_type"];
		    $isAdmin = ($user_type === "admin") ? true : false;
		    $h = ($isAdmin) ? "h3" : "h2";
		    $isTelephonic = false;
		    if ($order_type == 4) {
		        $order = "Onsite";
		    } else if ($order_type == 5) {
		        $order = "Telephonic";
		        $isTelephonic = true;
		    } else {
		        $order = "On-Site";
		    }
	    	$order_detail = TranslationOrders::getTranslationOrder($order_id, "*");
		    // $conference = get_conference($con, $order_id, "*"); // TODO
		    $customer_id = $order_detail["user_id"];
		    $overage_charge = $this->amt_format($interpret_order["overage_charge"]);
		    $overage_status = $interpret_order["overage_status"];
		    $overage_payment_id = $interpret_order["overage_payment_id"];
		    $overage_fee_per_unit = $interpret_order["overage_amt_per_30min"];
		    $time_unit = ($order == "Telephonic") ? "Minute" : "Hour";
		    $is_overage = ($overage_charge > 0 && !is_null($overage_payment_id)) ? true : false;
		    $is_overage_charged = ($overage_status == "PAID") ? true : false;
		    $want_to_pay_overage = (isset($_GET["pay_overage"]) && $_GET["pay_overage"] == "yes") ? true : false;
		    // if ($is_overage) { // TODO pogledat da je dio projects tablice korišten
		    //     $submitted_items = array("intProjectOverTime", "intProjectEndedAt");
		    //     $subt_project = get_submitted_projected($con, $order_id, $submitted_items);
		    //     foreach ($subt_project as $vars => $vals) {
		    //         $$vars = $vals;
		    //     }
		    //     $overage_fee_per_unit = ($isTelephonic) ? $overage_fee_per_unit : 110;
		    //     $intProjectOverTime = ($isTelephonic) ? $intProjectOverTime : ceil($intProjectOverTime / 60);
		    // }
	    	// if viewer is admin then create textfield instead of just text so that admin can update into
		    $name = ($view_type === "admin") ? "<input type='text' value='" . $row["name"] . "' name='client_name' />" : $row["name"];
		    $bname = ($view_type === "admin") ? "<input type='text' value='" . $row["business_name"] . "' name='client_business_name' />" : $row["business_name"];
		    $phone = ($view_type === "admin") ? "<input type='text' value='" . $row["phone"] . "' name='client_phone' />" : $row["phone"];
		    $email = ($view_type === "admin") ? "<input type='text' value='" . $row["email"] . "' name='client_email' />" : $row["email"];
		    $total_units = $row["total_units"];
		    $total_price = $row["total_price"];
		    $stripe_id = $row["stripe_id"];
		    $is_invoiced = ($stripe_id === "invoice") ? true : false;
		    $status = $row["status"];
		    switch ($status) {
		        case "0";
		            $order_status = "<em  style='color:grey'>PENDING</em>";
		            break;
		        case "1";
		            $order_status = "<em  style='color:green'>PAID</em>";
		            break;
		        case "2";
		            $order_status = "<em  style='color:green'>INVOICED</em>";
		            break;
		        case "3";
		            $order_status = "<em  style='color:red'>ABANDONED</em>";
		            break;
		        case "4";
		            $order_status = "<em  style='color:red'>CANCELLED</em>";
		            break;
		        case "5";
		            $order_status = "<em  style='color:red'>DELETED</em>";
		            break;
		        case "6";
		            $order_status = "<em  style='color:red'>CARD DECLINED</em>";
		            break;
		        case "7";
		            $order_status = "<em  style='color:red'>PROJECT SUBMITTED</em>";
		            break;
		    }
		    $hasPaid = ($status === "0" || $status === "1" || $status === "2") ? true : false;
		    $project_location = $interpret_order["service_region"] . " > " . $interpret_order["service_area"];
		    $project_langs = LangList::get_language_name($interpret_order["frm_lang"]) . " <> " . LangList::get_language_name($interpret_order["to_lang"]);
		    $project_timezone = $interpret_order["timezone"];
		    $project_date_from = $interpret_order["assg_frm_date"];
		    $project_date_from .= " " . date('l', strtotime($project_date_from));
		    $project_date_to = $interpret_order["assg_to_date"];
		    $project_date_to .= " " . date('l', strtotime($project_date_to));
		    $project_start_time = date('h:i A', strtotime($interpret_order["assg_frm_st"]));
		    $project_end_time = date('h:i A', strtotime($interpret_order["assg_frm_en"]));
		    $date1 = new \DateTime($project_date_from);
		    $date2 = new \DateTime($project_date_to);
		    $project_days = $date2->diff($date1)->format("%a") + 1;
		    $s = ($project_days > 1) ? "s" : "";
		    $project_amount = $interpret_order["amount"];
		    $project_invoice_id = $interpret_order["invoice_id"];
		    $project_hours = $interpret_order["interpreting_dur"];
		    $project_equipments = $interpret_order["equip_needed"];
		    $headsets_needed = false;
		    if ($project_equipments == 1) {
		        $headsets_needed = true;
		        $project_headsets_needed = $interpret_order["headsets_needed"];
		    }
		    // Get the shipment address for notarized orders
		    $interpret_address = "";
		    $onsite_contact_info = "";
		    $candidate_info = "";
		    // Display Interpreter Name on order detail at client portal if interpreter is assigned
		    if ($user_type === "account") { // NE KORISTI SE
		        // check if the order is assigned to interpreter
		        $interpreter_id = $interpret_order["interpreter_id"];
		        $ip = get_linguist($con, $interpreter_id);
		        if (is_array($ip)) {
		            $ip_name = $ip["Fname"] . " " . $ip["Lname"];
		            $interpret_address.= "<h2 style='$headStyle'>Interpreter Assigned</h2>". "<b>Interpreter Name:</b> $ip_name<br>";
		        }
		    }
		    // Address is not Required/Available for Telephonic Projects
		    if (!$isTelephonic) { // Not Is Telephonic
		        $interpret_address.= "<br><$h style='$headStyle'>Address of Interpreting Session</$h>";
		        $interpret_address.= "<table style='font-size:10px;'>";
		        $interpret_address.="<tr><td style='$bold'>Address: </td><td>" . $interpret_order["street_address"] . "</td></tr>";
		        $interpret_address.="<tr><td> </td><td>" . $interpret_order["address_line_2"] . "</td></tr>";
		        $interpret_address.="<tr><td style='$bold'>City: </td><td> " . $interpret_order["city"] . "</td></tr>";
		        $interpret_address.="<tr><td style='$bold'>State/Provice/Region: </td><td> " . $interpret_order["state"] . "</td></tr>";
		        $interpret_address.="<tr><td style='$bold'>Postal/Zip Code: </td><td> " . $interpret_order["zip"] . "</td></tr>";
		        $interpret_address.="<tr><td style='$bold'>Country: </td><td> " . $interpret_order["country"] . "</td></tr>";
		        $interpret_address.= "</table>";
		    }
		    $overage_info = "";
		    if ($is_overage && $user_type == "account") { // Samo login i admin, bez account
		        // profile view of overage
		        $overage_info.= "<br><$h style='$headStyle'>Project Overage</$h>";
		        $overage_info.= "<p><strong>Project has been completed and went over the scheduled time.</strong><br><br></p>";
		        $overage_info.= "<span style='padding:5px 0;display: inline-block;width:160px;$bold'>Scheduled Ending Time:</span>" . strtoupper($project_end_time) . "<br>";
		        $overage_info.= "<span style='padding:5px 0;display: inline-block;width:160px;$bold'>Actual Ending Time:</span>" . strtoupper($intProjectEndedAt) . "<br>";
		        $overage_info.= "<span style='padding:5px 0;display: inline-block;width:160px;$bold'>Overage Charge Period:</span>$intProjectOverTime {$time_unit}(s)<br>";
		        $overage_info.= "<span style='padding:5px 0;display: inline-block;width:160px;$bold'>Overage Fee:</span>$$overage_fee_per_unit USD per $time_unit<br><br>";
		    }
		    // onsite contact details is not required for telephonic projects
		    $contact_name = ($isTelephonic) ? $order_detail["name"] : $interpret_order["onsite_con_name"];
		    $contact_phone = $interpret_order["onsite_con_phone"];
		    $onsite_contact_info.= "<br><$h style='$headStyle'>$order Contact Detail</$h>";
		    $onsite_contact_info.= "<table style='font-size:10px;'>";
		    $onsite_contact_info.="<tr ><td style='$bold'>Contact Name: </td><td>$contact_name</td></tr>";
		    // Contact Email is not required on telephonic
		    if (!$isTelephonic) {
		        $onsite_contact_info.="<tr><td style='$bold'>Contact Email: </td><td>" . $interpret_order["onsite_con_email"] . "</td></tr>";
		    }
		    if ($interpret_order["scheduling_type"] == "get_call" || !$isTelephonic) {
		        $onsite_contact_info.="<tr><td style='$bold'>Contact Phone#: </td><td>$contact_phone</td></tr>";
		    }
		    $onsite_contact_info.= "</table>";
		    // PSI form fields
		    if ($interpret_order["candidate_name"] != "" && $interpret_order["candidate_confirmation_num"] != "") {
		        $candidate_info.= "<br><$h style='$headStyle'>Candidate Information</$h>";
		        $candidate_info.= "<table style='font-size:10px;'>" . "<tr ><td style='$bold'>Candidate Name: </td><td>" . $interpret_order["candidate_name"] . "</td></tr>". "<tr><td style='$bold'>Candidate Confirmation#: </td><td>" . $interpret_order["candidate_confirmation_num"] . "</td></tr>". "</table>";
		    }
	    	$pro_description = $interpret_order["description"];
		    $description = "";
		    if (trim($pro_description) !== "") {
		        $description = "<br><$h style='$headStyle'>Project Description</$h>$pro_description<br><br>";
		    }
		    $width = ($user_type == 'account') ? "95%" : "500px";
		    $project_detail = "<table border='0px' id='Order_Summary' cellspacing='0' cellpadding='0'    style='background:#fff;  padding:10px; border: 0px; border-radius:10px; border-spacing: 0px; width:$width; '><tr<td $head_td_style width='160px'>Project ID:</td<td $td_style >$order_id</td</tr>";
		    if ($order !== "Telephonic") {
		        // Project Location is not set for telephonic orders
		        $project_detail .= "<tr><td $head_td_style >Project Location:</td><td $td_style >$project_location</td></tr>";
		    }
		    if ($interpret_order["examination_type"] != ""){
		    	$project_detail .= "<tr><td $head_td_style >Examination Type:</td><td $td_style >{$interpret_order['examination_type']}</td></tr>";
		    }
	    	$project_detail .= "<tr><td $head_td_style >Project Languages:</td><td $td_style >$project_langs</td></tr>";
	    	$project_detail .= "<tr><td $head_td_style >Project Start Date:</td><td $td_style >$project_date_from</td></tr><tr><td $head_td_style >Project End Date:</td><td $td_style >$project_date_to</td></tr>";
	    	if ($isTelephonic) {
		        $telephonic_duration = $this->get_assignment_time($project_start_time, $project_end_time);
		        $telephonic_duration = $telephonic_duration['duration'] / 60; // get minutes
		        $project_detail .= "<tr><td $head_td_style >Minutes Scheduled:</td><td $td_style >$telephonic_duration Minutes</td></tr><tr><td $head_td_style >Time Zone:</td><td $td_style >$project_timezone</td></tr>";
	    	} else {
	        	$project_detail .= "<tr><td $head_td_style >Days Scheduled:</td><td $td_style >$project_days Day$s</td></tr><tr><td $head_td_style >Hours Scheduled:</td><td $td_style >$project_hours Hours</td></tr>";
	    	}
	    	$project_detail .= "<tr><td $head_td_style >Scheduled Start Time:</td><td $td_style >$project_start_time</td></tr><tr><td $head_td_style >Scheduled End Time:</td><td $td_style >$project_end_time</td></tr>";
	    	if ($headsets_needed) {
	        	$project_detail .= "<tr><td $head_td_style >Headsets Needed:</td><td $td_style >$project_headsets_needed</td></tr> ";
	    	}
	    	if ($isTelephonic && $interpret_order["scheduling_type"] == "conference_call" && $view_type !== "overage") {
	        	global $client_dial;
	        	$project_detail .= "<tr style='background:lemonchiffon'><td $head_td_style ><span  style='color:red;font-size:14px'>Conference Dial Number:</span></td><td $head_td_style ><span  style='color:red;font-size:14px'>$client_dial</span></td></tr> ";
	        	$project_detail .= "<tr style='background:lemonchiffon'><td $head_td_style ><span  style='color:red;font-size:14px'>Conference Secret Code:</span></td><td $head_td_style ><span  style='color:red;font-size:14px'>{$conference['user_code']}</span></td></tr> ";
	    	}
	    	if ($is_overage && $view_type == "overage") {
		        $project_detail .= "<tr style='background:lemonchiffon'><td $head_td_style ><span  style='color:red;font-size:12px'>Actual Ending Time:</span></td><td $td_style ><span  style='color:red;font-weight:bold'>" . strtoupper($intProjectEndedAt) . "</span></td></tr> ";
		        $project_detail .= "<tr style='background:lemonchiffon'><td $head_td_style ><span  style='color:red;font-size:12px'>Overage Charge Period:</span></td><td $td_style ><span  style='color:red;font-weight:bold'>$intProjectOverTime {$time_unit}(s)</span></td></tr> ";
		        $project_detail .= "<tr style='background:lemonchiffon'><td $head_td_style ><span  style='color:red;font-size:12px'>Overage Fee:</span></td><td $td_style ><span  style='color:red;font-weight:bold'>$$overage_fee_per_unit USD per $time_unit</span></td></tr> ";
		    }
		    $invoice_summary = "<tr><td colspan='2' ><h4 style='background-color:#f2f2f2; color:#333; padding:5px 5px; margin:10px 0;'> Price Summary </h4></td></tr><tr><td style='color:#111; font-weight:bold;  vertical-align:top; padding:5px 0;  font-size:11px; border-bottom: 1px dotted #ccc;'>Subtotal for Invoice ID#$project_invoice_id</td><td style='color:#111; font-weight:bold;  vertical-align:top; padding:5px 0;  font-size:11px; border-bottom: 1px dotted #ccc;'> $" . $this->amt_format($project_amount) . "</td></tr><tr style='background:#FFFFDD;'><td  style='color: darkgreen; font-weight:bold; font-size:14px;   margin: 5px 0; border-bottom:2px solid #ccc; padding: 5px 0'>Total Price</td><td  style='color: darkgreen; font-weight:bold; font-size:14px;   margin: 5px 0; border-bottom:2px solid #ccc; padding: 5px 0'>$" . $this->amt_format($project_amount) . "</td></tr>";
		    $order_price_summary = (isset($project_invoice_id) && $project_invoice_id != "") ? $invoice_summary : $interpret_order["order_summary_html"];
		    if ($is_overage && $view_type == "overage") {
		        $project_detail .= "<tr style='background:#FFFFDD;'><td  $foot_td_style>Overage Subtotal</td><td  $foot_td_style> $" . $this->amt_format($overage_charge) . "</td></tr>";
		    } else {
		        $project_detail .= ($order_price_summary == "") ? "<tr style='background:#FFFFDD;'><td  $foot_td_style>Fee</td><td  $foot_td_style> $" . $this->amt_format($total_price) . "</td></tr>" : $order_price_summary;
		    }
		    $project_detail .= "</table>";
		    $discount = $this->amt_format($row["discount"], 2, ".", "");
		    $body = "<html><head></head><body style='font-family: arial; font-size: 12px; color: #444;'><div>";
		    $addons_price = 0;
		    $addons_price_summary = "";
		    $addons_price_admin = "";
		    $addons_price_summary .= "<tr style='background:#FFFFDD'><td $foot_td_style><div style='float:right; margin-right:20px'>Add-Ons Total</div></td><td $foot_td_style>$$addons_price</td></tr>";
		    $additional_fee = $interpret_order["additional_fee"];
		    if ($additional_fee > 0) {
		        $additional_fee_desc = $interpret_order["additional_fee_desc"];
		        $addons_price_admin.= "<p style='$bold;color:green'>+ $" . $this->amt_format($additional_fee) . " " . $additional_fee_desc . " </p>";
		    }
		    if ($discount > 0) {
		        $addons_price_admin.= "<p style='$bold;color:red'>- $$discount Discount </p>";
		    }
		    // Add an overage price
		    $overage_amt = 0;
		    if ($is_overage) {
		        $overage_amt = $overage_charge;
		        $addons_price_admin.= "<p style='$bold;color:red'>+ $$overage_amt Overage Charges </p>";
		    }
		    if (!$isAdmin) {
		        // If user is a customer, not admin
		        // user_type == account means to display order detail to logged in customer in his portal
		        if ($user_type !== "account") {
		            // this is just for email
		            $body .= "<p>Dear " . ucwords($name) . ",</p><p>Thank you for scheduling a $order Interpreter with Alliance Business Solutions LLC</p>";
		        }
		        if ($is_overage_charged == false && $is_overage) {
		            $body .= "<br>Your project went over by <strong>$intProjectOverTime $time_unit(s)</strong> and requires a additional payment of <strong>$$overage_charge USD</strong>. We have tried to charge your existing mode of payment but were unsuccesfull.<br><br>";
		            if ($want_to_pay_overage == false) {
		                $body .= "<strong>At your earliest convenience please process the additional payment by clicking on the \"Pay Now\" button below<br><br></strong>";
		                $body .= "<a href='" . URL . "orders-script/pay_overage.php?oid=$order_id&pay_overage=yes' target='_blank'><button style='border:0px solid #ccc;padding:10px 50px; background:darkgreen; cursor:pointer; color:white; font-weight:bold; font-size:14px'>Pay Now</button></a>";
		            } else {
		                $body .= "<strong>At your earliest convenience please process the additional payment by using the secure form on left side. Please proceed with Payment at your earliest convenience to avoid any interest and penalty fees.</strong><br><br>";
		            }
		        }
		        // $body .= "<h2 style='$headStyle'>Files Provided</h2>";
		        if ($user_type !== "account") {
		            // this is just for email
		            // $body .= "<p style='$bold'>You have uploaded the following files for translation</p>";
		        } else {
		            $body .= "<br>";
		        }
		    }
		    if ($isAdmin) {
		        $followUp = ($hasPaid) ? "" : "<em style='color:darkblue; $bold'>NOTE: Please call client and follow up via E-mail. </em>";
		        $body .= "<h2>Order#$order_id  Status: $order_status</h2>$followUp";
		        $body .= "<h3 style='$headStyle'>Client Information</h3><table cellspacing='0' cellpadding='0' border='0' style='font-size:11px;'><tr><td width='80px' style='$bold' >Service:</td> <td> Interpretation</td></tr><tr><td style='$bold'>Type:</td> <td> $order</td></tr><tr><td style='$bold'>Name:</td> <td> $name </td></tr><tr><td style='$bold'>Business:</td> <td> $bname </td></tr><tr><td style='$bold'>Email:</td> <td> $email</td></tr><tr><td style='$bold'>Phone:</td> <td> $phone</td></tr></table>";
		        $body .= $overage_info;
		        $body .= $onsite_contact_info;
		        $body .= $candidate_info;
		        $body .= $interpret_address . $description . " <h3 style='$headStyle'>Project Details</h3>" . $project_detail;
		        $body .= "<h3 style='$headStyle'>Order Total</h3>". "<span style='color:green;$bold'>+ $" . ($total_price) . " Subtotal</span> <br>";
		        $body .= $addons_price_admin;
		        $grand_total = ($view_type === "overage") ? $overage_amt : ($total_price + $addons_price + $additional_fee) - $discount;
		        $body .= "<p style='border-top:2px solid #ccc;'></p><span style='color:green;font-size:18px;$bold'>$" . $this->amt_format($grand_total) . " Grand Total</span></br>";
		        $body .= ($view_type === "admin") ? "" : "<h3>Alliance Business Solutions LLC Order System</h3>";
		    }

		    // User Order Detail
		    if (!$isAdmin){
		        $body .= $overage_info . $interpret_address . $onsite_contact_info . $candidate_info . $description;
		        $charge_msg = ($is_invoiced) ? "You will be invoiced at the end of the month for the noted order." : "Your credit card was charged as per the Order Summary below.";
		        // change the charge message if overage
		        if ($is_overage && $view_type === "overage") {
		            $CustLogin = CustLogin::get_customer($con, $customer_id);
		            $type = $CustLogin["Type"];
		            $invoicing_order = ($type == "1" || $type == "4") ? false : true;
		            // If customer is a client and invoicing then use different informations
		            if ($invoicing_order && $customer_id != "0") {
		                $charge_msg = "You will be invoiced accordingly for the overage time utilized, as per the Order Summary below.";
		            } else {
		                $charge_msg = "Your credit card was charged for the overage time utilized, as per the Order Summary below.";
		            }
		        }
		        $oid = ($user_type !== "account") ? "<em style='font-size:11px;color:green;$bold'>Project ID: $order_id</em>" : "";
		        $body .= "<br><h2 style='$bold;$headStyle'>Project Details</h2><br>";
		        if ($user_type !== "account") {
		            $body .= "<p style='font-weight:bold;'>$charge_msg</p>";
		        }
		        $body .= "<div style='border:1px solid #ccc; width:$width; border-radius:2px; padding:30px 10px; background:#F2F2F2'><span style='background:#fff; margin:20px; border-radius:5px 5px 0 0; padding:5px; font-weight:bold; font-size:16px'>Project Summary</span>" . $project_detail;
				// Display addon(s) only when the price of addons is more than 0
		        if ($addons_price > 0) {
		            $body .= "<br><span style='background:#fff; margin:20px; border-radius:5px 5px 0 0; padding:5px; font-weight:bold; font-size:16px'>Add-Ons</span><table width='100%'  cellspacing='0' cellpadding='0' border='0'  style='border-radius:10px; padding:10px; margin-top:0px; background:#fff'  id='addons'><tr class = 'head'><td $head_td_style width = '400px' ><label class = 'bold'>Type</label></td><td $head_td_style width = '50px' ><label class = 'bold'>Cost</label></td></tr>";
		            $body .= $addons_price_summary;
		            $body .= "</table>";
		        }
		        $body .= "<div><br><br><div><table width = '100%'><tbody><tr style = 'font-weight:bold'><td $foot_td_style >Price Summary</td></tr>";
		        // Add an overage price
		        $overage_amt = 0;
		        if ($is_overage && $view_type == "overage") {
		            $overage_amt = $overage_charge;
		            $body .= "<tr style = 'font-weight:bold'><td style = '$bold;font-size:12px;color:red' >+ $" . $overage_amt . " <img src='".getenv('HOME_SECURE')."img/check.gif' width='13px' /> Overage Charges</td></tr>";
		        } else {
		            $body .= "<tr style = 'font-weight:bold'><td style = '$bold;font-size:12px;' >+ $" . $this->amt_format($total_price) . " <img src='".getenv('HOME_SECURE')."img/check.gif' width='13px' /> Subtotal</td></tr>";
		            $additional_fee = $interpret_order["additional_fee"];
		            if ($additional_fee > 0) {
		                $additional_fee_desc = $interpret_order["additional_fee_desc"];
		                $body .= "<tr style = 'font-weight:bold'><td style = '$bold;font-size:12px;' >+ $" . $this->amt_format($additional_fee) . " <img src='".getenv('HOME_SECURE')."img/check.gif' width='13px' /> $additional_fee_desc</td</tr>";
		            }
		            if ($discount > 0) {
		                $body .= "<tr style = 'font-weight:bold'><td style = '$bold;font-size:12px;color:green' >- $" . $this->amt_format($discount) . " <img src='".getenv('HOME_SECURE')."img/check.gif' width='13px' /> Discount</td</tr>";
		            }
		            if ($is_overage && $is_overage_charged) {
		                $overage_amt = $overage_charge;
		                $body .= "<tr style = 'font-weight:bold'><td style = '$bold;font-size:12px;color:red' >+ $" . $overage_amt . " <img src='".getenv('HOME_SECURE')."img/check.gif' width='13px' /> Overage Charges</td</tr>";
		            }
		        }
		        $grand_total = ($is_overage && $view_type == "overage") ? $this->amt_format($overage_amt) : $this->amt_format(($total_price + $addons_price + $additional_fee) - $discount + $overage_amt);
		        //$body .=$addons_price_list;
		        $body .="<tr style = 'font-weight:bold  !important; color:green !important'><td style = '$bold  !important;border-top:1px dashed #000  !important;color:green !important; ". "font-size:14px !important; padding-top:10px  !important' >$" . $grand_total . " Grand total</td></tr>";
		        $body .="</tbody></table></div><!--End of Order Summary -->";
		        if (!$user_type === "account") {
		            $body .= "<div style=' border:2px solid #ccc; background-color:#FFFFD4; margin:10px 0; width:500px; border-radius:10px; padding:0px 10px; '><p  style='$bold;font-size:20px;border-bottom:dotted 1px #ccc; color:darkgreen'><img src='".getenv('HOME_SECURE')."img/admin/warning.gif' alt='' border='0' /> Notice</p> ". "<p style=''>- Scheduled on-site interpreter cannot be cancelled or rescheduled</p>". "<p style=''>- Location of the project is as per receipt and cannot be changed unless within the same 5 mile radius on the same date and time</p>". "<p style=''>- Overage time is billed at the Platinum Rate ($110 per hour) at the beginning of each hour</p>". "</div>";
		        }
				// Check is user is logged in or new register
		        if ($user_type === "login" || $user_type === "register") {
		            $body .="<br><p>You can view your order details by loging into your online portal by clicking at the link below.<br><a style = 'font-weight:bold' href = '".getenv('HOME_SECURE')."linguist/clientportal/loginform.php'>".getenv('HOME_SECURE')."linguist/clientportal/loginform.php</a></p><br>";
		        }
		        $avoid_penality = ($is_overage_charged == false && $want_to_pay_overage == false) ? " Please proceed with Payment at your earliest convenience to avoid any interest and penalty fees." : "";
	        	// Don't display the footer in user portal while displaying order details
	        	if ($user_type == "account") {
	            	$body .=$avoid_penality;
	        	} else {
	            $body .="<p  style='font-weight:bold'>NOTICE:  Please note that all of the on-site and phone interpreting / reading projects are subject to the Alliance Business Solutions Terms and Conditions which can be viewed by clicking on the link:<br><a href='http://www.allianinterpreter.com/en/terms-and-conditions' target='_blank' >http://www.allianinterpreter.com/en/terms-and-conditions</a></p><br></div>";
	        	}
	        	if ($user_type !== "account") {
	            	$body .= $this->get_footer();
	        	}
	    	}
	    	$body .="</body></html>";
	    	return $body;
	    } else{ // order_id was greater than 0 or not equal to "0"
	        // if order_id was zero then do not display email contents
	        return "Order not found.";// TODO return false;
	    }
	}

	// get_footer() function returns the Client Services footer HTML.
	// Mainly used in emails to Clients
	function get_footer() { // TODO make logos/alliancebizsolutions_logo.png work
	    $footer = "<p><span style='font-family: Arial; font-size: 12px; font-style: normal;'><strong>ALLIAN<br /></strong><em>--Client Services</em><strong><br />Email:</strong> cs@".getenv('TRANSLATION_DOMAIN')."<br /><strong>Toll Free:</strong> 1.877.512.1195<br /><strong>International:</strong> 1.615.866.5542<br /><strong>Fax:</strong> 1.615.472.7924<br /></span><a href='".getenv('HOME_SECURE')."' target='_blank'>Translate</a><span style='font-family: Arial; font-size: 12px; font-style: normal;'>. | </span><a href='".getenv('INTERPRETING_HOME_SECURE')."/' target='_blank'>Interpret</a><span style='font-family: Arial; font-size: 12px; font-style: normal;'>. | </span><a href='".getenv('TRANSCRIPTION_HOME_SECURE')."' target='_blank'>Transcribe</a><span style='font-family: Arial; font-size: 12px; font-style: normal;'>.<br /></span><img style='font-family: Arial; font-size: 12px; font-style: normal;' src='".getenv('HOME_SECURE')."logos/alliancebizsolutions_logo.png' alt='' width='150' height='58' /></p>";
	    return $footer;
	}


}