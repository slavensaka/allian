<?php

namespace Allian\Helpers\Allian;

use \Dotenv\Dotenv;
use Database\Connect;
use Allian\Helpers\Mail;
use Allian\Models\LangList;
use Allian\Models\CustLogin;
use Allian\Models\TranslationOrders;
use Allian\Models\ConferenceSchedule;
use Allian\Http\Controllers\Controller;
use Allian\Models\OrderOnsiteInterpreter;
use Allian\Http\Controllers\ConferenceController;

class ConnectNowFunctions extends Controller {

	/**
	 *
	 * Block comment
	 *
	 */
	function addtofile($file, $data){
		// For testing
		return file_put_contents("misc/testReqs/FirstRequestFile" . time(). ".txt", json_encode($data));
	}

	/**
	 *
	 * Block comment
	 *
	 */
	function addCustomerIdType($sid, $data){
		$server = trim($_SERVER['HTTP_HOST']);
		$server=trim($server);
		if($server=="localhost"){
			return file_put_contents("misc/customertype/". $sid . ".txt", json_encode($data));
		} else if($server=="alliantranslate.com"){
			// TODO production
			// return file_put_contents("../linguist/phoneapp/customertype/". $sid . ".txt", json_encode(array('test'=>'test', 'novi'=>'test')));
		} else {
			return file_put_contents("misc/customertype/". $sid . ".txt", json_encode($data));
		}
	}

	function removeCustomerIdType($sid){
		$server = trim($_SERVER['HTTP_HOST']);
		$server=trim($server);
		if($server=="localhost"){
			$sidfile="misc/customertype/" . $sid . ".txt";
			if(file_exists($sidfile)){
				unlink($sidfile);
			}
		} else if($server=="alliantranslate.com"){
			$sidfile="../linguist/phoneapp/customertype/" . $sid . ".txt";
			if(file_exists($sidfile)){
				unlink($sidfile);
			}
		} else {
			$sidfile="misc/customertype/" . $sid . ".txt";
			if(file_exists($sidfile)){
				unlink($sidfile);
			}
		}
	}

	/**
	 *
	 * Block comment
	 *
	 */
	function addtofilePrepayment($file, $data){
		// For testing
		return file_put_contents("misc/testReqs/Prepayment" . time(). ".txt", json_encode($data));
	}

	/**
	 *
	 * Block comment
	 *
	 */
	function addtofilePairQueue($file, $data){
		// For testing
		return file_put_contents("misc/testReqs/PairIDQueue" . time(). ".txt", json_encode($data));
	}

	/**
	 *
	 * Block comment
	 *
	 */
	function getfromfile($file){
		$abc=file_get_contents("testing_files/" . $file . ".txt");
		$abc=json_decode($abc, TRUE);
		return $abc;
	}

	/**
	 *
	 * Used to send email notification to staff and admins
	 *
	 */
	function sendstaffmail($text, $param) {
	    $con = Connect::con();
	    $ip="Project Desk - Alliance Business Solutions <projects@alliancebizsolutions.com>";
	    $orders="HR - Alliance Business Solutions <orders@alliancebizsolutions.com>";
		$client="Client Services - Alliance Business Solutions <cs@alliantranslate.com>" ;
		$staff = "slavensakacic@gmail.com"; //TODO $staff="alen.brcic@alliancebizsolutions.com";
		$callfailed1="slavensakacic@gmail.com"; //$callfailed1 = "orders@alliancebizsolutions.com";
	    $link = "";
	    $link2 = "";
	    $number = "";
	    $pair = "";
	    $queueresult = "";
	    $time = "";
	    $email = "";
	    $mailtype = $text;
	    $notify_at_orders = false;
	    switch ($text) {
	        case "staff_onetimecallfailed":
	            $arr = explode(",", $param);
	            $number = $arr[0];
	            $pair = $arr[1];
	            $queueresult = $arr[2];
	            $time = $arr[3];
	            break;
	        case "staff_regcallfailed":
	            $arr = explode(",", $param);
	            $number = $arr[0];
	            $pair = $arr[1];
	            $queueresult = $arr[2];
	            $time = $arr[3];
	            $email = $arr[4];
	            break;
	        default:
	            $link = "";
	            break;
	    }
	    $text = __DIR__ . '/' .  "emailTexts/" . $text . ".txt";
	    $file = file($text);
	    $subject = trim($file[0]);
	    array_shift($file);
	    $file = implode("", $file);
	    $file = str_replace("[NUMBER]", $number, $file);
	    $file = str_replace("[LANGPAIR]", $pair, $file);
	    $file = str_replace("[REASON]", $queueresult, $file);
	    $file = str_replace("[DATETIME]", $time, $file);
	    $headers = "From:" . $staff;
	    $email = ($notify_at_orders) ? $orders : $ip;
	    if ($mailtype == "staff_onetimecallfailed" || $mailtype == "staff_regcallfailed") {
	    	$server = $this->serverEnv();
			if($server=="alliantranslate.com"){
				mail($callfailed1, $subject, $file, $headers);
			} else if($server == 'localhost'){
				mail($callfailed1, $subject, $file, $headers);
			}
	    }
	}

}