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
     * @ApiDescription(section="GetTimezones", description="")
     * @ApiMethod(type="post")
     * @ApiRoute(name="/testgauss/getTimezones")
     * @ApiBody(sample="{ 'data': ''}")
     * @ApiParams(name="token", type="object", nullable=false, description="Autentication token for users autentication.")
     * @ApiReturnHeaders(sample="HTTP 200 OK")
     * @ApiReturn(type="object", sample="{'data': ''}")
     */
	public function getTimezones($request, $response, $service, $app){
		if($request->token){

			// Validate token if not expired, or tampered with
			$this->validateToken($request->token);

			$timezones = include getcwd() . "/resources/assets/timezones.php";

	        return $response->json(array('data' => (array( 'timezones_array_top' => $timezones_array_top, 'timezones_array' => $timezones_array))));

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