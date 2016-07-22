<?php
namespace Allian\Helpers\TwilioConference;

use \Dotenv\Dotenv;
use Services_Twilio;
use Services_Twilio_Twiml;
use Services_Twilio_Capability;
use Services_Twilio_TinyHttp;
use Allian\Helpers\TwilioConference\DatabaseAccess;

class ConferenceFunctions{

	/**
	 *
	 * Schedule conference
	 *
	 */
	public function shedule_conference($order_id,$start_datetime,$end_datetime){
		$date = new \DateTime($start_datetime);
		$date->modify('-1 day');
		$start_datetime = $date->format('Y-m-d H:i');
		$date = new \DateTime($end_datetime);
		$date->modify('+1 day');
		$end_datetime = $date->format('Y-m-d H:i');
		$interpreter_code = $this->create_secret_code();
		$user_code = $this->create_secret_code();
		$conf_tag = strval($user_code);
		$conf_tag .= strval($interpreter_code);
		$query="INSERT INTO `conference_shedule`(`orderID`, `conf_tag`, `user_code`, `interpreter_code`, `start_datetime`, `end_datetime`) VALUES ('$order_id','$conf_tag','$user_code','$interpreter_code','$start_datetime','$end_datetime')";
		$db = new DatabaseAccess();
		$id = $db->db_insert($query);
	    $data['conf_id'] = $id;
		$data['user_code'] = $user_code;
		$data['interpreter_code'] = $interpreter_code;
		$data['conf_starts'] = $start_datetime;
		$data['conf_ends'] = $end_datetime;
		return $data;
	}

	/**
	 *
	 * Block comment
	 *
	 */
	public static function generateCapabilityToken($customerID){
		$accountSid = getenv('S_TWILIO_SID');
		$authToken = getenv('S_TWILIO_TOKEN');
		$appSid = getenv('S_TEST_TWILIO_APP_SID');
		$customerID = $customerID;
		$fullname = $name;
		$capability = new Services_Twilio_Capability($accountSid, $authToken);
		$capability->allowClientOutgoing($appSid, array(), $customerID);
		$capability->allowClientIncoming($customerID);
		$token = $capability->generateToken(60*60*24);
		return $token;
	}

	/**
	 *
	 * Block comment
	 *
	 */
	function verify_caller($code){
		$db = new DatabaseAccess();
		if($db->codeused($code)){ // TODO Check only if the number if from client, because only clients will do request
			$conf=$db->get_conf_data($code);
			if($conf['interpreter_code']==$code){ // Uvijek će biti klijent u app-u
				$data['auto_start']="true";
			}else{
				$data['auto_start']="false";
			}
			$end = new \DateTime($conf['end_datetime']);
			$today=new \DateTime(gmdate("Y-m-d H:i"));
			if($today > $end){
				$data['msg']="The conference scheduled is expired.";
			}else {
				$data['conf_tag'] = $conf['conf_tag'];
				$data['auth'] = true;
				$data['msg'] = "Please hold while connecting to conference.";
			}
		}else{
			$data['msg']="The secret code you given is not valid.";
		}
		return $data;
	}

	/**
	 *
	 * Block comment
	 *
	 */
	function set_pre_log($code,$call_sid){
		$connect_datetime = gmdate("Y-m-d H:i:s");
		$db = new DatabaseAccess();
		$query="INSERT INTO `conference_log`(`secret_code`, `call_sid`,`connect_datetime`) VALUES ('$code','$call_sid','$connect_datetime')";
		$id=$db->db_insert($query);
		return $id;
	}

	/**
	 *
	 * Block comment
	 *
	 */
	function set_post_log($call_sid){
		$date_time=gmdate("Y-m-d H:i:s");
		$query="UPDATE `conference_log` SET `hangup_datetime`='$date_time',`is_disconnected`=1 WHERE `call_sid`='$call_sid'";
		$db = new DatabaseAccess();
		$id=$db->db_insert($query);
		return $id;
	}

	/**
	 *
	 * Block comment
	 *
	 */
	function max_call_limit($code,$count){
		$db = new DatabaseAccess();
		$check=$db->oline_calls($code);
		if($check>=$count||$check>=5){
			return 1;
		}
		else {
			return 0;
		}
	}

	/**
	 *
	 * Block comment
	 *
	 */
	function chech_limit($code,$count)
	{
		max_call_limit($code,$count);
	}

	/**
	 *
	 * Block comment
	 *
	 */
	function remove_expired_shedule(){
		$db = new DatabaseAccess();
		$today=gmdate("Y-m-d H:i:s");
		$logkey=$db->expired_conf($today);
		// print_r($logkey);

		foreach ($logkey as $key => $value) {
			foreach ($value as $key => $value) {
			$query="DELETE FROM `conference_log` WHERE `secret_code`='$value'";
			$id=$db->delete_data($query);
			}
		}
		$query="DELETE FROM `conference_shedule` WHERE `end_datetime`< '$today'";
		$id=$db->db_insert($query);
	}
}