<?php
require realpath('.') . '/vendor/autoload.php';
use Allian\Helpers\Mail;

$PairID = $argv[1]; // 73
$real_queue = $argv[2]; //738268

selectIP($PairID, $real_queue);

function selectIP($PairID, $real_queue, $istest=1){ // TODO FOR PRODUCTION $istest = 0
	$host = getenv('DB_HOST');
	$db_username = getenv('DB_USERNAME');
	$db_password = getenv('DB_PASSWORD');
	$db_name = getenv('DB_NAME');
	$con = mysqli_connect("$host", "$db_username", "$db_password", "$db_name");
	$sid = getenv('LIVE_TWILIO_ALLIAN_SID');
	$token = getenv('LIVE_TWILIO_ALLIAN_TOKEN');
	$TimeID = getcurrenthour();
	$query = "SELECT LangPair.IPID FROM LangPair INNER JOIN IPTimings ON LangPair.IPID=IPTimings.IPID INNER JOIN Login ON IPTimings.IPID=Login.IPID WHERE IPTimings.TimeID='$TimeID' AND LangPair.PairID='$PairID' AND LangPair.Approved='1' AND Login.Active='1' AND Login.Telephonic='2' ORDER BY RAND() LIMIT 10";
	$result = mysqli_query($con, $query);
	while($row = mysqli_fetch_array($result)){
		if($Phone = mysqli_fetch_array(mysqli_query($con, "SELECT Phone FROM Login WHERE IPID=" . $row['IPID']))){
			if($Phone['Phone'] == '13024404084' || $Phone['Phone'] == '19175459853' || $Phone['Phone'] == '16153967919'){ // TODO REMOVE IN PRODUCTION
			} else {
				if($istest == 0){
					try{
						$urlCallback = "callRandomHandle?
											PairID=" . $PairID . "&" .
											"real_queue=" . $real_queue . "&" .
											"IPID=" . $row['IPID'];

						$client = new Services_Twilio($sid, $token);
						$call = $client->account->calls->create(
							getenv('TWILIO_CONF_OB_NUMBER'), // BIO $outboundnum = TWILIO_CONF_OB_NUMBER
							"+" . $Phone['Phone'],
							$urlCallback
						);
						mail("slavensakacic@gmail.com", "callRandomHandle.php", "call created with REST API" . getenv('TWILIO_CONF_OB_NUMBER') . " ". $Phone['Phone'] . $urlCallback);
					}catch(\Exception $e){
						 print_r($e);
					}
				}else{
					echo "In call ". $Phone['Phone'] . "<br>";
				}
			}
		}
	}

	// Also send 50 emails
	$result = mysqli_query($con,"SELECT LangPair.IPID FROM LangPair INNER JOIN IPTimings ON LangPair.IPID=IPTimings.IPID INNER JOIN Login ON IPTimings.IPID=Login.IPID WHERE IPTimings.TimeID='$TimeID' AND LangPair.PairID='$PairID' AND LangPair.Approved='1' AND Login.Active='1' AND Login.Telephonic='2' ORDER BY RAND() LIMIT 50");
	while($row = mysqli_fetch_array($result)){
		if($Email = mysqli_fetch_array(mysqli_query($con,"SELECT Email FROM Login WHERE IPID=" . $row['IPID'])))
			$result1 = mysqli_query($con,"SELECT L1,L2,PairID FROM LangRate WHERE PairID='$PairID'");
		while($row1 = mysqli_fetch_array($result1)){
		  	$lang1 = mysqli_fetch_array(mysqli_query($con,"SELECT LangName FROM LangList WHERE LangId=" . $row1['L1']));
			$lang2 = mysqli_fetch_array(mysqli_query($con,"SELECT LangName FROM LangList WHERE LangId=" . $row1['L2']));
			$Pairname = trim($lang1['LangName']) . "-" . trim($lang2['LangName']);

				if($istest == 0){
					mailagents($Email['Email'], $Pairname);
				}else{
					echo "In mail " . $Email['Email'] . " " . $Pairname . "<br>";
					mailagents($Email['Email'], $Pairname);
				}

		}
	}
}

function getcurrenthour(){
	date_default_timezone_set('GMT');
	$daydigit = date("w");
	$dayhour = 24 * $daydigit;
	$hour = date('G');
	$currenthour = $dayhour + $hour;
	$currenthour = $currenthour%168;
	return $currenthour;
}

function mailagents($email, $queuename){
	$time = date("m/d/y G.i:s", time());
	$param = $queuename . "," . $time;
	Mail::sendClientCallWaitingProduction($email, $param);
}

