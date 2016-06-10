<?php

namespace Allian\Http\Controllers;

use \Dotenv\Dotenv;
use Database\Connect;
use Firebase\JWT\JWT;
use RNCryptor\Encryptor;
use RNCryptor\Decryptor;
use Allian\Models\LangList;
use Allian\Helpers\ArrayValues;
use Firebase\JWT\DomainException;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\BeforeValidException;

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
    'totalPrice': '30.00',
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
			$rArray['totalPrice'] = $this->amt_format($amount);
			$rArray['daily'] = "ATS - $scheduling_type Telephonic Scheduling ($$rate_per_min/Min) for $actual_minutes minutes";
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
			$service->validate($data['neededFor'], 'Error: neededFor not present.')->notNull();
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

			$frm_lang = $this->get_language_by_name($data['langFrom']);
			$to_lang = $this->get_language_by_name($data['langTo']);

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
				$sArray['onsite_con_phone'] = $data['contacts'];
			} elseif($data['schedulingType'] == 'get_call'){
				$data['onsite_con_email'] = $data['contacts']; //TODO
			}
			// return $data['contacts'];
			$sArray['intr_needed_for'] = $data['neededFor'];
			$sArray['description'] = $data['description'];
			$sArray['orderID'] = md5(time().$data['description']);
			$sArray['currency_code']='usd';
			$sArray['scheduling_type'] = $data['schedulingType'];
			$all_data_valid = true;
			// if(intval($data['amount'])<=29){ // Amount check if less than 29$ error
			// 	$all_data_valid=false;
			// }
			$sArray['amount'] = $amount; // caluculate_price() TODO
			if($all_data_valid){
				if($data['schedulingType'] == 'get_call'){
					$sArray['interpreter_amt'] = (25/100)*$amount;  // caluculate_price() TODO
				} elseif($data['schedulingType'] = 'conference_call'){
					$sArray['interpreter_amt'] = (25/100)*($amount-5); // caluculate_price() TODO
				}
			}

			$sArray['interpreting_dur']= $this->telephonic_duration(
										$data['fromDate'].'T'.$sArray['assg_frm_st'],
										$data['fromDate'].'T'.$sArray['assg_frm_en']);


			//Mysql insert into order_onsite_interpreterž
			// return $response->json($sArray);
			// $con=mysqli_connect("localhost","root","","allian10_abs_linguist_portal"); // TODO for server
			$con = Connect::con(); // TODO PUT INTO model OrderOnsiteInterpreter
			foreach($sArray as $key=>$value){
				$in[$key] = mysqli_real_escape_string($con,$value);
			}
			$fields = implode(',', array_keys($in));
			$values = implode("', '", array_values($in));
			$query = sprintf("insert into order_onsite_interpreter(%s) values('%s')",$fields,$values);
			// return $query;

			$result = mysqli_query($con,$query);
			if(!$result and mysqli_affected_rows($con)>0){
				if(mysqli_errno($con)==1048){
					$feedback=json_encode("Error--Missing Required Values");
				}else {
					$feedback=json_encode("Error--Failed to Save Data");//.mysqli_error($con);
				}

			}

			// TU


			$retArray = array();
			$retArray['timezone'] = $data['timezone'];
			$retArray['status'] = 1;
			$retArray['confStarts'] = $data['fromDate'] . ' ' . $data['timeStarts'];
			$retArray['confEnds'] = $data['fromDate'] . ' ' . $data['timeEnds'];
			$retArray['confCode'] = "12345";
			$retArray['confDialNumber'] = "+18555129043"; //TODO

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

	function telephonic_duration($frm_time, $to_time) {

	    $start_date = new \DateTime($frm_time);
	    $since_start = $start_date->diff(new \DateTime($to_time));

	    $days = $since_start->d + 1;
	    $minutes += $since_start->h * 60;
	    $minutes += $since_start->i;
	    //echo "<br>".$minutes."<br>";
	    return $minutes * $days;
	}

	function get_language_by_name($langName, $get = 'LangId') {
		// $con=mysqli_connect("localhost","root","","allian10_abs_linguist_portal"); // TODO for server
		$con = Connect::con();
		$query = "SELECT $get FROM `LangList` where LangName LIKE  '%".trim($langName)."%'";
		$get_lang_info = mysqli_query($con, $query);
		$lang = mysqli_fetch_array($get_lang_info);
		$get = $lang[$get];
		return $get;
	}



}