<?php

namespace Allian\Models;

use Database\DataObject;
use Database\Connect;

class OrderOnsiteInterpreter extends DataObject {

	protected $data = array(
	    "autoID" => "",
	    "orderID" => "",
	    "customer_id" => "",
	    "invoice_id" => "",
	    "timezone" => "",
	    "assg_frm_date" => "",
	    "assg_frm_st" => "",
	    "assg_frm_en" => "",
	    "assg_to_date" => "",
	    "assg_frm_timestamp" => "",
	    "assg_to_timestamp" => "",
	    "assg_to_st" => "",
	    "assg_to_en" => "",
	    "interpreting_dur" => "",
	    "scheduling_type" => "",
	    "frm_lang" => "",
	    "to_lang" => "",
	    "service_region" => "",
	    "service_area" => "",
	    "dist_frm_reg_srvc_area" => "",
	    "street_address" => "",
	    "address_line_2" => "",
	    "city" => "",
	    "state" => "",
	    "zip" => "",
	    "country" => "",
	    "onsite_con_name" => "",
	    "onsite_con_phone" => "",
	    "onsite_con_email" => "",
	    "candidate_name" => "",
	    "candidate_confirmation_num" => "",
	    "interpreting_type" => "",
	    "equip_needed" => "",
	    "headsets_needed" => "",
	    "intr_needed_for" => "",
	    "description" => "",
	    "t_and_c" => "",
	    "status" => "",
	    "amount" => "",
	    "order_summary_html" => "",
	    "additional_fee" => "",
	    "additional_fee_desc" => "",
	    "overage_charge" => "",
	    "overage_status" => "",
	    "overage_payment_id" => "",
	    "currency_code" => "",
	    "pay_transaction_id" => "",
	    "interpretation_status" => "",
	    "time_taken_complete" => "",
	    "interpreter_id" => "",
	    "interpreter_amt" => "",
	    "overage_amt_per_30min" => "",
	    "overage_interpreter_amt" => "",
	    "ip_note" => "",
	    "admin_note" => "",
	    "notification_24" => "",
	    "notification_day_off" => "",
	    "notification_completion" => "",
	    "notification_checkout" => "",
	    "telephonic_linguists_cj" => "",
	    "mail_sent_to_telephonic_linguists" => "",
	    "deleted" => "",
	    "project_submitted" => "",
	);

	public static function insertScheduleOrder($sArray){
		$con = Connect::con();
		foreach($sArray as $key=>$value){
			$in[$key] = mysqli_real_escape_string($con,$value);
		}
		$fields = implode(',', array_keys($in));
		$values = implode("', '", array_values($in));
		$query = sprintf("insert into order_onsite_interpreter(%s) values('%s')",$fields,$values);
		$result = mysqli_query($con,$query);
		if($result){
			return mysqli_insert_id($con);
		}
		if(!$result and mysqli_affected_rows($con)>0){
			if(mysqli_errno($con) == 1048){
				return "Error--Missing Required Values";
			}else {
				 return "Error--Failed to Save Data";
			}
		}
	}

	public static function updateScheduleOrderID(){
		$con = Connect::con();
		$update_query = "UPDATE `order_onsite_interpreter` set orderID = '$orderID' WHERE autoID='" . $onsiteAutoId."'";
		$result = mysqli_query($con, $update_query);
		if($result){
			return true;
		}
		return false;
	}

	/**
  	 *
  	 * Block comment TO USE
  	 *
  	 */
  	public static function getOrderOnsiteInterpreter($CustomerID) {
	    $conn = parent::connect();
	    $sql = "SELECT * FROM " . getenv('TBL_ORDER_ONSITE_INTERPRETER') . " WHERE CustomerID = :CustomerID";
	    try {
		    $st = $conn->prepare($sql );
		    $st->bindValue(":CustomerID", $CustomerID, \PDO::PARAM_INT);
		    $st->execute();
		    $row = $st->fetch();
		    parent::disconnect($conn);
		    if ($row) {
		      	return new CustLogin($row);
		    } else return false;
	    } catch (\PDOException $e) {
		      parent::disconnect($conn);
		      return false;
	    }
  	}

  	function get_interpret_order($order_id, $get_value) {
  		$con = Connect::con();
	    $get_values = (is_array($get_value)) ? implode(",", $get_value) : $get_value;
	    $get_order_info = mysqli_query($con, "SELECT $get_values FROM order_onsite_interpreter WHERE orderID =  '$order_id'");
	    $order = mysqli_fetch_array($get_order_info);
	    if ($get_value === "*" || is_array($get_value)) {
	        return $order;
	    } else {
	        return $order[$get_value];
	    }
	}

	public static function getOrderOnsiteInterpreters($CustomerID){ // Limit where scheduling_type = getcall ili conferenceCall
		$con = Connect::con();
		$project_query =  "SELECT * FROM " . getenv("TBL_ORDER_ONSITE_INTERPRETER") . " WHERE customer_id='$CustomerID' ORDER BY `orderID` DESC";
		$result = mysqli_query($con, $project_query);
		return $result;
	}

}