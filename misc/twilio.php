<?php
// $redir = "Location: https://" . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'];
		// header($redir);
$sid = getenv('S_TEST_TWILIO_SID');
$token = getenv('S_TEST_TWILIO_TOKEN');
$client = new Services_Twilio($sid, $token);

$call = $client->account->calls->create("+12014642721", "+385919249906", "http://demo.twilio.com/docs/voice.xml", array());
echo $call->sid;


// $service->render('./resources/views/test.php');
// $client = new Services_Twilio(getenv('S_TEST_TWILIO_SID'), getenv('S_TEST_TWILIO_TOKEN'), $version, $http);
// $number = $client->account->incoming_phone_numbers->create(array( "VoiceUrl" => "http://demo.twilio.com/docs/voice.xml", "PhoneNumber" => "+15005550006" ));
// $sid = getenv('S_TEST_TWILIO_SID');
// $token = getenv('S_TEST_TWILIO_TOKEN');
// $client = new Services_Twilio($sid, $token);

// $call = $client->account->calls->create("+12014642721", "+385919249906", "http://demo.twilio.com/docs/voice.xml", array());
// echo $call->sid;


// 	if($request->token){
// 		// Validate token if not expired, or tampered with
// 		$this->validateToken($request->token);
// 		// Decrypt data
// 		$data = $this->decryptValues($request->data);
// 		// Validate CustomerId
// 		$service->validate($data['CustomerID'], 'Error: No customer id is present.')->notNull()->isInt();
// 		// Validate token in database for customer stored
// 		$validated = $this->validateTokenInDatabase($request->token, $data['CustomerID']);
// 		// If error validating token in database
// 		if(!$validated){
// 			$base64Encrypted = $this->encryptValues(json_encode($this->errorJson("Authentication problems present")));
//      		return $response->json(array('data' => $base64Encrypted));
// 		}
// } else {
// 	$base64Encrypted = $this->encryptValues(json_encode($this->errorJson("No token provided in request")));
	//return $response->json(array('data' => $base64Encrypted));
// }
