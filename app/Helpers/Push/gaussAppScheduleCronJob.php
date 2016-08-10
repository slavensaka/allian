<?php
include $_SERVER['DOCUMENT_ROOT']. "/testgauss/vendor/twilio/sdk/Services/Twilio/Capability.php";
require $_SERVER['DOCUMENT_ROOT']. '/testgauss/vendor/autoload.php';

$dotenv = new Dotenv\Dotenv($_SERVER['DOCUMENT_ROOT'] . '/testgauss');
$dotenv->load();

$server = trim($_SERVER['HTTP_HOST']);
$server=trim($server);

$host = getenv('DB_HOST');
$db_username = getenv("DB_USERNAME");
$db_password = getenv("DB_PASSWORD");
$db_name = getenv("DB_NAME");

if($server == "localhost"){
	$query = "SELECT orderID, scheduling_type, frm_lang, to_lang, customer_id, amount, onsite_con_phone, assg_frm_date, assg_frm_st, timezone FROM `order_onsite_interpreter` WHERE scheduling_type IN ('conference_call', 'get_call') AND push_notification_sent =0 AND is_phone =1 AND DATE_SUB(CONVERT_TZ(DATE_FORMAT(FROM_UNIXTIME(assg_frm_timestamp), '%Y-%c-%d %T:%f'), '+2:00', '-0:00'),INTERVAL 5 MINUTE) BETWEEN CONVERT_TZ(DATE_SUB(NOW(), INTERVAL 5 MINUTE), '+2:00', '-0:00') AND CONVERT_TZ(NOW(), '+2:00', '-0:00')";
} else if($server == "alliantranslate.com"){
	$query = "SELECT orderID, scheduling_type, frm_lang, to_lang, customer_id, amount, onsite_con_phone, assg_frm_date, assg_frm_st, timezone FROM `order_onsite_interpreter` WHERE scheduling_type IN ('conference_call', 'get_call') AND push_notification_sent =0 AND is_phone =1 AND DATE_SUB(CONVERT_TZ(DATE_FORMAT(FROM_UNIXTIME(assg_frm_timestamp), '%Y-%c-%d %T:%f'), '-7:00', '-0:00'),INTERVAL 5 MINUTE) BETWEEN CONVERT_TZ(DATE_SUB(NOW(), INTERVAL 5 MINUTE), '-7:00', '-0:00') AND CONVERT_TZ(NOW(), '-7:00', '-0:00')";
}

$con = mysqli_connect("$host", "$db_username", "$db_password", "$db_name");
$queryResult = mysqli_query($con, $query);
while ($rows = mysqli_fetch_assoc($queryResult)) {

	 $orderID = $rows['orderID'];
    $scheduling_type = $rows["scheduling_type"];

    $frm_lang = get_language_name($con, $rows['frm_lang'], 'LangName');
    $to_lang = get_language_name($con, $rows['to_lang'], 'LangName');
    $CustomerID = $rows['customer_id'];
    $twilioToken = generateCapabilityToken($CustomerID);
    $amount = $rows['amount'];
    $date = $rows['assg_frm_st'] ." ". date('l', strtotime($$rows['assg_frm_date'])) . ' '. $rows['assg_frm_date'] . ' ' . $rows['timezone'];
    if($scheduling_type == 'conference_call'){
    	$message = trim("Your scheduled conference call is about to start in 5 minutes. Translation: $frm_lang <> $to_lang. On date: $date. Cost: $amount$.");
    } elseif($scheduling_type == 'get_call'){
    	$onsite_con_phone = $rows['onsite_con_phone'];
    	$message = trim("Your scheduled interpreter\'s call is about to start in 5 minutes. Translation: $frm_lang <> $to_lang. On date: $date. Cost: $amount$. Call will be to $onsite_con_phone.");
	} else{
		exit();
	}
	mail('slavensakacic@gmail.com',"Cron job fire success!", $message);
    $customer = get_customer($con ,$CustomerID);
    mysqli_query($con, "UPDATE `order_onsite_interpreter` SET push_notification_sent = 1 WHERE orderID = $orderID");
    $deviceToken = (string)$customer['deviceToken'];
	if($deviceToken == null){
		exit();
	}
	// The private key's passphrase
	$passphrase = getenv("PUSH_PASS_PHRASE");
	// Put your alert message here:
	$ctx = stream_context_create();
	// 	stream_context_set_option($ctx, 'ssl', 'local_cert', 'app/Helpers/Push/allianpushcertifikatprod.pem');
	stream_context_set_option($ctx, 'ssl', 'passphrase', $passphrase);
	stream_context_set_option($ctx, 'ssl', 'local_cert', 'allianpushcertfikat.pem');
	stream_context_set_option($ctx, 'ssl', 'verify_peer', false);
	// Open a connection to the APNS server
	$fp = stream_socket_client('ssl://gateway.sandbox.push.apple.com:2195', $err, $errstr, 60, STREAM_CLIENT_CONNECT|STREAM_CLIENT_PERSISTENT, $ctx);
	if(!$fp){
		exit();
	}
	// Create the payload body
	$body = array('aps' => array('alert' => $message, 'sound' => 'default', 'badge' => 4, 'code' => 200 ), 'orderID' => $orderID, 'twilioToken' => $twilioToken);
	// Encode the payload as JSON
	$payload = json_encode($body);
	// // Build the binary notification
	// $msg = chr(0) . pack('n', 32) . pack('H*', str_replace(' ', '', sprintf('%u', CRC32($deviceToken)))) . pack('n', strlen($payload)) . $payload;
	// $msg = chr(0) . pack('n', 32) . pack('H*', $deviceToken) . pack('n', strlen($payload)) . $payload;
	$msg = chr(0) . chr(0) . chr(32) . pack('H*', str_replace(' ', '', $deviceToken)) . chr(0) . chr(strlen($payload)) . $payload;

	// Send it to the server
	$result = fwrite($fp, $msg, strlen($msg));

}

// Close the connection to the server
// socket_close($fp);
// fclose($fp);

function get_language_name($con, $langID, $get = 'LangName') {
    $get_lang_info = mysqli_query($con, "SELECT $get FROM `LangList` where LangId = $langID");
    $lang = mysqli_fetch_array($get_lang_info);
    $get = $lang[$get];
    return $get;
}

function generateCapabilityToken($customerID){
	$accountSid = 'AC50625761130ab2fd390e3d576147601c'; // TODO LIVE TWILIO
	$authToken = 'aed33ccda160f3ca70a3a6ec87ac970b';
	$appSid = 'APf91e7e119ba4d5e6cf46c01ec8d937d2';
	$customerID = $customerID;
	$fullname = $name;
	$capability = new Services_Twilio_Capability($accountSid, $authToken);
	$capability->allowClientOutgoing($appSid, array(), $customerID);
	$capability->allowClientIncoming($customerID);
	$token = $capability->generateToken(60*60*24);
	return $token;
}

function get_customer($con, $cid) {
    $get_cust_info = mysqli_query($con, "SELECT * FROM CustLogin WHERE CustomerID =  '$cid'");
    $cust = mysqli_fetch_array($get_cust_info);
    return $cust;
}