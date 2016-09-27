<?php

namespace Allian\Helpers\TwilioConference;

use Database\DatabaseConnection;

class DatabaseAccess extends DatabaseConnection {

	/**
	 *
	 * Block comment
	 *
	 */
	public function db_insert($query){
		$this->mysqli->query($query);
	    $id=$this->mysqli->insert_id;
		$this->mysqli->close();
		return $id;
	}

	/**
	 *
	 * Can be interpreter or client
	 * just use user_code
	 */
	public function codeused($code){
		$query="SELECT  `user_code`, `interpreter_code` FROM `conference_shedule` WHERE `user_code`=? OR `interpreter_code`= ?";
		$stmt=$this->mysqli->prepare($query);
		$stmt->bind_param('ii',$code,$code);
		$stmt->execute();
		$stmt->store_result();
		$a=$stmt->num_rows;
		$stmt->free_result();
		$stmt->close();
		return $a;
	}

	/**
	 *
	 * Block comment
	 *
	 */
	public function conf_exist($order_id){
		$query="SELECT  `user_code`, `interpreter_code` FROM `conference_shedule` WHERE `orderID`= ?";
		$stmt=$this->mysqli->prepare($query);
		$stmt->bind_param('s',$order_id);
		$stmt->execute();
		$stmt->store_result();
		$a=$stmt->num_rows;
		$stmt->free_result();
		$stmt->close();
		return $a;
	}

	/**
	 *
	 * Block comment
	 *
	 */
	public function oline_calls($code){
		$value=0;
		$query="SELECT `call_sid` FROM `conference_log` WHERE `secret_code`=? AND `is_disconnected`=?";
		$stmt=$this->mysqli->prepare($query);
		$stmt->bind_param('ii',$code,$value);
		$stmt->execute();
		$stmt->store_result();
		$a=$stmt->num_rows;
		$stmt->free_result();
		$stmt->close();
		return $a;
	}

	/**
	 *
	 * Block comment
	 *
	 */
	public function get_conf_data($code){
		$result=array();
		$query="SELECT  `conf_tag`, `user_code`, `interpreter_code`, `start_datetime`, `end_datetime` FROM `conference_shedule` WHERE `user_code`=? OR `interpreter_code`= ?";
		$stmt=$this->mysqli->prepare($query);
		$stmt->bind_param('ii',$code,$code);
		$stmt->execute();
		$stmt->bind_result($conf_tag , $user_code, $interpreter_code, $start_datetime, $end_datetime);
		while ($stmt->fetch()) {
       			$result['conf_tag']=$conf_tag;
       			$result['user_code']=$user_code;
				$result['interpreter_code']=$interpreter_code;
				$result['start_datetime']=$start_datetime;
				$result['end_datetime']=$end_datetime;
			    }
		$stmt->close();
		return $result;
	}

	/**
	 *
	 * Block comment
	 *
	 */
	public function expired_conf($today){
		$result=array();
		$query="SELECT  `user_code`, `interpreter_code` FROM `conference_shedule` WHERE `end_datetime`< '$today'";
		$stmt=$this->mysqli->prepare($query);
		//$stmt->bind_param('ii',$code,$code);
		$stmt->execute();
		$stmt->bind_result($user_code, $interpreter_code);
		$n=0;
		while ($stmt->fetch()) {
       			$result[$n]['user_code']=$user_code;
				$result[$n]['interpreter_code']=$interpreter_code;
				$n++;
			    }
		$stmt->close();
		return $result;
	}

	/**
	 *
	 * Block comment
	 *
	 */
	public function get_conf_bridge($order_id){
		$result=array();
		$query="SELECT `user_code`, `interpreter_code`, `start_datetime`, `end_datetime` FROM `conference_shedule` WHERE `orderID`=?";
		$stmt=$this->mysqli->prepare($query);
		$stmt->bind_param('s',$order_id);
		$stmt->execute();
		$stmt->bind_result($user_code, $interpreter_code, $start_datetime, $end_datetime);
		while ($stmt->fetch()) {
       			$result['user_code']=$user_code;
				$result['interpreter_code']=$interpreter_code;
				$result['start_datetime']=$start_datetime;
				$result['end_datetime']=$end_datetime;
			    }
		$stmt->close();
		return $result;
	}

	/**
	 *
	 * Block comment
	 *
	 */
	public function delete_data($query){
		$this->mysqli->query($query);
	}

}
