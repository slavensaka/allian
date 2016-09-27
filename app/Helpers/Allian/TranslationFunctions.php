<?php

namespace Allian\Helpers\Allian;

use \Dotenv\Dotenv;
use Database\Connect;
use Allian\Models\Login;
use Allian\Models\LangList;
use Allian\Models\TranslationOrders;
use Allian\Models\OrderOnsiteInterpreter;
use Allian\Helpers\Allian\TranslationFunctions;
use Allian\Helpers\Allian\ScheduleFunctions;

class TranslationFunctions {

	/*
	 	order_onsite_template function renders order template for transcription orders so that it may be sent to Customer as a Receipt,
		to Linguist as an Order Detail and to Admin.
		@Param  $con: Required to create database connection. Required argument.
		@Param  $order_id: It is the Order Id against what the reports/reciept/template is generated. Required argument.
		@Param  $user_type: Different templates are generated for different users. This argument is provided at the time of calling this function to generate template. The possible users are as a follows.
		admin: to generate order details template for admins
		account: to generate order details template for Customers to view in their portals.
		guest: to generate order details template for customer who checkout as a guest.
		login: to generate order details template for customer who logs in with existing account before checkout.
		register: to generate order details template for customer who newly registers with Alliance Business Solutions before checkout.
		@Param  $view_type: Either editable view or Readable view. Possbile values are as follows
		user: For readable view. It is the default value
		admin: For editable view. when passed this value to displays textfields with actual values in them for admin to update them when required.
		NOTE: The Order details are almost same for all type of users but just changed the look and feel as well as provided different guidence for different users.
		// order_onsite_template($data['orderId'], 'account', "user")
	*/
	function order_onsite_template($order_id, $user_type, $view_type = "user") {
	   	$row = TranslationOrders::getTranslationOrder($order_id, '*');
	    $interpret_order = OrderOnsiteInterpreter::get_interpret_order($order_id, "*");
	    if(is_array($row) and is_array($interpret_order)){
		    $order_type = $row["order_type"];
		    $conference = self::get_conference($order_id, "*");
		    $total_price = $row["total_price"];
		    $project_langs = trim(LangList::get_language_name($interpret_order["frm_lang"])) . " <> " . trim(LangList::get_language_name($interpret_order["to_lang"]));
		    $project_timezone = $interpret_order["timezone"];
		    $project_date_from = $interpret_order["assg_frm_date"];
		    $project_date_from = date('m.d.Y', strtotime($project_date_from));
		    $project_date_to = $interpret_order["assg_to_date"];
		    $project_date_to = date('m.d.Y', strtotime($project_date_to));
		    $project_start_time = date('h:i A', strtotime($interpret_order["assg_frm_st"]));
		    $project_end_time = date('h:i A', strtotime($interpret_order["assg_frm_en"]));
		    $telephonic_duration = self::get_assignment_time($project_start_time, $project_end_time);
		    $telephonic_duration = $telephonic_duration['duration'] / 60; // get minutes
	    	$discount = self::amt_format($row["discount"], 2, ".", "");
	    	$additional_fee = $interpret_order["additional_fee"];
	       	$overage_amt = 0;
		    $grand_total = self::amt_format(($total_price + $addons_price + $additional_fee) - $discount + $overage_amt);
		    /*====================================
		    =            Return Array            =
		    ====================================*/
		    $rArray['projectDescription'] = $interpret_order["description"];
		    $rArray['projectId']  = $order_id;
		    $rArray['projectLangs'] =  $project_langs;
		    $rArray['timeStarts'] = $project_date_from;
			$rArray['timeEnds'] = $project_date_to;
			$rArray['minutesScheduled'] = $telephonic_duration . ' Minutes';
			$rArray['timezone'] = $project_timezone;
			$rArray['scheduledStartTime'] = $project_start_time;
			$rArray['scheduledEndTime'] = $project_end_time;
			$rArray['conferenceSecretCode'] = $conference['user_code'];
			$daily = $grand_total - 5;
			$rArray['daily'] = "ATS - Regular Telephonic Scheduling ($3/Min) for " . $telephonic_duration . " minutes";
			$rArray['dailyPrice'] = "$$daily" . ".00"; // $this->getFromTime();
			$rArray['conferencePresent'] = "$5.00";
			$rArray['grandTotal'] = $grand_total;
			$rArray['status'] = 1;
			$rArray['userMessage'] = "Order summary";
			// $rArray['daily'] = "$scheduling_type Telephonic Scheduling ($$rate_per_min/Min) for $actual_minutes minutes";
			if($interpret_order["scheduling_type"] == 'conference_call'){
				// $rArray['conferenceDialNumber'] = getenv('CONF_DIAL_ALLIAN_LIVE');
			} else if($interpret_order["scheduling_type"] == 'get_call'){
				$rArray['conferenceDialNumber'] = $interpret_order['onsite_con_phone'];
			}
			 /*=====  End of Return Array  ======*/
		    return $rArray;
	    }  else{
	        return false;
	    }
	}

	/*
	  get_conference function resturns the value from conference_shedule database table against order id.
	  @Param $con: Required for database connection.
	  @Param $order_id: Order ID against what the value from table is returned.
	  @Param $get_value: The actual value that is returned from table.
		 Usage:
		1: Please press Ctrl+Shift+f
		2: A search Window asks to search specific function. You may search "get_conference(" without double quotes and with opening parentheses.
		3: choose directory to search within and press "Find" Button
		4: The "Search Results" panel will search and display the pages where this functions has been used in the code.
	*/
	function get_conference($order_id, $get_value) {
		$con = Connect::con();
	    $get_order_info = mysqli_query($con, "SELECT $get_value FROM conference_shedule WHERE orderID =  '$order_id'");
	    $order = mysqli_fetch_array($get_order_info);
	    $get = $order[$get_value];
	    // If whole record is required then return whole object
	    return ($get_value === "*") ? $order : $get;
	}

	/*
		amt_format function is used to format the amount (price) value.
	 * For example a system calculates price something like 23 and this function reformats the price into 23.00
	 * @Param @amt: The amout value to be reformatted. Required argument
	 * @Param $decimels: The position of decimels point. By default set as "2". Optional argument
	 * @Param $decimel_point: Character to show decimels. By default set as a point ".". Optional argument
	 * @Param $thousand_sep: This is used as a thousand seperator. By default set nothing. Optional argument
		Usage:
		1: Please press Ctrl+Shift+f
		2: A search Window asks to search specific function. You may search "amt_format(" without double quotes and with opening parentheses.
		3: choose directory to search within and press "Find" Button
		4: The "Search Results" panel will search and display the pages where this functions has been used in the code.
	*/
	function amt_format($amt, $decimels = "2", $decimel_point = ".", $thousand_sep = "") {
		// Return number formate
		return number_format($amt, $decimels, $decimel_point, $thousand_sep);
	}

	/**
	 *
	 * Block comment
	 *
	 */
	function get_assignment_time($actual_starting_time, $actual_ending_time = "") {
	    $time_difference = strtotime($actual_starting_time) - time();
	    $duration = strtotime($actual_ending_time) - strtotime($actual_starting_time);
	    $hours = floor($time_difference / 3600);
	    $hours = ($hours < 10) ? "0" . $hours : $hours;
	    $minutes = floor(($time_difference / 60) % 60);
	    $minutes = ($minutes < 10) ? "0" . $minutes : $minutes;
	    $seconds = $time_difference % 60;
	    $seconds = ($seconds < 10) ? "0" . $seconds : $seconds;
	    $time_left = "$hours:$minutes:$seconds";
	    // True if start time is less than 24
	    // False if start time is greater than 24 hours
	    // False if start time is less than an Hour (no need to send 24hr prior notification when only 1 hour is left)
	    $notify = (86400 > $time_difference && $time_difference > 3600); // 24 hours left
	    $notify_checkout = ($actual_ending_time == "") ? false : time() > strtotime($actual_ending_time);
	    return array("notify_24_hours" => $notify, "notify_checkout" => $notify_checkout, "time_to_start" => $time_left, "hours_to_start" => $hours, "minutes_to_start" => $minutes, "time" => $time_difference, "duration" => $duration);
	}

	/**
	 *
	 * Something that can be used
	 *
	 */
	function getFromTime($actual_starting_time, $actual_ending_time = "") {
	    $time_difference = strtotime($actual_starting_time) - time();
	    $duration = strtotime($actual_ending_time) - strtotime($actual_starting_time);
	    $hours = floor($time_difference / 3600);
	    $hours = ($hours < 10) ? "0" . $hours : $hours;
	    $minutes = floor(($time_difference / 60) % 60);
	    $minutes = ($minutes < 10) ? "0" . $minutes : $minutes;
	    $seconds = $time_difference % 60;
	    $seconds = ($seconds < 10) ? "0" . $seconds : $seconds;
	    $time_left = "$hours:$minutes:$seconds";
	    // True if start time is less than 24
	    // False if start time is greater than 24 hours
	    // False if start time is less than an Hour (no need to send 24hr prior notification when only 1 hour is left)
	    $notify = (86400 > $time_difference && $time_difference > 3600); // 24 hours left
	    $notify_checkout = ($actual_ending_time == "") ? false : time() > strtotime($actual_ending_time);
	    return array("notify_24_hours" => $notify, "notify_checkout" => $notify_checkout, "time_to_start" => $time_left, "hours_to_start" => $hours, "minutes_to_start" => $minutes, "time" => $time_difference, "duration" => $duration);
	}

}