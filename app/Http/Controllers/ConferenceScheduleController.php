<?php

namespace Allian\Http\Controllers;

use Firebase\JWT\JWT;
use \Dotenv\Dotenv;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\DomainException;
use Firebase\JWT\BeforeValidException;
use RNCryptor\Encryptor;
use RNCryptor\Decryptor;

class ConferenceScheduleController extends Controller {

	/**
     * @ApiDescription(section="GetTimezones", description="Retrieve json of top timezones and other timezones")
     * @ApiMethod(type="post")
     * @ApiRoute(name="/testgauss/getTimezones")
     * @ApiBody(sample="{ 'data': 'AwGsq1rYpTw4g6yAX/P7mkrAoKWLlnkxcAQUlNqeV1dyqztE1M4OiLEsM62DaKYeSBCyHilqoynA8MPx2St6jk+fioyzDMm6JZJ9DvECc4MIQpB7NYzK201LUoKl0Rhp7QY=',
     'token': 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzUxMiJ9.eyJpYXQiOjE0NjQ1OTMzNjcsImp0aSI6IlJoOGpiMVhUZHFvUDVDVUVSQ29VY3pWR0dnSVFsQWJ1bFwvRFp1U2pcL050OD0iLCJpc3MiOiJsb2NhbGhvc3QiLCJuYmYiOjE0NjQ1OTMzNjcsImV4cCI6MTQ2NTgwMjk2NywiZGF0YSI6eyJTdWNjZXNzIjoiU3VjY2VzcyJ9fQ.JDwNdycstmqNC0dyrNgNuik_zXCYbx3PwbIkdTX7is3oDrQr6CKQ6mREUt-9tbOys361mcH1kyXaahn9Y2tTRg'}")
     * @ApiParams(name="data", type="object", nullable=false, description="CustomerId.")
     * @ApiParams(name="token", type="object", nullable=false, description="Autentication token for users autentication.")
     * @ApiReturnHeaders(sample="HTTP 200 OK")
     * @ApiReturn(type="object", sample="{'data': ''}")
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
				// $base64Encrypted = $this->encryptValues(json_encode($this->errorJson("Authentication problems present")));
	     		return $response->json(array('data' => $this->errorJson("Authentication problems present")));
			}
			// Retrieve timezones
			// $timezones = include getcwd() . "/app/Http/Controllers/timezones.php";
			$timezones_array_top = array(
				'US/Pacific' => "Pacific Time (UTC -8:00)",
				'US/Mountain' => "Mountain Time (UTC -7:00)",
				'US/Central' => "Central Time (UTC -6:00)",
				'US/Eastern' => "Eastern Time (UTC -5:00)",
				'US/Hawaii' => "Hawaii­Aleutian Time (UTC -10:00)",
				'US/Alaska' => "Alaska Time (UTC -9:00)",
				'Canada/Atlantic' => "Atlantic Time (Canada)  (UTC -4:00)",
				'Greenland' => "West Greenland Time (UTC -3:00)",
				'Atlantic/Stanley' => "Falkland Islands (UTC -2:00) ",
				'Atlantic/Cape_Verde' => "East Greenland Time (UTC -1:00)",
				'Europe/London' => "Western European Time (UTC)",
				'Europe/Berlin' => "Central European Time (UTC +1:00)",
				'Europe/Athens' => "Eastern European Time (UTC +2:00)",
				'Europe/Moscow' => "Moscow Time (UTC +3:00)",
				'Asia/Tehran' => "Iran Time (UTC +3:30)",
				'Asia/Yerevan' => "Russian Samara time (UTC +4:00)",
				'Asia/Tashkent' => "Russia Yekaterinburg Time (UTC +5:00)",
				'Asia/Dhaka' => "Bangladesh (UTC +6:00)",
				'Asia/Bangkok' => "Thailand Time (UTC +7:00)",
				'Asia/Ulaanbaatar' => "Mongolia Time (UTC +8:00)",
				'Asia/Seoul' => "South Korea Time (UTC +9:00)",
				'Australia/Sydney' => "Australia (UTC +10:00)",
				'Pacific/Auckland' => "New Zeland (UTC +12:00)",
			);

			$timezones_array = array(
			    'Pacific/Midway' => "Midway Island (UTC -11:00) ",
			    'US/Samoa' => "Samoa (UTC -11:00)",
			    'US/Hawaii' => "Hawaii (UTC -10:00)",
			    'US/Alaska' => "Alaska (UTC -09:00) ",
			    'US/Pacific' => "Pacific Time (US &amp; Canada) (UTC -08:00)",
			    'America/Tijuana' => "Tijuana (UTC -08:00)",
			    'US/Arizona' => "Arizona (UTC -07:00)",
			    'US/Mountain' => "Mountain Time (US &amp; Canada) (UTC -07:00)",
			    'America/Chihuahua' => "Chihuahua (UTC -07:00)",
			    'America/Mazatlan' => "Mazatlan (UTC -07:00)",
			    'America/Mexico_City' => "Mexico City (UTC -06:00)",
			    'America/Monterrey' => "Monterrey (UTC -06:00)",
			    'Canada/Saskatchewan' => "Saskatchewan (UTC -06:00)",
			    'US/Central' => "Central Time (US &amp; Canada) (UTC -06:00)",
			    'US/Eastern' => "Eastern Time (US &amp; Canada) (UTC -05:00)",
			    'US/East-Indiana' => "Indiana (East) (UTC -05:00)",
			    'America/Bogota' => "Bogota (UTC -05:00)",
			    'America/Lima' => "Lima (UTC -05:00)",
			    'America/Caracas' => "Caracas (UTC -04:30)",
			    'Canada/Atlantic' => "Atlantic Time (Canada) (UTC -04:00)",
			    'America/La_Paz' => "La Paz (UTC -04:00)",
			    'America/Santiago' => "Santiago (UTC -04:00)",
			    'Canada/Newfoundland' => "Newfoundland (UTC -03:30)",
			    'America/Buenos_Aires' => "Buenos Aires (UTC -03:00)",
			    'Greenland' => "Greenland (UTC -03:00)",
			    'Atlantic/Stanley' => "Stanley (UTC -02:00)",
			    'Atlantic/Azores' => "Azores (UTC -01:00)",
			    'Atlantic/Cape_Verde' => "Cape Verde Is. (UTC -01:00)",
			    'Africa/Casablanca' => "Casablanca (UTC )",
			    'Europe/Dublin' => "Dublin (UTC )",
			    'Europe/Lisbon' => "Lisbon (UTC )",
			    'Europe/London' => "London (UTC )",
			    'Africa/Monrovia' => "Monrovia (UTC )",
			    'Europe/Amsterdam' => "Amsterdam (UTC +01:00)",
			    'Europe/Belgrade' => "Belgrade (UTC +01:00)",
			    'Europe/Berlin' => "Berlin (UTC +01:00)",
			    'Europe/Bratislava' => "Bratislava (UTC +01:00)",
			    'Europe/Brussels' => "Brussels (UTC +01:00)",
			    'Europe/Budapest' => "Budapest (UTC +01:00)",
			    'Europe/Copenhagen' => "Copenhagen (UTC +01:00)",
			    'Europe/Ljubljana' => "Ljubljana (UTC +01:00)",
			    'Europe/Madrid' => "Madrid (UTC +01:00)",
			    'Europe/Paris' => "Paris (UTC +01:00)",
			    'Europe/Prague' => "Prague (UTC +01:00)",
			    'Europe/Rome' => "Rome (UTC +01:00)",
			    'Europe/Sarajevo' => "Sarajevo (UTC +01:00)",
			    'Europe/Skopje' => "Skopje (UTC +01:00)",
			    'Europe/Stockholm' => "Stockholm (UTC +01:00)",
			    'Europe/Vienna' => "Vienna (UTC +01:00)",
			    'Europe/Warsaw' => "Warsaw (UTC +01:00)",
			    'Europe/Zagreb' => "Zagreb (UTC +01:00)",
			    'Europe/Athens' => "Athens (UTC +02:00)",
			    'Europe/Bucharest' => "Bucharest (UTC +02:00)",
			    'Africa/Cairo' => "Cairo (UTC +02:00)",
			    'Africa/Harare' => "Harare (UTC +02:00)",
			    'Europe/Helsinki' => "Helsinki (UTC +02:00)",
			    'Europe/Istanbul' => "Istanbul (UTC +02:00)",
			    'Asia/Jerusalem' => "Jerusalem (UTC +02:00)",
			    'Europe/Kiev' => "Kyiv (UTC +02:00)",
			    'Europe/Minsk' => "Minsk (UTC +02:00)",
			    'Europe/Riga' => "Riga (UTC +02:00)",
			    'Europe/Sofia' => "Sofia (UTC +02:00)",
			    'Europe/Tallinn' => "Tallinn (UTC +02:00)",
			    'Europe/Vilnius' => "Vilnius (UTC +02:00)",
			    'Asia/Baghdad' => "Baghdad (UTC +03:00)",
			    'Asia/Kuwait' => "Kuwait (UTC +03:00)",
			    'Africa/Nairobi' => "Nairobi (UTC +03:00)",
			    'Asia/Riyadh' => "Riyadh (UTC +03:00)",
			    'Asia/Tehran' => "Tehran (UTC +03:30)",
			    'Europe/Moscow' => "Moscow (UTC +04:00)",
			    'Asia/Baku' => "Baku (UTC +04:00)",
			    'Europe/Volgograd' => "Volgograd (UTC +04:00)",
			    'Asia/Muscat' => "Muscat (UTC +04:00)",
			    'Asia/Tbilisi' => "Tbilisi (UTC +04:00)",
			    'Asia/Yerevan' => "Yerevan (UTC +04:00)",
			    'Asia/Kabul' => "Kabul (UTC +04:30)",
			    'Asia/Karachi' => "Karachi (UTC +05:00)",
			    'Asia/Tashkent' => "Tashkent (UTC +05:00)",
			    'Asia/Kolkata' => "Kolkata (UTC +05:30)",
			    'Asia/Kathmandu' => "Kathmandu (UTC +05:45)",
			    'Asia/Yekaterinburg' => "Ekaterinburg (UTC +06:00)",
			    'Asia/Almaty' => "Almaty (UTC +06:00)",
			    'Asia/Dhaka' => "Dhaka (UTC +06:00)",
			    'Asia/Novosibirsk' => "Novosibirsk (UTC +07:00)",
			    'Asia/Bangkok' => "Bangkok (UTC +07:00)",
			    'Asia/Jakarta' => "Jakarta (UTC +07:00)",
			    'Asia/Krasnoyarsk' => "Krasnoyarsk (UTC +08:00)",
			    'Asia/Chongqing' => "Chongqing (UTC +08:00)",
			    'Asia/Hong_Kong' => "Hong Kong (UTC +08:00)",
			    'Asia/Kuala_Lumpur' => "Kuala Lumpur (UTC +08:00)",
			    'Australia/Perth' => "Perth (UTC +08:00)",
			    'Asia/Singapore' => "Singapore (UTC +08:00)",
			    'Asia/Taipei' => "Taipei (UTC +08:00)",
			    'Asia/Ulaanbaatar' => "Ulaan Bataar (UTC +08:00)",
			    'Asia/Urumqi' => "Urumqi (UTC +08:00)",
			    'Asia/Irkutsk' => "Irkutsk (UTC +09:00)",
			    'Asia/Seoul' => "Seoul (UTC +09:00)",
			    'Asia/Tokyo' => "Tokyo (UTC +09:00)",
			    'Australia/Adelaide' => "Adelaide (UTC +09:30)",
			    'Australia/Darwin' => "Darwin (UTC +09:30)",
			    'Asia/Yakutsk' => "Yakutsk (UTC +10:00)",
			    'Australia/Brisbane' => "Brisbane (UTC +10:00)",
			    'Australia/Canberra' => "Canberra (UTC +10:00)",
			    'Pacific/Guam' => "Guam (UTC +10:00)",
			    'Australia/Hobart' => "Hobart (UTC +10:00)",
			    'Australia/Melbourne' => "Melbourne (UTC +10:00)",
			    'Pacific/Port_Moresby' => "Port Moresby (UTC +10:00)",
			    'Australia/Sydney' => "Sydney (UTC +10:00)",
			    'Asia/Vladivostok' => "Vladivostok (UTC +11:00)",
			    'Asia/Magadan' => "Magadan (UTC +12:00) ",
			    'Pacific/Auckland' => "Auckland (UTC +12:00) ",
			    'Pacific/Fiji' => "Fiji (UTC +12:00)",
			);

			// Encrypt fomrat json response
			$result = array_merge(array('timezones_top' => $timezones_array_top), array('timezones' => $timezones_array));
			// Encrypt the data
	     	return $response->json(array('data' => $result));
	    } else {
	    	return $response->json("No token provided. TODO. Encrypt this");
	    }
	}
	/**
     * @ApiDescription(section="SchedulePartOne", description="Retrieve the first part of the payment after user selects end time.")
     * @ApiMethod(type="post")
     * @ApiRoute(name="/testgauss/schedulePartOne")
     * @ApiBody(sample="{ 'data': 'AwGsq1rYpTw4g6yAX/P7mkrAoKWLlnkxcAQUlNqeV1dyqztE1M4OiLEsM62DaKYeSBCyHilqoynA8MPx2St6jk+fioyzDMm6JZJ9DvECc4MIQpB7NYzK201LUoKl0Rhp7QY=',
     'token': 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzUxMiJ9.eyJpYXQiOjE0NjQ1OTMzNjcsImp0aSI6IlJoOGpiMVhUZHFvUDVDVUVSQ29VY3pWR0dnSVFsQWJ1bFwvRFp1U2pcL050OD0iLCJpc3MiOiJsb2NhbGhvc3QiLCJuYmYiOjE0NjQ1OTMzNjcsImV4cCI6MTQ2NTgwMjk2NywiZGF0YSI6eyJTdWNjZXNzIjoiU3VjY2VzcyJ9fQ.JDwNdycstmqNC0dyrNgNuik_zXCYbx3PwbIkdTX7is3oDrQr6CKQ6mREUt-9tbOys361mcH1kyXaahn9Y2tTRg'}")
     * @ApiParams(name="data", type="object", nullable=false, description="CustomerId.")
     * @ApiParams(name="token", type="object", nullable=false, description="Autentication token for users autentication.")
     * @ApiReturnHeaders(sample="HTTP 200 OK")
     * @ApiReturn(type="object", sample="{'data': ''}")
     */
	public function schedulePartOne($request, $response, $service, $app){
		if($request->token){
			//Order Summary will be displayed after you choose Scheduling Date/Time
			// $type = 'conference_call'; // conference_call or get_call
			// $fromDate = "2016-06-07";
			// $timeStarts = "2016-06-07 12:50:00 AM";
			// $timeEnds = "2016-06-07 12:55:00 PM";
			// $timezone = "Pacific Time (UTC -8:00)";

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
			//Validate the jwt token in the database
			$validated = $this->validateTokenInDatabase($request->token, $data['CustomerID']);
			// If error validating token in database
			if(!$validated){
				$base64Encrypted = $this->encryptValues(json_encode($this->errorJson("Authentication problems present")));
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

			// $details['daily'][] = "ATS - $scheduling_type Telephonic Scheduling ($$rate_per_min/Min) for $actual_minutes minutes$minimum_text::$$amount";

			// if ($type == 'conference_call') {
			//     $amount+= $conference_fee;
			//     $details['daily'][] = "Conference Calling Fee:: $$conference_fee";
			// }
			$rArray = array();
			$rArray['totalPrice'] = $this->amt_format($amount);
			$rArray['daily'] = "ATS - $scheduling_type Telephonic Scheduling ($$rate_per_min/Min) for $actual_minutes minutes";
			if($minimum_text){
				$rArray['minimumText'] = $minimum_text ;
			} else {
				$rArray['minimum_text'] = null;
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
	 *
	 * Block comment
	 *
	 */
	public function schedulePartTwo($request, $response, $service, $app){
		//CHOOSE TYPE Adds 5 dollars
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
			// $service->validate($data['timezone'], 'Error: timezone not present.')->notNull();
			$service->validate($data['schedulingType'], 'Error: from date not present.')->notNull();

			$validated = $this->validateTokenInDatabase($request->token, $data['CustomerID']);
			// If error validating token in database
			if(!$validated){
				$base64Encrypted = $this->encryptValues(json_encode($this->errorJson("Authentication problems present")));
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
				$rArray['minimum_text'] = null;
			}


			if ($data['schedulingType'] == 'conference_call') {
			    $amount+= $conference_fee;
			    $rArray['conferencePresent'] = "Conference Calling Fee:: $$conference_fee";
			} else{
				$rArray['conferencePresent'] = null;
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
	 *
	 * Block comment
	 *
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
				$base64Encrypted = $this->encryptValues(json_encode($this->errorJson("Authentication problems present")));
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
			$sArray['frm_lang'] = $data['langFrom']; // broj languagea
			$sArray['to_lang'] = $data['langTo']; // broj Languagea
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
										$data['fromDate']. 'T'.$data['assg_frm_en']);
			//Mysql insert into order_onsite_interpreterž
			// return $response->json($sArray);
			$con=mysqli_connect("localhost","root","","allian10_abs_linguist_portal");
			foreach($sArray as $key=>$value){
				$in[$key] = mysqli_real_escape_string($con,$value);
			}
			$fields = implode(',', array_keys($in));
			$values = implode("', '", array_values($in));
			$query = sprintf("insert into order_onsite_interpreter(%s) values('%s')",$fields,$values);
			// return $query;



			$result = mysqli_query($con,$query);
			if($result and mysqli_affected_rows($con)>0){
				$feedback = json_encode("true");
			}else {
				if(mysqli_errno($con)==1048){
					$feedback=json_encode("Error--Missing Required Values");
				}else {
					$feedback=json_encode("Error--Failed to Save Data");//.mysqli_error($con);
				}
			}
			$retArray = array();
			$retArray['timezone']
			return $feedback;
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




}