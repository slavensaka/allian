<?php

/* TWILIO PHONE BUILD */
// if($customer['Phone']){
// 	$urlBuild = array("CustomerID" => $data['CustomerID'], "phoneNumber" => $userPhone,
// 					"lang" => $data['lang'], "translationTo" => $data['translationTo']);
// } else {
// 	$urlBuild = array("CustomerID" => $data['CustomerID'], 'phoneNumber' => "+385919249906",
//     				"lang" => $data['lang'], "translationTo" => $data['translationTo']);
// }
// $fallbackUrl = array("CustomerID" => $data['CustomerID'], "lang" => $data['lang'], "translationTo" => $data['translationTo']);
// $url = $urlFirst . 'testgauss/connectOut' . '?' . http_build_query($urlBuild, '', '&');
// $fallbackUrl = $urlFirst . 'testgauss/hotlineFallbackOut' . '?' . http_build_query($fallbackUrl, '', '&');
// if($customer['Phone']){
// 	$call  = $client->account->calls->create('+385919249906', $urlBuild['phoneNumber'], $url,
// 					array('StatusCallback' => $fallbackUrl));
// } else {
// 	$call  = $client->account->calls->create('+385919249906', '+12014642721', $url, array('StatusCallback' => $fallbackUrl));
// }
// return $call;


/* TWILIO CALL */
// $sid = getenv('S_TEST_TWILIO_SID');
// $token = getenv('S_TEST_TWILIO_TOKEN');
// $client = new Services_Twilio($sid, $token);
// $call = $client->account->calls->create("+12014642721", "+385919249906", "http://demo.twilio.com/docs/voice.xml", array());
// echo $call->sid;


/* INCOMING PHONE NUMBERS */
// $client = new Services_Twilio(getenv('S_TEST_TWILIO_SID'), getenv('S_TEST_TWILIO_TOKEN'), $version, $http);
// $number = $client->account->incoming_phone_numbers->create(array( "VoiceUrl" => "http://demo.twilio.com/docs/voice.xml", "PhoneNumber" => "+15005550006" ));
// $sid = getenv('S_TEST_TWILIO_SID');
// $token = getenv('S_TEST_TWILIO_TOKEN');
// $client = new Services_Twilio($sid, $token);