<?php
//DEALING WITH UNIX TIMESTAMPS
// $timestamp=1381920863;
// echo gmdate("Y-m-d\TH:i:s\Z", $timestamp);
// echo date('d M Y H:i:s Z',$timestamp);
// echo date('c',$timestamp);
// echo strtotime('2013-10-16T10:54:23Z');
// CONSTANTS
// echo __DIR__; C:\xampp\htdocs\testgauss
// echo dirname(__FILE__);   C:\xampp\htdocs\testgauss
// define('ROOT_DIR', __DIR__);
// define('ROOT_PATH', substr(ROOT_DIR, strlen($_SERVER['DOCUMENT_ROOT'])));
// echo substr($_SERVER['REQUEST_URI']);
// echo $_SERVER['DOCUMENT_ROOT'];

# Fill our vars and run on cli
# $ php -f db-connect-test.php

// PRODUCTION SERVER
// $dbname = 'allian10_abs_linguist_portal';
// $dbuser = 'allian10_alenb';
// $dbpass = 'allian2016@';
// $dbhost = 'vps9239.inmotionhosting.com';

// STAGING SERVER
// $dbname = 'alliantr_testgauss';
// $dbuser = 'alliantr_gauss';
// $dbpass = '124L3lSFlM5Ngyk9';
// $dbhost = 'vps9239.inmotionhosting.com';

// DEVELOPMENT SERVER
// $dbname="allian10_abs_linguist_portal";
// $dbuser="root";
// $dbpass="";
// $dbhost="localhost";

// // CONNECTION
// $con = mysqli_connect("vps9239.inmotionhosting.com", "alliantr_gauss", "124L3lSFlM5Ngyk9", "alliantr_testgauss");
// $langID = 1;
// $query ="SELECT DISTINCT langpair_trans.Lang2 AS lang2, langlist.LangName AS langName FROM langpair_trans LEFT JOIN langlist ON langpair_trans.Lang2 = langlist.LangId WHERE langpair_trans.Lang1 = '$langID' AND Approved = 1 AND Lang2 IS NOT NULL AND Lang2 <> 'N/A' ORDER BY langlist.LangName";
// while($row = mysqli_fetch_raray($con,$query)){
// 	var_dump($result["langName"]);
// }

// if (CRYPT_BLOWFISH == 1) {
//     echo "Yes";
// } else {
//     echo "No";
// }


// $all_headers = $request->headers()->get('Tester');
		// return $response->json($all_headers);
		// $order_inserted = mysqli_query($con, "INSERT INTO `translation_orders` (`user_id`)". "VALUES('" . 111111 . "')");
		// $order_id = mysqli_insert_id($con); // Vrati od order_id
  		// $response->json($order_id);
		// $all_headers = $request->headers()->get('token');//DIT it
		// $all_headers = $request->headers()->all();//DIT it
		// $all_headers = $request->param('novi');
		// return $response->json($all_headers);
		// $data = $request->data;
		// $dec = json_decode($data);
		// $ar = (array) $dec;
		// if($ar['services']['telephonic_interpreting']){
		// 	return $ar['services']['telephonic_interpreting'];
		// 	$ar['services']['telephonic_interpreting'] = 'Telephonic Interpreting';
		// }
		// if($ar['services'][1]){
		// 	$ar['services'][1] = 'Translation Services';
		// }
		// if($ar['services'][2]){
		// 	$ar['services'][2] = 'On-Site Interpreting';
		// }
		// if($ar['services'][3]){
		// 	$ar['services'][3] = 'Transcription Services';
		// }
		// return $response->json($ar['services']);
		// $services = implode(":", $ar['services']);
		// return $services;

     	// $sid = getenv('S_TWILIO_SID');
		// $token = getenv('S_TWILIO_TOKEN');
		// $version = '2010-04-01';
		// $http = new Services_Twilio_TinyHttp('https://api.twilio.com', array('curlopts' => array(CURLOPT_SSL_VERIFYPEER => false)));
		// $client = new Services_Twilio($sid, $token, $version, $http);
		// $capability = new Services_Twilio_Capability($sid, $token);
		// $appSid = getenv('S_TEST_TWILIO_APP_SID');
		// $capability->allowClientOutgoing($appSid);

		// $formated = preg_replace("/[^0-9]/","", $customer['Phone']);
		// $userPhone = $formated;
		// if($formated[0] != '+') {
		// 	$userPhone = '+' . $formated;
		// }

		// if($customer['Phone']){
		// 	$urlBuild = array(
		//     	"CustomerID" => $data['CustomerID'],
		//     	"phoneNumber" => $userPhone,
		//     	"lang" => $data['lang'],
		//     	"translationTo" => $data['translationTo']
		// 	);
		// } else {
		// 	$urlBuild = array(
		//     	"CustomerID" => $data['CustomerID'],
		//     	'phoneNumber' => "+385919249906", // MOJ BROJ VERIFIED TESTING
		//     	"lang" => $data['lang'],
		//     	"translationTo" => $data['translationTo']
		// 	);
		// }

		// $fallbackUrl = array(
	 //    	"CustomerID" => $data['CustomerID'],
	 //    	"lang" => $data['lang'],
	 //    	"translationTo" => $data['translationTo']
	 //    );

		// $url = $urlFirst . 'testgauss/connectOut' . '?' . http_build_query($urlBuild, '', '&');

		// $fallbackUrl = $urlFirst . 'testgauss/hotlineFallbackOut' . '?' . http_build_query($fallbackUrl, '', '&'); // TODO

		// if($customer['Phone']){
		// 	$call  = $client->account->calls->create( // Moj twilio broj +12014642721
		// 		'+385919249906', $urlBuild['phoneNumber'], $url, array('StatusCallback' => $fallbackUrl));
		// } else {
		// 	$call  = $client->account->calls->create(
		// 		'+385919249906', '+12014642721', $url, array('StatusCallback' => $fallbackUrl));
		// }

		// return $call;


$server = trim($_SERVER['HTTP_HOST']);
$server=trim($server);
echo $server;

 // $server = $this->serverEnv();
		// $urlFirst = 'https://af3d0846.ngrok.io/';
		// if($server=="alliantranslate"){
		// 	$urlFirst = 'https://alliantranslate.com/';
		// }