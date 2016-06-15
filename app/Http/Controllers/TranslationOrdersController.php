<?php

namespace Allian\Http\Controllers;

use Firebase\JWT\JWT;
use \Dotenv\Dotenv;
use Allian\Models\TranslationOrders;
use Database\Connect;
use Allian\Models\OrderOnsiteInterpreter;

class TranslationOrdersController extends Controller {

	/**
     * @ApiDescription(section="OrderSummary", description="Retrieve the orders summary json.")
     * @ApiMethod(type="post")
     * @ApiRoute(name="/testgauss/orderSummary")
     * @ApiBody(sample="{'data': {
	    'CustomerID': '406'
	  },
     'token': 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzUxMiJ9.eyJpYXQiOjE0NjU1NDY1MjcsImp0aSI6IklGSTJTcmxlbWtQck1ncUZNSmV1RDZYYTlUTzRQbm02TmVGdThyK1VLV2c9IiwiaXNzIjoibG9jYWxob3N0IiwibmJmIjoxNDY1NTQ2NTI3LCJleHAiOjE1OTE2OTA1MjcsImRhdGEiOnsiU3VjY2VzcyI6IlN1Y2Nlc3MifX0.ff_JJqrXL1HLsTGRo7HA6q9YQJWiLaQRoVy0RcQYnDPpFQu-0HH1bYQ8PLHnyaOzSm3yYXkCle0gLd1O80vREg'}")
     *@ApiParams(name="data", type="string", nullable=false, description="Data")
     @ApiParams(name="token", type="string", nullable=false, description="Autentication token for users autentication.")
     * @ApiReturnHeaders(sample="HTTP 200 OK")
     * @ApiReturn(type="string", sample="{
     *  'data': {
	     'orderSummary': [
	     {
		'orderId': '3763',
        'orderTime': '2015-05-23',
        'cost': 'Total: 185$'
        }], '...': ''
	  	}
     * }")
     */
	public function orderSummary($request, $response, $service, $app) {
		if($request->token){
		// 	Validate token if not expired, or tampered with
			$this->validateToken($request->token);
			// 	Decrypt data
			$data = $this->decryptValues($request->data);
			// Validate input data
			$service->validate($data['CustomerID'], 'Error: No customer id is present.')->notNull()->isInt();
			//Validate the jwt token in the database
			$validated = $this->validateTokenInDatabase($request->token, $data['CustomerID']);
			// If error validating token in database
			if(!$validated){
	     		return $response->json(array('data' => $this->errorJson("Authentication problems present")));
			}

			$result = TranslationOrders::getTranslationOrders($data['CustomerID']);

			$arr = array();
			while ($row = mysqli_fetch_array($result)) {
				$order_id = $row["order_id"];
				$order_time = substr($row["order_time"], 0, -9);
				$order_type = $row["order_type"];
				// $cost = ($row["total_price"]+$addon); TODO
				$cost = ($row["total_price"] + 0 );
				if($order_type == 5){ // Telephonic only
					$arr[] = array('orderId' => $order_id, 'orderTime' => $order_time, 'cost' => 'Total: ' .  $cost . '$');
				}
			}
			return $response->json(array('data' => array('orderSummary' => $arr, 'status' => 1)));
		} else {
			$base64Encrypted = $this->encryptValues(json_encode($this->errorJson("No token provided in request")));
     		return $response->json(array('data' => $base64Encrypted));
		}
	}

	/**
     * @ApiDescription(section="OrderSummaryDetails", description="Retrieve the orders summaries details json.")
     * @ApiMethod(type="post")
     * @ApiRoute(name="/testgauss/orderSummaryDetails")
     * @ApiBody(sample="{'data': {
	    'CustomerID': '406',
	    'orderID': '1'
	  },
     'token': 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzUxMiJ9.eyJpYXQiOjE0NjU1NDY1MjcsImp0aSI6IklGSTJTcmxlbWtQck1ncUZNSmV1RDZYYTlUTzRQbm02TmVGdThyK1VLV2c9IiwiaXNzIjoibG9jYWxob3N0IiwibmJmIjoxNDY1NTQ2NTI3LCJleHAiOjE1OTE2OTA1MjcsImRhdGEiOnsiU3VjY2VzcyI6IlN1Y2Nlc3MifX0.ff_JJqrXL1HLsTGRo7HA6q9YQJWiLaQRoVy0RcQYnDPpFQu-0HH1bYQ8PLHnyaOzSm3yYXkCle0gLd1O80vREg'}")
     *@ApiParams(name="data", type="string", nullable=false, description="Data")
     @ApiParams(name="token", type="string", nullable=false, description="Autentication token for users autentication.")
     * @ApiReturnHeaders(sample="HTTP 200 OK")
     * @ApiReturn(type="string", sample="{
     *  'data': {
	     'orderSummary': [
	     {
		'orderId': '3763',
        'orderTime': '2015-05-23',
        'cost': 'Total: 185$'
        }], '...': ''
	  	}
     * }")
     */
	public function orderSummaryDetails($request, $response, $service, $app) {

		// 	Decrypt data
		$data = $this->decryptValues($request->data);

		$service->validate($data['orderId'], 'Error: No order id is present.')->notNull();
		$service->validate($data['CustomerID'], 'Error: No customer id is present.')->notNull();

		// $translation_order = TranslationOrders::getTranslationOrder($orderId,"*");
		// $order_user_id = $translation_order["user_id"];
		// return $order_user_id;
		// if($order_user_id !== $data['CustomerID']){
		// 	$base64Encrypted = $this->encryptValues(json_encode($this->errorJson("Invalid Order")));
  		//    		return $response->json(array('data' => $base64Encrypted));
		// }
		$display_order = $this->order_onsite_template($data['orderId'], 'account');
		return $response->json(array('data' => $display_order));
	}

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
	Usage:
	1: Please press Ctrl+Shift+f
	2: A search Window asks to search specific function. You may search "order_onsite_template(" without double quotes and with opening parentheses.
	3: choose directory to search within and press "Find" Button
	4: The "Search Results" panel will search and display the pages where this functions has been used in the code.
	*/
	function order_onsite_template($order_id, $user_type, $view_type = "user") {
		// $con = Connect::con();
	    //  $result = mysqli_query($con, "SELECT * FROM translation_orders WHERE order_id=$order_id");
	    // return $row = mysqli_fetch_assoc($result);
	   	$row = TranslationOrders::getTranslationOrder($order_id, '*');
	    $interpret_order = OrderOnsiteInterpreter::get_interpret_order($order_id, "*");

	    // Check if results are found in translation orders table and onsite_order table
	    if(is_array($row) and is_array($interpret_order)){

		    $bold = "font-weight:bold";
		    $td_style = "style='color:#111;  vertical-align:top; padding:5px 0;  font-size:11px; border-bottom: 1px dotted #ccc;'";
		    $headStyle = "background-color:#f2f2f2; padding:5px 5px; margin:10px 0;";
		    $head_td_style = "style='color: #222; font-weight:bold; font-size:11px;   margin: 5px 0; border-bottom:1px dotted #ccc; padding: 5px 0'; ";
		    $foot_td_style = "style='color: darkgreen; font-weight:bold; font-size:14px;   margin: 5px 0; border-bottom:2px solid #ccc; padding: 5px 0'";
		    $order_type = $row["order_type"];
		    $isAdmin = ($user_type === "admin") ? true : false;
		    $h = ($isAdmin) ? "h3" : "h2";
		    $isTelephonic = false;
	    	if ($order_type == 4) {
	        	$order = "Onsite";
	    	} else if ($order_type == 5) {
		        $order = "Telephonic";//Z
		        $isTelephonic = true;//ZZ
	    	} else {
	        	$order = "On-Site";
	    	}
		    $order_detail = TranslationOrders::getTranslationOrder($order_id, '*');
		    $conference = $this->get_conference($order_id, "*");
		    $customer_id = $order_detail["user_id"];
		    $overage_charge = $this->amt_format($interpret_order["overage_charge"]);
		    $overage_status = $interpret_order["overage_status"];
		    $overage_payment_id = $interpret_order["overage_payment_id"];
		    $overage_fee_per_unit = $interpret_order["overage_amt_per_30min"];
		    $time_unit = ($order == "Telephonic") ? "Minute" : "Hour";
		    $is_overage = ($overage_charge > 0 && !is_null($overage_payment_id)) ? true : false;
		    $is_overage_charged = ($overage_status == "PAID") ? true : false;
		    $want_to_pay_overage = (isset($_GET["pay_overage"]) && $_GET["pay_overage"] == "yes") ? true : false;
		    if ($is_overage) {
		        $submitted_items = array("intProjectOverTime", "intProjectEndedAt");
		        $subt_project = get_submitted_projected($con, $order_id, $submitted_items);
		        foreach ($subt_project as $vars => $vals) {
		            $$vars = $vals;
		        }
		        $overage_fee_per_unit = ($isTelephonic) ? $overage_fee_per_unit : 110;
		        $intProjectOverTime = ($isTelephonic) ? $intProjectOverTime : ceil($intProjectOverTime / 60);
		    }
		    // if viewer is admin then create textfield instead of just text so that admin can update into
		    $name = ($view_type === "admin") ? "<input type='text' value='" . $row["name"] . "' name='client_name' />" : $row["name"];
		    $bname = ($view_type === "admin") ? "<input type='text' value='" . $row["business_name"] . "' name='client_business_name' />" : $row["business_name"];
		    $phone = ($view_type === "admin") ? "<input type='text' value='" . $row["phone"] . "' name='client_phone' />" : $row["phone"];
		    $email = ($view_type === "admin") ? "<input type='text' value='" . $row["email"] . "' name='client_email' />" : $row["email"];
		    $total_units = $row["total_units"];
		    $total_price = $row["total_price"];
		    $stripe_id = $row["stripe_id"];
		    $is_invoiced = ($stripe_id === "invoice") ? true : false;
		    $status = $row["status"];
		    switch ($status) {
		        case "0";
		            $order_status = "<em  style='color:grey'>PENDING</em>";
		            break;
		        case "1";
		            $order_status = "<em  style='color:green'>PAID</em>";
		            break;
		        case "2";
		            $order_status = "<em  style='color:green'>INVOICED</em>";
		            break;
		        case "3";
		            $order_status = "<em  style='color:red'>ABANDONED</em>";
		            break;
		        case "4";
		            $order_status = "<em  style='color:red'>CANCELLED</em>";
		            break;
		        case "5";
		            $order_status = "<em  style='color:red'>DELETED</em>";
		            break;
		        case "6";
		            $order_status = "<em  style='color:red'>CARD DECLINED</em>";
		            break;
		        case "7";
		            $order_status = "<em  style='color:red'>PROJECT SUBMITTED</em>";
		            break;
		    }
		    $hasPaid = ($status === "0" || $status === "1" || $status === "2") ? true : false;
		    $project_location = $interpret_order["service_region"] . " > " . $interpret_order["service_area"];
		    $project_langs = $this->get_language_name($con, $interpret_order["frm_lang"]) . " <> " . $this->get_language_name($con, $interpret_order["to_lang"]);
		    $project_timezone = $interpret_order["timezone"];
		    $project_date_from = $interpret_order["assg_frm_date"];
		    $project_date_from .= " " . date('l', strtotime($project_date_from));
		    $project_date_to = $interpret_order["assg_to_date"];
		    $project_date_to .= " " . date('l', strtotime($project_date_to));
		    $project_start_time = date('h:i A', strtotime($interpret_order["assg_frm_st"]));
		    $project_end_time = date('h:i A', strtotime($interpret_order["assg_frm_en"]));
		    $date1 = new DateTime($project_date_from);
		    $date2 = new DateTime($project_date_to);
		    $project_days = $date2->diff($date1)->format("%a") + 1;
		    $s = ($project_days > 1) ? "s" : "";
		    $project_amount = $interpret_order["amount"];
		    $project_invoice_id = $interpret_order["invoice_id"];
		    $project_hours = $interpret_order["interpreting_dur"];
		    $project_equipments = $interpret_order["equip_needed"];
		    $headsets_needed = false;
		    if ($project_equipments == 1) {
		        $headsets_needed = true;
		        $project_headsets_needed = $interpret_order["headsets_needed"];
		    }
		    // Get the shipment address for notarized orders
		    $interpret_address = "";
		    $onsite_contact_info = "";
		    $candidate_info = "";
		    // Display Interpreter Name on order detail at client portal if interpreter is assigned
		    if ($user_type === "account") {
		        // check if the order is assigned to interpreter
		        $interpreter_id = $interpret_order["interpreter_id"];
		        $ip = get_linguist($con, $interpreter_id);
		        if (is_array($ip)) {
		            $ip_name = $ip["Fname"] . " " . $ip["Lname"];
		            $interpret_address.= "<h2 style='$headStyle'>Interpreter Assigned</h2>"
		                    . "<b>Interpreter Name:</b> $ip_name<br>";
		        }
		    }
		    // Address is not Required/Available for Telephonic Projects
		    if (!$isTelephonic) { // Not Telephonic
		        $interpret_address.= "<br><$h style='$headStyle'>Address of Interpreting Session</$h>";
		        $interpret_address.= "<table style='font-size:10px;'>";
		        $interpret_address.="<tr><td style='$bold'>Address: </td><td>" . $interpret_order["street_address"] . "</td></tr>";
		        $interpret_address.="<tr><td> </td><td>" . $interpret_order["address_line_2"] . "</td></tr>";
		        $interpret_address.="<tr><td style='$bold'>City: </td><td> " . $interpret_order["city"] . "</td></tr>";
		        $interpret_address.="<tr><td style='$bold'>State/Provice/Region: </td><td> " . $interpret_order["state"] . "</td></tr>";
		        $interpret_address.="<tr><td style='$bold'>Postal/Zip Code: </td><td> " . $interpret_order["zip"] . "</td></tr>";
		        $interpret_address.="<tr><td style='$bold'>Country: </td><td> " . $interpret_order["country"] . "</td></tr>";
		        $interpret_address.= "</table>";
		    }
		    $overage_info = "";
		    if ($is_overage && $user_type == "account") {
		        // profile view of overage
		        $overage_info.= "<br><$h style='$headStyle'>Project Overage</$h>";
		        $overage_info.= "<p><strong>Project has been completed and went over the scheduled time.</strong><br><br></p>";
		        $overage_info.= "<span style='padding:5px 0;display: inline-block;width:160px;$bold'>Scheduled Ending Time:</span>" . strtoupper($project_end_time) . "<br>";
		        $overage_info.= "<span style='padding:5px 0;display: inline-block;width:160px;$bold'>Actual Ending Time:</span>" . strtoupper($intProjectEndedAt) . "<br>";
		        $overage_info.= "<span style='padding:5px 0;display: inline-block;width:160px;$bold'>Overage Charge Period:</span>$intProjectOverTime {$time_unit}(s)<br>";
		        $overage_info.= "<span style='padding:5px 0;display: inline-block;width:160px;$bold'>Overage Fee:</span>$$overage_fee_per_unit USD per $time_unit<br><br>";
		    }
		    // onsite contact details is not required for telephonic projects
		    $contact_name = ($isTelephonic) ? $order_detail["name"] : $interpret_order["onsite_con_name"];
		    $contact_phone = $interpret_order["onsite_con_phone"];
		    $onsite_contact_info.= "<br><$h style='$headStyle'>$order Contact Detail</$h>";
		    $onsite_contact_info.= "<table style='font-size:10px;'>";
		    $onsite_contact_info.="<tr ><td style='$bold'>Contact Name: </td><td>$contact_name</td></tr>";
		    // Contact Email is not required on telephonic
		    if (!$isTelephonic) {
		        $onsite_contact_info.="<tr><td style='$bold'>Contact Email: </td><td>" . $interpret_order["onsite_con_email"] . "</td></tr>";
		    }
		    if ($interpret_order["scheduling_type"] == "get_call" || !$isTelephonic) {
		        $onsite_contact_info.="<tr><td style='$bold'>Contact Phone#: </td><td>$contact_phone</td></tr>";
		    }
		    $onsite_contact_info.= "</table>";
		    // PSI form fields
		    if ($interpret_order["candidate_name"] != "" && $interpret_order["candidate_confirmation_num"] != "") {
		        $candidate_info.= "<br><$h style='$headStyle'>Candidate Information</$h>";
		        $candidate_info.= "<table style='font-size:10px;'>"
		                . "<tr ><td style='$bold'>Candidate Name: </td><td>" . $interpret_order["candidate_name"] . "</td></tr>"
		                . "<tr><td style='$bold'>Candidate Confirmation#: </td><td>" . $interpret_order["candidate_confirmation_num"] . "</td></tr>"
		                . "</table>";
		    }
		    $pro_description = $interpret_order["description"];
		    $description = "";
		    if (trim($pro_description) !== "") {
		        $description = "<br><$h style='$headStyle'>Project Description</$h>$pro_description<br><br>";
		    }
	    	$width = ($user_type == account) ? "95%" : "500px";
	    	$project_detail = "<table border='0px' id='Order_Summary' cellspacing='0' cellpadding='0'    style='background:#fff;  padding:10px; border: 0px; border-radius:10px; border-spacing: 0px; width:$width; '><tr><td $head_td_style width='160px'>Project ID:</td><td $td_style >$order_id</td></tr>";
	    	if ($order !== "Telephonic") {
		        // Project Location is not set for telephonic orders
		        $project_detail .= "<tr><td $head_td_style >Project Location:</td><td $td_style >$project_location</td></tr>";
	    	}
	    	if($interpret_order["examination_type"] != ""){
	    		$project_detail .= "<tr><td $head_td_style >Examination Type:</td><td $td_style >{$interpret_order['examination_type']}</td></tr>";
	    	}
	    	$project_detail .= "<tr><td $head_td_style >Project Languages:</td><td $td_style >$project_langs</td></tr>";
			$project_detail .= "<tr><td $head_td_style >Project Start Date:</td><td $td_style >$project_date_from</td></tr><tr><td $head_td_style >Project End Date:</td><td $td_style >$project_date_to</td></tr>";
		    if ($isTelephonic) {
		        $telephonic_duration = get_assignment_time($project_start_time, $project_end_time);
		        $telephonic_duration = $telephonic_duration['duration'] / 60; // get minutes
		        $project_detail .= "<tr><td $head_td_style >Minutes Scheduled:</td><td $td_style >$telephonic_duration Minutes</td></tr> <tr><td $head_td_style >Time Zone:</td><td $td_style >$project_timezone</td> </tr> ";
	   		} else {
	        $project_detail .= "<tr><td $head_td_style >Days Scheduled:</td><td $td_style >$project_days Day$s</td> </tr><tr><td $head_td_style >Hours Scheduled:</td><td $td_style >$project_hours Hours</td></tr>";
	    	}
	    	$project_detail .= "<tr><td $head_td_style >Scheduled Start Time:</td><td $td_style >$project_start_time</td></tr><tr><td $head_td_style >Scheduled End Time:</td><td $td_style >$project_end_time</td></tr>";
	    	if ($headsets_needed) {
	        	$project_detail .= "<tr><td $head_td_style >Headsets Needed:</td><td $td_style >$project_headsets_needed</td></tr> ";
	    	}
	    	if ($isTelephonic && $interpret_order["scheduling_type"] == "conference_call" && $view_type !== "overage") {
	        	global $client_dial;
	        	$project_detail .= "<tr style='background:lemonchiffon'><td $head_td_style ><span  style='color:red;font-size:14px'>Conference Dial Number:</span></td><td $head_td_style ><span  style='color:red;font-size:14px'>$client_dial</span></td></tr> ";
	        	$project_detail .= "<tr style='background:lemonchiffon'><td $head_td_style ><span  style='color:red;font-size:14px'>Conference Secret Code:</span></td><td $head_td_style ><span  style='color:red;font-size:14px'>{$conference['user_code']}</span></td></tr> ";
	    	}
	    	if ($is_overage && $view_type == "overage") {
		        $project_detail .= "<tr style='background:lemonchiffon'><td $head_td_style ><span  style='color:red;font-size:12px'>Actual Ending Time:</span></td><td $td_style ><span  style='color:red;font-weight:bold'>" . strtoupper($intProjectEndedAt) . "</span></td></tr> ";
		        $project_detail .= "<tr style='background:lemonchiffon'><td $head_td_style ><span  style='color:red;font-size:12px'>Overage Charge Period:</span></td><td $td_style ><span  style='color:red;font-weight:bold'>$intProjectOverTime {$time_unit}(s)</span></td></tr> ";
		        $project_detail .= "<tr style='background:lemonchiffon'><td $head_td_style ><span  style='color:red;font-size:12px'>Overage Fee:</span></td><td $td_style ><span  style='color:red;font-weight:bold'>$$overage_fee_per_unit USD per $time_unit</span></td></tr> ";
	    	}
	    	$invoice_summary = "<tr><td colspan='2' ><h4 style='background-color:#f2f2f2; color:#333; padding:5px 5px; margin:10px 0;'> Price Summary </h4></td></tr><tr><td style='color:#111; font-weight:bold;  vertical-align:top; padding:5px 0;  font-size:11px; border-bottom: 1px dotted #ccc;'>Subtotal for Invoice ID#$project_invoice_id</td><td style='color:#111; font-weight:bold;  vertical-align:top; padding:5px 0;  font-size:11px; border-bottom: 1px dotted #ccc;'> $" . amt_format($project_amount) . "</td></tr><tr style='background:#FFFFDD;'><td  style='color: darkgreen; font-weight:bold; font-size:14px;   margin: 5px 0; border-bottom:2px solid #ccc; padding: 5px 0'>Total Price</td><td  style='color: darkgreen; font-weight:bold; font-size:14px;   margin: 5px 0; border-bottom:2px solid #ccc; padding: 5px 0'>$" . amt_format($project_amount) . "</td></tr>";
	    	$order_price_summary = (isset($project_invoice_id) && $project_invoice_id != "") ? $invoice_summary : $interpret_order["order_summary_html"];
	    	if ($is_overage && $view_type == "overage") {
	        	$project_detail .= "<tr style='background:#FFFFDD;'><td  $foot_td_style>Overage Subtotal</td><td  $foot_td_style> $" . amt_format($overage_charge) . "</td></tr>";
	    	} else {
	        	$project_detail .= ($order_price_summary == "") ? "<tr style='background:#FFFFDD;'><td  $foot_td_style>Fee</td><td  $foot_td_style> $" . amt_format($total_price) . "</td></tr>" : $order_price_summary;
	    	}
	    	$project_detail .= "</table>";
	    	$discount = amt_format($row["discount"], 2, ".", "");
	    	$body = "<html><head></head><body style='font-family: arial; font-size: 12px; color: #444;'><div>";
		    $addons_price = 0;
		    $addons_price_summary = "";
		    $addons_price_admin = "";
		    $addons_price_summary .= "<tr style='background:#FFFFDD'><td $foot_td_style><div style='float:right; margin-right:20px'>Add-Ons Total</div></td><td $foot_td_style>$$addons_price</td></tr>";
	    	$additional_fee = $interpret_order["additional_fee"];
	    	if ($additional_fee > 0) {
		        $additional_fee_desc = $interpret_order["additional_fee_desc"];
		        $addons_price_admin.= "<p style='$bold;color:green'>+ $" . amt_format($additional_fee) . " " . $additional_fee_desc . " </p>";
	    	}
	    	if ($discount > 0) {
	        	$addons_price_admin.= "<p style='$bold;color:red'>- $$discount Discount </p>";
	    	}
	    	// Add an overage price
	    	$overage_amt = 0;
	    	if ($is_overage) {
		        $overage_amt = $overage_charge;
		        $addons_price_admin.= "<p style='$bold;color:red'>+ $$overage_amt Overage Charges </p>";
	    	}
	    	if (!$isAdmin) {
		        // If user is a customer, not admin
		        // user_type == account means to display order detail to logged in customer in his portal
		        if ($user_type !== "account") {
		            // this is just for email
		            $body .= "<p>Dear " . ucwords($name) . ",</p>
		            <p>Thank you for scheduling a $order Interpreter with Alliance Business Solutions LLC</p>";
		        }
		        if ($is_overage_charged == false && $is_overage) {
		            $body .= "<br>Your project went over by <strong>$intProjectOverTime $time_unit(s)</strong> and requires a additional payment of <strong>$$overage_charge USD</strong>. We have tried to charge your existing mode of payment but were unsuccesfull.<br><br>";
		            if ($want_to_pay_overage == false) {
		                $body .= "<strong>At your earliest convenience please process the additional payment by clicking on the \"Pay Now\" button below<br><br></strong>";
		                $body .= "<a href='" . URL . "orders-script/pay_overage.php?oid=$order_id&pay_overage=yes' target='_blank'><button style='border:0px solid #ccc;padding:10px 50px; background:darkgreen; cursor:pointer; color:white; font-weight:bold; font-size:14px'>Pay Now</button></a>";
		            } else {
		                $body .= "<strong>At your earliest convenience please process the additional payment by using the secure form on left side. Please proceed with Payment at your earliest convenience to avoid any interest and penalty fees.</strong><br><br>";
		            }
		        }
		        // $body .= "<h2 style='$headStyle'>Files Provided</h2>";
		        if ($user_type !== "account") {
		            // this is just for email
		            // $body .= "<p style='$bold'>You have uploaded the following files for translation</p>";
		        } else {
		            $body .= "<br>";
		        }
	    	}
		    if ($isAdmin) {
		        $followUp = ($hasPaid) ? "" : "<em style='color:darkblue; $bold'>NOTE: Please call client and follow up via E-mail. </em>";
		        $body .= "<h2>Order#$order_id  Status: $order_status</h2>$followUp";
		        $body .= "<h3 style='$headStyle'>Client Information</h3>
		            <table cellspacing='0' cellpadding='0' border='0' style='font-size:11px;'>
		            <tr><td width='80px' style='$bold' >Service:</td> <td> Interpretation</td></tr>
		            <tr><td style='$bold'>Type:</td> <td> $order</td></tr>
	            <tr><td style='$bold'>Name:</td> <td> $name </td></tr>
	            <tr><td style='$bold'>Business:</td> <td> $bname </td></tr>
	            <tr><td style='$bold'>Email:</td> <td> $email</td></tr>
	            <tr><td style='$bold'>Phone:</td> <td> $phone</td></tr>
	            </table>
	                  ";
	        $body .= $overage_info;
	        $body .= $onsite_contact_info;
	        $body .= $candidate_info;
	        $body .= $interpret_address . $description . " <h3 style='$headStyle'>Project Details</h3>" . $project_detail;
	        $body .= "<h3 style='$headStyle'>Order Total</h3>"
	                . "<span style='color:green;$bold'>+ $" . ($total_price) . " Subtotal</span> <br>";
	        $body .= $addons_price_admin;
	        $grand_total = ($view_type === "overage") ? $overage_amt : ($total_price + $addons_price + $additional_fee) - $discount;
	        $body .= "<p style='border-top:2px solid #ccc;'></p><span style='color:green;font-size:18px;$bold'>$" . amt_format($grand_total) . " Grand Total</span></br>";
	        $body .= ($view_type === "admin") ? "" : "<h3>Alliance Business Solutions LLC Order System</h3>";
	    }
	    // User Order Detail
	    if (!$isAdmin) {
	        $body .= $overage_info . $interpret_address . $onsite_contact_info . $candidate_info . $description;
	        $charge_msg = ($is_invoiced) ? "You will be invoiced at the end of the month for the noted order." : "Your credit card was charged as per the Order Summary below.";
	        // change the charge message if overage
	        if ($is_overage && $view_type === "overage") {
	            $CustLogin = get_customer($con, $customer_id);
	            $type = $CustLogin["Type"];
	            $invoicing_order = ($type == "1" || $type == "4") ? false : true;
	            // If customer is a client and invoicing then use different informations
	            if ($invoicing_order && $customer_id != "0") {
	                $charge_msg = "You will be invoiced accordingly for the overage time utilized, as per the Order Summary below.";
	            } else {
	                $charge_msg = "Your credit card was charged for the overage time utilized, as per the Order Summary below.";
	            }
	        }
	        $oid = ($user_type !== "account") ? "<em style='font-size:11px;color:green;$bold'>Project ID: $order_id</em>" : "";
	        $body .= "<br><h2 style='$bold;$headStyle'>Project Details</h2><br>";
	        if ($user_type !== "account") {
	            $body .= "<p style='font-weight:bold;'>$charge_msg</p>";
	        }
	        $body .= "<div style='border:1px solid #ccc; width:$width; border-radius:2px; padding:30px 10px; background:#F2F2F2'><span style='background:#fff; margin:20px; border-radius:5px 5px 0 0; padding:5px; font-weight:bold; font-size:16px'>Project Summary</span>" . $project_detail;
			// Display addon(s) only when the price of addons is more than 0
		        if ($addons_price > 0) {
		            $body .= "<br><span style='background:#fff; margin:20px; border-radius:5px 5px 0 0; padding:5px; font-weight:bold; font-size:16px'>Add-Ons</span><table width='100%'  cellspacing='0' cellpadding='0' border='0'  style='border-radius:10px; padding:10px; margin-top:0px; background:#fff'  id='addons'> <tr class = 'head'> <td $head_td_style width = '400px' ><label class = 'bold'>Type</label></td><td $head_td_style width = '50px' ><label class = 'bold'>Cost</label></td></tr>";
		            $body .= $addons_price_summary;
		            $body .= "</table>";
		        }
		        $body .= "<div><br><br><div><table width = '100%'><tbody><tr style = 'font-weight:bold'><td $foot_td_style >Price Summary</td></tr>";
		        // Add an overage price
	       	 	$overage_amt = 0;
	        	if ($is_overage && $view_type == "overage") {
		            $overage_amt = $overage_charge;
		            $body .= "<tr style = 'font-weight:bold'><td style = '$bold;font-size:12px;color:red' >+ $" . $overage_amt . " <img src='".HOME_SECURE."img/check.gif' width='13px' /> Overage Charges</td></tr>";
		       	} else {
		            $body .= "<tr style = 'font-weight:bold'><td style = '$bold;font-size:12px;' >+ $" . amt_format($total_price) . " <img src='".HOME_SECURE."img/check.gif' width='13px' /> Subtotal</td></tr>";
		            $additional_fee = $interpret_order["additional_fee"];
		            if ($additional_fee > 0) {
		                $additional_fee_desc = $interpret_order["additional_fee_desc"];
		                $body .= "<tr style = 'font-weight:bold'><td style = '$bold;font-size:12px;' >+ $" . amt_format($additional_fee) . " <img src='".HOME_SECURE."img/check.gif' width='13px' /> $additional_fee_desc</td></tr>";
		            }
		            if ($discount > 0) {
		                $body .= "<tr style = 'font-weight:bold'><td style = '$bold;font-size:12px;color:green' >- $" . amt_format($discount) . " <img src='".HOME_SECURE."img/check.gif' width='13px' /> Discount</td></tr>";
		            }
		            if ($is_overage && $is_overage_charged) {
		                $overage_amt = $overage_charge;
		                $body .= "<tr style = 'font-weight:bold'><td style = '$bold;font-size:12px;color:red' >+ $" . $overage_amt . " <img src='".HOME_SECURE."img/check.gif' width='13px' /> Overage Charges</td></tr>";
		            }
		        }
		        $grand_total = ($is_overage && $view_type == "overage") ? amt_format($overage_amt) : amt_format(($total_price + $addons_price + $additional_fee) - $discount + $overage_amt);
		        //$body .=$addons_price_list;
		        $body .="<tr style = 'font-weight:bold  !important; color:green !important'><td style = '$bold  !important;border-top:1px dashed #000  !important;color:green !important; ". "font-size:14px !important; padding-top:10px  !important' >$" . $grand_total . " Grand total</td></tr>";
		        $body .="</tbody></table></div><!--End of Order Summary -->";
		        if (!$user_type === "account") {
		            $body .= "<div style=' border:2px solid #ccc; background-color:#FFFFD4; margin:10px 0; width:500px; border-radius:10px; padding:0px 10px; '><p  style='$bold;font-size:
		            	20px;border-bottom:dotted 1px #ccc; color:darkgreen'><img src='".HOME_SECURE."img/admin/warning.gif' alt='' border='0' /> Notice</p> ". "<p style=''>- Scheduled on-site interpreter cannot be cancelled or rescheduled</p>". "<p style=''>- Location of the project is as per receipt and cannot be changed unless within the same 5 mile radius on the same date and time</p>". "<p style=''>- Overage time is billed at the Platinum Rate ($110 per hour) at the beginning of each hour</p>". "</div>";
		        }
				// Check is user is logged in or new register
		        if ($user_type === "login" || $user_type === "register") {
		            $body .="<br><p>You can view your order details by loging into your online portal by clicking at the link below.<br><a style = 'font-weight:bold' href = '".HOME_SECURE."linguist/clientportal/loginform.php'>".HOME_SECURE."linguist/clientportal/loginform.php</a></p><br>";
		        }
		        $avoid_penality = ($is_overage_charged == false && $want_to_pay_overage == false) ? " Please proceed with Payment at your earliest convenience to avoid any interest and penalty fees." : "";
		        // Don't display the footer in user portal while displaying order details
		        if ($user_type == "account") {
		            $body .=$avoid_penality;
		        } else {
		            $body .="<p  style='font-weight:bold'>NOTICE:  Please note that all of the on-site and phone interpreting / reading projects are subject to the Alliance Business Solutions Terms and Conditions which can be viewed by clicking on the link:<br><a href='http://www.allianinterpreter.com/en/terms-and-conditions' target='_blank' >http://www.allianinterpreter.com/en/terms-and-conditions</a></p><br></div>";
		        }
		        if ($user_type !== "account") {
		            $body .=get_footer();
		        }
		    }
		    $body .="</body></html>";
		    return $body;
	    }  else{ // order_id was greater than 0 or not equal to "0"
	        // if order_id was zero then do not display email contents
	        return "Order not found.";
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

		/* amt_format function is used to format the amount (price) value.
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
	return number_format($amt, $decimels, $decimel_point, $thousand_sep);
	}

	/*
	get_language_name function returns the records from LangList table of database
	against Language ID.
	@Param  $con: It is required to make connection with database. Required argument
	@Param  $langID: It is the Language Id against what the record/value is fetched and returned. Required argument
	@Param  $get: It is the actual record/value which is fetched and returned. Optional argument as it fetches LangName by default.

	Usage:
	1: Please press Ctrl+Shift+f
	2: A search Window asks to search specific function. You may search "get_language_name(" without double quotes and with opening parentheses.
	3: choose directory to search within and press "Find" Button
	4: The "Search Results" panel will search and display the pages where this functions has been used in the code.
	*/
	function get_language_name($con, $langID, $get = 'LangName') {
	    $get_lang_info = mysqli_query($con, "SELECT $get FROM `LangList` where LangId = $langID");
	    $lang = mysqli_fetch_array($get_lang_info);
	    $get = $lang[$get];
	    return $get;
	}

}