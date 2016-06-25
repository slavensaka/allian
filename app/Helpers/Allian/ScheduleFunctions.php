<?php

namespace Allian\Helpers\Allian;

// use Database\DatabaseConnection;

class ScheduleFunctions {

	/**
	 *
	 * Block comment
	 *
	 */
	public static function calculateAmountToPay($data){
		$amount = 0;
		$minimum_rate = 30;
		$conference_fee = self::amt_format(5); // 5$ usd for creating a confrence call

		$frm_time = $data['fromDate'] . ' ' . $data['timeStarts'];
		$to_time = $data['fromDate'] . ' ' . $data['timeEnds'];

		$timing = self::get_assignment_time($frm_time,$to_time);
		$hours_left = $timing["hours_to_start"];

		if($hours_left<24) {
		    $minimum_minutes = 10;
		    $rate_per_min=3;
		    $scheduling_type="Short Notice";
		}else{
		    $minimum_minutes = 15;
		    $rate_per_min=1.75;
		    $scheduling_type="Regular";
		}

 		$minutes = self::telephonic_duration($frm_time, $to_time);
		$actual_minutes = $minutes;
		$minimum_appied = ($minutes <= $minimum_minutes) ? true : false;
		$minutes = ($minimum_appied) ? $minimum_minutes : $minutes;

		$minimum_text = ($minimum_appied) ? "Minimum $scheduling_type telephonic scheduling price is $$minimum_rate" : "";
		$amount += ($minimum_appied) ? $minimum_rate : $rate_per_min * $minutes;

		if(array_key_exists("schedulingType", $data)){
			if ($data['schedulingType'] == 'conference_call') {
			    $amount += $conference_fee;
			   	$rArray['conferencePresent'] = "Conference Calling Fee:: $$conference_fee";
			} else{
				$rArray['conferencePresent'] = null;
			}
		}

		$ret = array();
		$ret['totalPrice'] = self::amt_format($amount);
		$ret['daily'] = "ATS - $scheduling_type Telephonic Scheduling ($$rate_per_min/Min) for $actual_minutes minutes";
		$ret['status'] = 1;
		if($minimum_text){
			$ret['minimumText'] = $minimum_text ;
		} else {
			$ret['minimumText'] = null;
		}
		return $ret;
	}

	/**
	 *
	 * Block comment
	 *
	 */
	public function amt_format($amt, $decimels = "2", $decimel_point = ".", $thousand_sep = "") {
		return number_format($amt, $decimels, $decimel_point, $thousand_sep);
	}

	/**
	 *
	 * Block comment
	 *
	 */
	function telephonic_duration($frm_time, $to_time) {
	    $start_date = new \DateTime($frm_time);
	    $since_start = $start_date->diff(new \DateTime($to_time));
	    $days = $since_start->d + 1;
	    $minutes += $since_start->h * 60;
	    $minutes += $since_start->i;
	    return $minutes * $days;
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
}