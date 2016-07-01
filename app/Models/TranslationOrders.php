<?php

namespace Allian\Models;

use Database\DataObject;
use Allian\Models\CustLogin;
use Database\Connect;

class TranslationOrders extends DataObject {

	protected $data = array(
	    "order_id" => "",
	    "order_type" => "",
	    "user_id" => "",
	    "invoice_id" => "",
	    "name" => "",
	    "business_name" => "",
	    "email" => "",
	    "phone" => "",
	    "file_ids" => "",
	    "total_units" => "",
	    "total_price" => "",
	    "dtp" => "",
	    "apostille" => "",
	    "rush_processing" => "",
	    "verbatim" => "",
	    "time_stamping" => "",
	    "time_stamping_type" => "",
	    "USPS" => "",
	    "copies_price" => "",
	    "international_shipping" => "",
	    "rp_delivery" => "",
	    "discount" => "",
	    "additional_fee" => "",
	    "additional_fee_desc" => "",
	    "is_charged" => "",
	    "stripe_id" => "",
	    "complete_percent" => "",
	    "status" => "",
	    "order_status" => "",
	    "order_time" => "",
	    "last_updated" => "",
	);

	/**
	 *
	 * // $name = $FName." ".$LName;
		// $user = CustomerID
		// $order_type = 5
		// $invoice_id = null
		// $bsn_name for me is null
		// $email, $phone
		// $file_ids = null
		// $total_units = 0
		// $total_price = možda amount
		// $status is 0, with time should be 7 . (0=Pending; 1=Paid; 2=Invoiced; 3=Abandoned; 4=Cancelled; 5=Deleted; 6=Card Declined; 7=Project Submitted)
		// order_time = date("Y-m-d H:i");
	 *
	 */
	public static function insertTransationOrder($sArray, $FName, $LName, $Email, $Phone){
		$con = Connect::con();
		$order_type = 5;
		$user =	$sArray['customer_id'];
		$invoice_id = '';
		$file_ids = '';
		$total_units = 0;
		$total_price = $sArray['amount'];
		$name =$FName . " " . $LName;
		$bsn_name = '';
		$email = $Email;
		$phone = $Phone;
		$order_time = date("Y-m-d H:i");
		$order_insert_query = "INSERT INTO " . getenv("TBL_TRANSLATION_ORDERS") . " (`order_type`, `user_id`, `invoice_id`, `name`,  `business_name`, `email`, `phone`, `file_ids`,`total_units`, `total_price`,`status`, `order_time`)VALUES('" . $order_type . "','" . $user . "','" . $invoice_id . "','" . $name . "','" . $bsn_name . "','" . $email . "','" . $phone . "','" . $file_ids . "','" . $total_units . "','" . $total_price . "','0','$order_time')";
			$order_inserted = mysqli_query($con, $order_insert_query);
			if ($order_inserted) {
				return mysqli_insert_id($con);
			}
		return false;
	}

	/**
	 *
	 * Block comment
	 *
	 */
	public static function updateTranslationOrdersSch($dArray){
		$con = Connect::con();
		try {
			$complete = mysqli_query($con, "UPDATE " . getenv("TBL_TRANSLATION_ORDERS") . " SET
				status = '1', stripe_id = '" . $dArray['stripe_id']
				//TODO status je 0 ako je charged korisnik, status 1 ako je cus_ kreiran ili, stripe_id mora i biti invoice, ako je 1. DAKLE POPRAVIT ZA STRIPE_ID I STATUS COLUMN
				. "',dtp='" . $dArray['DTP_Price'] // nevidim da se koristi
				. "',rush_processing='" . $dArray['RP_Price'] // nevidim da se koristi
				. "',verbatim='" . $dArray['Verbatim_Price'] // nevidim da se koristi
				. "',time_stamping='" . $dArray['TS_Price'] // nevidim da se koristi
				. "',time_stamping_type='" . $dArray['TS_Type'] // nevidim da se koristi
				. "',USPS='" . $dArray['usps_fee'] // nevidim da se koristi
				. "',apostille='" . $dArray['Apostille_Price'] // nevidim da se koristi
				 . "',international_shipping='" . $dArray['shipping_fee'] // nevidim da se koristi
				 . "', copies_price='" . $dArray['Copies_Price'] // nevidim da se koristi
				// . "'   '" $dArray['add_RP'] . // nevidim da se koristi
				."' WHERE order_id=" . $dArray['order_id']);
	    	if(!$complete){
	    		return false;
	    	}

	    	return $complete;
    	} catch(\Exception $e){
    		throw new \Exception("Problem with inserting order into translation order. Contact support.");
    	}
	}

	/**
	 *
	 * Block comment
	 *
	 */
	public static function getTranslationOrder($order_id, $get_value){
		$con = Connect::con();
	    $get_order_info = mysqli_query($con, "SELECT $get_value FROM " . getenv("TBL_TRANSLATION_ORDERS") . " WHERE order_id =  '$order_id'");
		$order = mysqli_fetch_array($get_order_info);
		$get = $order[$get_value];
		return ($get_value === "*") ? $order : $get;
	}

	/**
	 *
	 * Block comment
	 *
	 */
	public static function getTranslationOrders($CustomerID){
		$con = Connect::con();
		$telephonic_type = 5;
		// Return only those that are Paid, Invoiced & Project Submitted
		$project_query =  "SELECT * FROM " . getenv("TBL_TRANSLATION_ORDERS") . " WHERE user_id='$CustomerID' AND status  IN(1,2,7) AND order_type = '$telephonic_type'  ORDER BY `order_id` DESC";
		$result = mysqli_query($con, $project_query);
		return $result;
	}

}