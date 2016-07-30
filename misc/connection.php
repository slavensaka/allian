<?php

/* CONSTANTS */
echo __DIR__ . '<br>';
echo dirname(__FILE__) . '<br>';
echo substr(__DIR__, strlen($_SERVER['DOCUMENT_ROOT'])) . '<br>';
echo $_SERVER['DOCUMENT_ROOT'] . '<br>';

/* PRODUCTION SERVER */
$dbhostProd = 'vps9239.inmotionhosting.com';
$dbuserProd = 'allian10_alenb';
$dbpassProd = 'allian2016@';
$dbnameProd = 'allian10_abs_linguist_portal';

/* STAGING SERVER */
$dbhostStag = 'vps9239.inmotionhosting.com';
$dbuserStag = 'alliantr_gauss';
$dbpassStag = '124L3lSFlM5Ngyk9';
$dbnameStag = 'alliantr_testgauss';

/* DEVELOPMENT SERVER */
$dbhostDev = "localhost";
$dbuserDev = "root";
$dbpassDev = "";
$dbnameDev = "allian10_abs_linguist_portal";


/* MYSQLI CONNECT */
// $con = mysqli_connect("$dbhostProd", "$dbuserProd", "$dbpassProd", "$dbnameProd");
//$query ="SELECT L2 FROM LangRate";
// while($row = mysqli_fetch_array($con, $query)){
	// print_r($row["L2"]);
// }



$payload = json_encode($body);
echo $payload;
/* FORMAT PHONE */
// $formated = preg_replace("/[^0-9]/","", $customer['Phone']);
// $userPhone = $formated;
// if($formated[0] != '+') {
// 	$userPhone = '+' . $formated;
// }


/* FORMAT DATE */
// 1422910800 -> Mon, 02 Feb 2015 21:00:00 GMT
// Pacific/Midway 'Pacific/Midway' => "Midway Island (UTC -11:00) ",
// 10:00:00 + 11:00 => 21:00 TOČNO
//     "fromDate": "2016-06-07",
// "timeStarts": "3:00:00 AM",
// "timezone": "US/Central",
// $frmT = new \DateTime("2016-06-07".' '."3:00:00 AM",new \DateTimeZone("US/Central"));
// print_r( $frmT);
// $frmT->setTimezone(new \DateTimeZone('GMT'));
// echo "<br>";
// print_r( $frmT);