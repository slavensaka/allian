<?php

namespace Allian\Http\Controllers;

use \Dotenv\Dotenv;
use Database\Connect;
use Firebase\JWT\JWT; // MAYBE NOT USED
use RNCryptor\Encryptor; // MAYBE NOT USED
use RNCryptor\Decryptor; // MAYBE NOT USED
use Allian\Helpers\Mail;
use Allian\Models\LangList;
use Allian\Models\CustLogin;
use Allian\Helpers\ArrayValues;
use Firebase\JWT\DomainException;
use Firebase\JWT\ExpiredException;
use Allian\Models\TranslationOrders;
use Firebase\JWT\BeforeValidException; // MAYBE NOT USED
use Allian\Models\OrderOnsiteInterpreter;
use Allian\Helpers\Allian\ScheduleFunctions;
use Allian\Http\Controllers\ConferenceController;

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
	public function getTimezones($request, $response, $service, $app){ // FIX
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
	     		return $response->json(array('data' => $this->errorJson("Authentication problems. CustomerID doesn't match that with token.")));
			}

			// TimezonesTop
			$timezonesTop = array();
			foreach (ArrayValues::timezonesTop() as $number => $row){
			    $timezonesTop[] = array($number => $row);
			}
			$for1 = array('timezonesTop' => $timezonesTop);

			// Timezones
			$timezones = array();
			foreach (ArrayValues::timezones() as $dd => $aa){
			    $timezones[] = array($dd => $aa);
			}
			$for2 = array('timezones' => $timezones);

			// LangFrom
			$langFrom = array();
			foreach (ArrayValues::langFrom() as $number => $row){
			    $langFrom[] = array($number => $row);
			}
			$for3 = array('langFrom' => $langFrom);

			// LangTo
			$langTo = array();
			foreach (ArrayValues::langTo() as $number => $row){
			    $langTo[] = array($number => $row);
			}
			$for4 = array('langTo' => $langTo);

			// Countries
			$countries = array();
			foreach (ArrayValues::countries() as $number => $row){
			    $countries[] = array($number => $row);
			}
			$for5 = array('countries' => $countries);

			// // SchedulingType
			// $schedulingType = array();
			// foreach (ArrayValues::schedulingType() as $number => $row){
			//     $schedulingType[] = array($number => $row);
			// }
			// $for6 = array('schedulingType' => $schedulingType);

			// NeededFor
			// $neededFor = array();
			// foreach (ArrayValues::neededFor() as $number => $row){
			//     $neededFor[] = array($number => $row);
			// }
			// $for7 = array('neededFor' => $neededFor);

			// $result = array_merge($for1, $for2, $for3, $for4, $for5, $for6, $for7);
			// return $response->json(array('data' =>$result));

			$schedulingType = array(
			'get_call' => 'Get Interpreters Call',
			'conference_call' => 'Conference Call');
			$type = array('schedulingType' => $schedulingType);

			// Retrieve array values for populating scheduling form
			// $timezonesTop =  ArrayValues::timezonesTop();
			// $timezones =  ArrayValues::timezones();
			// $langFrom = ArrayValues::langFrom();
			// $langTo = ArrayValues::langTo();
			// $countries =  ArrayValues::countries();
			// $schedulingType = ArrayValues::schedulingType();
			$neededFor =  ArrayValues::neededFor();

			// // Merge all arrays into one
			$result = array_merge($for1, $for2, $for3, $for4, $for5, $type, $neededFor);
			return $response->json(array('data' => $result));
			// $result = array_merge($timezonesTop, $timezones, $langFrom, $langTo, $countries, $schedulingType, $neededFor);
			// // Format & return response
	     	return $response->json(array('data' => $result));
	    } else {
	    	return $response->json($this->errorJson("No token provided"));
	    }
	}

	/**
     * @ApiDescription(section="CheckPromoCode", description="Check the validity of the promo code.")
     * @ApiMethod(type="post")
     * @ApiRoute(name="/testgauss/checkPromoCode")
     * @ApiBody(sample="{ 'data': {
	    'CustomerID': '800',
	    'promoCode': 'idCode1',
	    'totalPrice': '20'
  		},
     'token': 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzUxMiJ9.eyJpYXQiOjE0NjUzODMxNjUsImp0aSI6IlJpRW16NzRHSGhGR043QzEzT1JpQ1FuWXRnOHJ4bk9YVHRRZ002NnBDN1E9IiwiaXNzIjoiYWxsaWFudHJhbnNsYXRlLmNvbSIsIm5iZiI6MTQ2NTM4MzE2NSwiZXhwIjoxNDY2NTkyNzY1LCJkYXRhIjp7IlN1Y2Nlc3MiOiJTdWNjZXNzIn19.DvPdwcIGybU3zs5NH4NRmldNbhrer8AgvSSwi9lBY6SwJ-WKegETMRQmXZvtLu5-qrAx5hwBkEKXqG80zTqByw'}")
     * @ApiParams(name="data", type="string", nullable=false, description="CustomerId.")
     * @ApiParams(name="token", type="string", nullable=false, description="Autentication token for users autentication.")
     * @ApiReturnHeaders(sample="HTTP 200 OK")
     * @ApiReturn(type="string", sample="{'data': {
	     'status': 1,
	    'response': '$5',
	    'discount': 5,
	    'type': '$',
	    'userMessage': 'Promotional discount will be applied! The discount is 5$',
	    'totalPrice': 25
	  	}}")
     */
	public function checkPromoCode($request, $response, $service, $app){
		if($request->token){
			// Validate token if not expired, or tampered with
			$this->validateToken($request->token);
			// Decrypt input data
			$data = $this->decryptValues($request->data);
			// Validate input data
			$service->validate($data['CustomerID'], 'Error: No customer id is present.')->notNull()->isInt();
			$service->validate($data['promoCode'], 'Error: Promotional code is not present.')->notNull();
			$service->validate($data['totalPrice'], 'Error: Total price is not present.')->notNull();
			//Validate the jwt token in the database
			$validated = $this->validateTokenInDatabase($request->token, $data['CustomerID']);
			// If error validating token in database
			if(!$validated){
				$base64Encrypted = $this->encryptValues(json_encode($this->errorJson("Authentication problems. CustomerID doesn't match that with token..")));
	     		return $response->json(array('data' => $base64Encrypted));
			}
			$stripe = new StripeController();
			$result = $stripe->promoCode($data['promoCode']);
			$discount = ($result['type'] === "%") ? (($result['discount'] / 100) * $data['totalPrice']) : $result['discount'];
			$resp = array();
			$result['userMessage'] = 'Promotional discount will be applied! The discount is ' . $discount . '$';
			$result['discount'] = $discount;
			$result['totalPrice'] = $data['totalPrice'] - $discount;

			$base64Encrypted = $this->encryptValues(json_encode($result));
			return $response->json(array('data' => $base64Encrypted));

		} else {
	    	$base64Encrypted = $this->encryptValues(json_encode($this->errorJson("No token provided in request")));
     		return $response->json(array('data' => $base64Encrypted));
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
	public function schedulePartOne($request, $response, $service, $app){ // DONT CHANGE
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
				$base64Encrypted = $this->encryptValues(json_encode($this->errorJson("Authentication problems. CustomerID doesn't match that with token..")));
	     		return $response->json(array('data' => $base64Encrypted));
			}
			// Calculate the amount for customer to pay
			$amountArray = ScheduleFunctions::calculateAmountToPay($data);
			// Encrypt the values
			$base64Encrypted = $this->encryptValues(json_encode($amountArray));
			// Return response json
			return $response->json(array('data' => $base64Encrypted));
		} else {
	    	$base64Encrypted = $this->encryptValues(json_encode($this->errorJson("No token provided in request")));
     		return $response->json(array('data' => $base64Encrypted));
	    }
	}

	/**
     * @ApiDescription(section="SchedulePartTwo", description="NOT USED. SKIPPED TO SchedulePartTwo. Retrieve the second part of the payment after user selects or diselects scheduling type.")
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
	public function schedulePartTwo($request, $response, $service, $app){ // DONT CHANGE
		if($request->token){
			// Validate token if not expired, or tampered with
			$this->validateToken($request->token);
			// Decrypt input data
			$data = $this->decryptValues($request->data);
			// Validate input data
			$service->validate($data['CustomerID'], 'Error: No customer id is present.')->notNull()->isInt();
			$service->validate($data['fromDate'], 'Error: from date not present.')->notNull();
			$service->validate($data['timeStarts'], 'Error: time Starts not present.')->notNull();
			$service->validate($data['timeEnds'], 'Error: time ends not present.')->notNull();
			$service->validate($data['schedulingType'], 'Error: from date not present.')->notNull();
			// Validate token in database
			$validated = $this->validateTokenInDatabase($request->token, $data['CustomerID']);
			// If error validating token in database
			if(!$validated){
				$base64Encrypted = $this->encryptValues(json_encode($this->errorJson("Authentication problems. CustomerID doesn't match that with token.")));
	     		return $response->json(array('data' => $base64Encrypted));
			}

			$amountArray = ScheduleFunctions::calculateAmountToPay($data);

			$base64Encrypted = $this->encryptValues(json_encode($amountArray));
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
	    'promoCode': 'idCode1',
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
	public function scheduleFinal($request, $response, $service, $app){ // DONT CHANGE
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
			$service->validate($data['timeEnds'], 'Error: time ends not present.')->notNull();
			$service->validate($data['langFrom'], 'Error: lang from not present.')->notNull();
			$service->validate($data['langTo'], 'Error: lang to present.')->notNull();
			$service->validate($data['country'], 'Error: country not present.');
			//TODO promotionalCode
			$service->validate($data['schedulingType'], 'Error: scheduling type not present.')->notNull();
			$service->validate($data['clients'], 'Error: clients not present.')->notNull();
			$service->validate($data['neededFor'], 'Error: interpreting needed for not present.')->notNull();
			$service->validate($data['description'], 'Error: description not present.')->notNull();
			// $service->validate($data['promoCode'], 'Error: description not present.');
			// Validate token in database with customerID, added security
			$validated = $this->validateTokenInDatabase($request->token, $data['CustomerID']);
			// If error validating token in database
			if(!$validated){
				$base64Encrypted = $this->encryptValues(json_encode($this->errorJson("Authentication problems. CustomerID doesn't match that with token.")));
	     		return $response->json(array('data' => $base64Encrypted));
			}
			//Calculate the amount customer pays based on time specified
			$amountArray = ScheduleFunctions::calculateAmountToPay($data);

			// Assign to default name the total price calculated
			$amount = $amountArray['totalPrice'];
			// Format date with timeStarts with the timezone
			$frmT = new \DateTime($data['fromDate'].' '.$data['timeStarts'],new \DateTimeZone($data['timezone']));
			$frmT->setTimezone(new \DateTimeZone('GMT'));
			// Format date with timeEnds with the timezone
		    $toT = new \DateTime($data['fromDate'].' '.$data['timeEnds'],new \DateTimeZone($data['timezone']));
			$toT->setTimezone(new \DateTimeZone('GMT'));
			// Create array with values to store in the database
			$sArray = array();
			$sArray['customer_id'] = $data['CustomerID'];
			$sArray['assg_frm_date'] = $data['fromDate'];
			$sArray['assg_frm_st'] = date("H:i:s",strtotime($data['timeStarts']));
			$sArray['assg_frm_en'] = date("H:i:s",strtotime($data['timeEnds']));
			$sArray['assg_to_date'] = $data['fromDate'];
			$sArray['timezone'] = $data['timezone'];
			$sArray['assg_frm_timestamp'] =$frmT->format('U');
			$sArray['assg_to_timestamp'] =$toT->format('U');
			$sArray['scheduling_type'] = $data['schedulingType'];
			$sArray['frm_lang'] = LangList::langIdByName($data['langFrom']);
			$sArray['to_lang'] = LangList::langIdByName($data['langTo']);
			$sArray['country'] = $data['country'];
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
			$sArray['amount'] = $amount;

			$sArray['interpreting_dur']= ScheduleFunctions::telephonic_duration($data['fromDate'].'T'.$sArray['assg_frm_st'], $data['fromDate'].'T'.$sArray['assg_frm_en']);
			if ($data['schedulingType'] == 'conference_call') {
				$sArray['onsite_con_phone'] = "";
			} elseif($data['schedulingType'] == 'get_call'){
				$sArray['onsite_con_phone'] = $data['clients'][0];
			}
			if($data['schedulingType'] == 'get_call'){
				$sArray['interpreter_amt'] = (25/100) * $amount;  // caluculate_price() TODO
			} elseif($data['schedulingType'] = 'conference_call'){
				$sArray['interpreter_amt'] = (25/100) * ($amount - 5);
			}
			// Get the customer whos scheduling the session by customer_id
			$customer = CustLogin::getCustomer($sArray['customer_id']);

			// TODO STRIPE DISCOUNT GOES HERE order-scripts/get_disount.php $amount-$dicount
			if(!empty($data['promoCode'])){
				$stripe = new StripeController();
				$result = $stripe->promoCode($data['promoCode']);
				$discount = ($result['type'] === "%") ? (($result['discount'] / 100) * $amount) : $result['discount'];
				$sArray['amount'] = $amount - $discount;
			}

			// Inrst into order_onsite_interpreter values sArray-a
			$onsiteAutoId = OrderOnsiteInterpreter::insertScheduleOrder($sArray);
			if(!$onsiteAutoId){
				$base64Encrypted = $this->encryptValues(json_encode($this->errorJson("Telephonic order not scheduled. Contact Support.")));
				return $response->json(array('data' => $base64Encrypted));
			}
			// Insert into translation_order values
			$orderID = TranslationOrders::insertTransationOrder($sArray, $customer->getValueEncoded('FName'), $customer->getValueEncoded('LName'), $customer->getValueEncoded('Email'), $customer->getValueEncoded('Phone'));
			if(!$orderID){
				$base64Encrypted = $this->encryptValues(json_encode($this->errorJson("Telephonic order not scheduled. Contact Support.")));
				return $response->json(array('data' => $base64Encrypted));
			}
			// Update order_onsite_interpreter to have the same id as in translation_order, Connect them
			$updated = OrderOnsiteInterpreter::updateScheduleOrderID($orderID, $onsiteAutoId);
			if(!$updated){
				$base64Encrypted = $this->encryptValues(json_encode($this->errorJson("Problem with updating order_onsite_interpreter table. Contact Support!")));
				return $response->json(array('data' => $base64Encrypted));
			}
			// Charge the customer an amount
			$stripe = new StripeController();
			$stripe_id = $stripe->chargeCustomer($amount, $customer->getValueEncoded('token'), $customer->getValueEncoded('Email'));
			// If stripe error where charge token is not returned
			if(!$stripe_id){
				$base64Encrypted = $this->encryptValues(json_encode($this->errorJson("Customer stripe charge token not generated.")));
	 			return $response->json(array('data' => $base64Encrypted));
			}
			//Get stripe id
			$dArray['stripe_id'] = $stripe_id;
			$dArray['order_id'] = $orderID;
			// Update translation orders with new info, user paid & did or not had discount
			$complete = TranslationOrders::updateTranslationOrdersSch($dArray);
	    	if(!$complete){
	    		// TODO, charged user but if error make better user message
	    		$errorJson = $this->encryptValues(json_encode($this->errorJson("Problems with updating translation orders.")));
	 			return $response->json(array('data' => $errorJson));
	    	}
	    	// Main method with processing the order after inserting
	    	$user_code = ScheduleFunctions::order_telephonic_notification($orderID, $data['CustomerID'], $customer->getValueEncoded('Email'), $data['clients']);
	    	// If error while main processing failed
	    	if(!$user_code){
	    		$errorJson = $this->encryptValues(json_encode($this->errorJson("Couldn't find the order in our system. Contact support.")));
	 			return $response->json(array('data' => $errorJson));
	    	}
	    	// Create the response array
			$retArray = array();
			$retArray['timezone'] = $data['timezone'];
			$retArray['status'] = 1;
			$retArray['confStarts'] = $data['fromDate'] . ' ' . $data['timeStarts'];
			$retArray['confEnds'] = $data['fromDate'] . ' ' . $data['timeEnds'];
			if($data['schedulingType'] == 'conference_call'){
				$retArray['confCode'] = "$user_code";
			} else if($data['schedulingType'] == 'get_call'){
				$retArray['confCode'] = null;
			}
			// $retArray['confDialNumber'] = getenv('CONF_DIAL_NUMBER_LIVE'); // TODO FOR PRODUCTION
			$retArray['confDialNumber'] = getenv('S_TEST_TWILIO_NO_E_CONF_CALL');
			// Encrypt and return the values
			$base64Encrypted = $this->encryptValues(json_encode($retArray));
	 		return $response->json(array('data' => $base64Encrypted));
		} else {
	    	$base64Encrypted = $this->encryptValues(json_encode($this->errorJson("No token provided in request")));
	 		return $response->json(array('data' => $base64Encrypted));
    	}
	}

}