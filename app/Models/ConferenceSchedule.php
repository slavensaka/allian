<?php

namespace Allian\Models;

use Database\Connect;
use Database\DataObject;

class ConferenceSchedule extends DataObject {

	protected $data = array(
	    "conf_id" => "",
	    "orderID" => "",
	    "conf_tag" => "",
	    "user_code" => "",
	    "interpreter_code" => "",
	    "start_datetime" => "",
	    "end_datetime" => "",
	);

	/**
	 *
	 * get_conference function resturns the value from conference_shedule database table against order id.
  	 @Param $con: Required for database connection.
  	 @Param $order_id: Order ID against what the value from table is returned.
   	 @Param $get_value: The actual value that is returned from table.
	 *
	 */
	function get_conference($con, $order_id, $get_value) {
		$con = Connect::con();
		$get_order_info = mysqli_query($con, "SELECT $get_value FROM conference_shedule WHERE orderID =  '$order_id'");
		$order = mysqli_fetch_array($get_order_info);
		$get = $order[$get_value];
		// If whole record is required then return whole object
		return ($get_value === "*") ? $order : $get;
		}

}