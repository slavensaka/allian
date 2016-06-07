<?php
$data['amount'] = calculate_price(array(
		'scheduling_type'=>$data['scheduling_type'],
		'interpreting_dur'=>$data['interpreting_dur'],
		'frm_lang'=>$data['frm_lang'],
		'to_lang'=>$data['to_lang'],
		'country'=>$data['country'],
		'frm_time'=> $data['assg_frm_date'].'T'.$data['assg_frm_st'],
		'to_time' => $data['assg_to_date']. 'T'.$data['assg_frm_en'],
		'timezone' => $data['timezone'],
		'equip_needed'	=> $data['equip_needed'],
		'headsets_needed' => $data['headsets_needed'],
		'interpreting_type' => $data['interpreting_type'],
		'dist_frm_reg_srvc_area' => $data['dist_frm_reg_srvc_area'],
		'is_onsite_page' => $_POST['is_onsite_page']
));

function calculate_price($data) {
    global $con;
    $type = $data['scheduling_type']; //regular/prem/plat
    $duration = $data['interpreting_dur'];
    if ($duration == '')
        $duration = '0 to 2';
    $frm_lang = $data['frm_lang'];
    $to_lang = $data['to_lang'];
    $country = isset($data['country']) ? $data['country'] : $data['loc_country'];
    $frm_time = $data['frm_time'];
    $to_time = $data['to_time'];
    $timezone = $data['timezone'];
    $details = array();
    $amount = 0;
    if ($data['scheduling_type'] == 'regular' || $data['scheduling_type'] == 'premium' || $data['scheduling_type'] == 'platinum') {
        switch ($type) {
            case 'regular': $amount+=200;
                $details['daily'][] = "Regular Charge (0-2 Hours):: $200";
                break;
            case 'premium': $amount+=235;
                $details['daily'][] = "Premium Charge (0-2 Hours):: $235";
                break;
            case 'platinum': $amount+=310;
                $details['daily'][] = "Platinum Charge (0-2 Hours):: $310";
                break;
            default: $amount+=310;
                $details['daily'][] = "Platinum Charge (0-2 Hours):: $310";
                break;
        }
        //echo $amount."<br>";
        //echo $amount."<br>";
        if ($duration != '0 to 2') {
            switch ($type) {
                case 'regular':
                    $amount+= (75 * (intval($duration) - 2));
                    $details['daily'][] = "Extra Duration(" . (intval($duration) - 2) . " Hr) Charge (Regular) :: $" . (75 * (intval($duration) - 2));
                    break;
                case 'premium':
                    $amount+= (85 * (intval($duration) - 2));
                    $details['daily'][] = "Extra Duration(" . (intval($duration) - 2) . " Hr) Charge (Premium) :: $" . (85 * (intval($duration) - 2));
                    break;
                case 'platinum':
                    $amount+= (110 * (intval($duration) - 2));
                    $details['daily'][] = "Extra Duration(" . (intval($duration) - 2) . " Hr) Charge (Platinum) :: $" . (110 * (intval($duration) - 2));
                    break;
            }
        }
        $dist = floatval($data['dist_frm_reg_srvc_area']);
        if ($dist != 0) {
            $min = 50;
            $dist_amt = $dist * 1.5;
            $dist_amt = $dist_amt > $min ? $dist_amt : $min;
            //$amount+=$amt;
        }
        // The countries where International Fee for onsite services is not applied
        // Example below explains how to add another contry in the list
        // $country_list = array("United States","Canada","Mexico","Pakistan","any other country");
        $free_countries = array("United States","Canada","Mexico");
        $international_serivce_fee = 200;
        $is_onsite_page =  $data["is_onsite_page"];
        $free_country_found = (in_array($country, $free_countries));
		//echo $amount."<br>";
        if ($frm_time != '' and $to_time != '') {
            try {
                $frm_time = new DateTime($data['frm_time'], new DateTimeZone($timezone));
                $to_time = new DateTime($data['to_time'], new DateTimeZone($timezone));
                $days = 1;
                do{
                    if ($frm_time->format('Y-m-d') == $to_time->format('Y-m-d') or $frm_time >= $to_time)
                        break;
                    $days++;
                    $frm_time->modify('+24 hours');
                }while ($frm_time->format('Y-m-d') != $to_time->format('Y-m-d') and $frm_time < $to_time);
            } catch (Exception $e) {
                $days = 1;
            }
            if (isset($data['interpreting_type']) and $data['interpreting_type'] == "simultaneous") {
                $details['daily'][] = "+100% for Simultaneous Interpretation :: $" . $amount;
                $amount += $amount;
            }
            //echo $amount."<br>";
            if (isset($data['equip_needed']) && isset($data['headsets_needed'])) {
                if ($data['headsets_needed'] > 0 && $data['headsets_needed'] <= 10) {
                    $amount += 600;
                    $details['daily'][] = "1 - 10 Headsets :: $600";
                } else if ($data['headsets_needed'] > 10) {
                    $amount += 600;
                    $extra_pieces = $data['headsets_needed'] - 10;
                    $amount += 25 * $extra_pieces;
                    $details['daily'][] = "More Than 10 Headsets ($600 for 1-10 + $25 per extra piece ) :: $" . (600 + 25 * $extra_pieces);
                }
            }
            if ($to_lang != '') {
                $query = "select * from LangList where LangId='$to_lang'";
                $result = mysqli_query($con, $query);
                if ($result and mysqli_num_rows($result) > 0) {
                    $row = mysqli_fetch_assoc($result);
                    $tier = $row['TierType_Interpret'];
                    $query = "select * from LangList where LangId='$frm_lang'";
                    $result = mysqli_query($con, $query);
                    if ($result and mysqli_num_rows($result) > 0) {
                        $row = mysqli_fetch_assoc($result);
                        $tierF = $row['TierType_Interpret'];
                    }
                    if ((int) $tier < (int) $tierF) {
                        $tier = (int) $tierF;
                    }
                    switch ((int) $tier) {
                        case 1: $amount+=0;
                            $details[] = "Tier 1 Language Charge :: $0";
                            break;
                        case 2:
                            switch ($type) {
                                case 'regular': $amount+= 75;
                                    $details['daily'][] = "Tier 2 Language Charge (Regular) :: $75";
                                    break;
                                case 'premium': $amount+= 100;
                                    $details['daily'][] = "Tier 2 Language Charge (Premium) :: $100";
                                    break;
                                case 'platinum': $amount+= 150;
                                    $details['daily'][] = "Tier 2 Language Charge (Platinum) :: $150";
                                    break;
                            }
                            break;
                        case 3:
                            switch ($type) {
                                case 'regular': $amount+= 125;
                                    $details['daily'][] = "Tier 3 Language Charge (Regular) :: $125";
                                    break;
                                case 'premium': $amount+= 150;
                                    $details['daily'][] = "Tier 3 Language Charge (Premium) :: $150";
                                    break;
                                case 'platinum': $amount+= 200;
                                    $details['daily'][] = "Tier 3 Language Charge (Platinum) :: $200";
                                    break;
                            }
                            break;
                    }
                }
            }
            //echo $amount."<br>";
            if (empty($days))
                $days = 1;
            if (empty($dist_amt)) {
                $dist_amt = 0;
            }
            $amount += $dist_amt;
            if ($dist_amt > 0 && $dist > 0) {
                $details['daily'][] = "Distance from Regular Service Area is $dist Miles :: $" . $dist_amt;
            }
            //$details['daily'][] = "Total Charge for 1 day :: $".$amount;
            //if($days>1)
            $days_text = ($days > 1) ? "days" : "day";
            $add_international_fee = (!$free_country_found && $is_onsite_page=="true")?"+ $".$international_serivce_fee:"";
            // Not Free country found
        	if (!$free_country_found && $is_onsite_page=="true"){
		        $amount+= $international_serivce_fee;
		        $details['daily'][] = "Internationals On-Site Service Fee :: $".$international_serivce_fee;
        	}
            //$details['daily'][] = "For $days $days_text ($ $amount x $days) :: $".($amount*$days);
            $details['daily'][] = "DAYS::" . $days . " " . $days_text . " ($$amount X $days) $add_international_fee";
            $amount = $amount * $days;
        }
    } else if ($data['scheduling_type'] == 'conference_call' || $data['scheduling_type'] == 'get_call' ){
        // Get number of hours left to start
        $timing = get_assignment_time($frm_time,$to_time);
        $hours_left = $timing["hours_to_start"];
        $minimum_rate = 30; // $30
        $conference_fee = amt_format(5);
        if($hours_left<24) {
	        $minimum_minutes = 10; // 10 minutes
	        $rate_per_min=3; // $3
	        $scheduling_type="Short Notice";
        }else{
	        $minimum_minutes = 15;  // 30 minutes
	        $rate_per_min=1.75; // $1.75
	        $scheduling_type="Regular";
        }
        // Get duration in minutes
        $minutes = telephonic_duration($frm_time, $to_time);
        $actual_minutes = $minutes;
        $minimum_appied = ($minutes <= $minimum_minutes)?true:false;
        $minutes = ($minimum_appied) ?$minimum_minutes:$minutes;
        $minimum_text = ($minimum_appied)?"<br><br><span style='color:red;font-size:9px;font-weight:bold;'>Minimum $scheduling_type telephonic scheduling price is $$minimum_rate</span>":"";
        /*/ Check if lang pair is already defined in database
        if ($rate = lang_pair_rate($frm_lang, $to_lang)) {
            $lang_pair_rate = $rate/100;
        } else {
        $lang_pair_rate = $rate_per_min;
        }
        */
        $amount+= ($minimum_appied)?$minimum_rate:$rate_per_min * $minutes;
        $amount = amt_format($amount);
        //ATS - Short Notice Telephonic Scheduling ($3/Min) for 0-10 minutes
        //ATS - Regular Telephonic Scheduling ($3/Min) for 15 minutes
        $details['daily'][] = "ATS - $scheduling_type Telephonic Scheduling ($$rate_per_min/Min) for $actual_minutes minutes$minimum_text::$$amount";
	    if ($type == 'conference_call') {
	        $amount+= $conference_fee;
	        $details['daily'][] = "Conference Calling Fee:: $$conference_fee";
	    }
	    $debug = date("Y-m-d h:i:s")."<br>";
	    $debug .= "<br>From Time = ".$frm_time;
	    $debug .= "<br>To Time = ".$to_time;
	    $debug .= "<br>Time Left = ".$hours_left;
	    $debug .= "<br>Scheduling_type = ".$data['scheduling_type'];
    	//send_notification("onsite_price_calc debugging", $debug, "goharulzaman@yahoo.com");
    }
    if (!empty($data['calculate_mode']) && $data['calculate_mode'] == "Detailed"){
        return $details;
    }
    return amt_format($amount);
}