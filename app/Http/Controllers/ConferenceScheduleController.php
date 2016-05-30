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
				$base64Encrypted = $this->encryptValues(json_encode($this->errorJson("Authentication problems present")));
	     		return $response->json(array('data' => $base64Encrypted));
			}
			// Retrieve timezones
			$timezones = include getcwd() . "/resources/assets/timezones.php";
			// Encrypt fomrat json response
			$result = array_merge(array('timezones_top' => $timezones_array_top), array('timezones' => $timezones_array));
			// Encrypt the data
	     	return $response->json(array('data' => $result));
	    } else {
	    	return "No token provided";
	    }
	}

	public function scheduleFirstPart($request, $response, $service, $app){
		//Order Summary
		//Order Summary will be displayed after you choose Scheduling Date/Time
	}

	public function scheduleSecondPart($request, $response, $service, $app){
		//CHOOSE TYPE Adds 5 dollars
	}

	public function scheduleFinal($request, $response, $service, $app){
// 		Conference Detail
// TIMEZONE
// Canada/Atlan)c
// CONFERENCE STARTS
// 2016-03-23 08:00 AM
// CONFERENCE ENDS
// 2016-03-23 09:00 AM
// CONFERENCE CODE
// 29281
// CONFERENCE DIAL NUMBER
// +18555129043
	}



}