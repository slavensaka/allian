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

	public static function insertTransationOrder($sArray){
		// $name = $FName." ".$LName;
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
		$con = Connect::con();

		$customer = CustLogin::getCustomer($sArray['customer_id']);

		$order_type = 5;
		$user =	$sArray['customer_id'];
		$invoice_id = '';
		$file_ids = '';
		$total_units = 0;
		$total_price = $sArray['amount'];
		$name =$customer->getValueEncoded('FName')." ".$customer->getValueEncoded('LName');
		$bsn_name = '';
		$email = $customer->getValueEncoded('Email');
		$phone = $customer->getValueEncoded('Phone');
		$order_time = date("Y-m-d H:i");
		$order_insert_query = "INSERT INTO " . getenv("TBL_TRANSLATION_ORDERS") . " (`order_type`, `user_id`, `invoice_id`, `name`,  `business_name`, `email`, `phone`, `file_ids`,`total_units`, `total_price`,`status`, `order_time`)VALUES('" . $order_type . "','" . $user . "','" . $invoice_id . "','" . $name . "','" . $bsn_name . "','" . $email . "','" . $phone . "','" . $file_ids . "','" . $total_units . "','" . $total_price . "','0','$order_time')";
			$order_inserted = mysqli_query($con, $order_insert_query);
			if ($order_inserted) {
				return mysqli_insert_id($con);
			}
		return false;


	}

	public static function updateTranslationOrdersSch($dArray){
		$con = Connect::con();

		$complete = mysqli_query($con, "UPDATE " . getenv("TBL_TRANSLATION_ORDERS") . " SET
			status = '1',
			stripe_id = '" . $dArray['stripe_id']
			. "',dtp='" . $dArray['DTP_Price']
			. "',rush_processing='" . $dArray['RP_Price']
			. "',verbatim=" . $dArray['Verbatim_Price']
			. ",time_stamping=" . $dArray['TS_Price']
			. ",time_stamping_type='" . $dArray['TS_Type']
			. "',USPS='" . $dArray['usps_fee']
			. "',apostille='" . $dArray['Apostille_Price']
			 . "',international_shipping='" . $dArray['shipping_fee']
			 . "', copies_price='" . $dArray['Copies_Price']
			// . "'   '" $dArray['add_RP'] .
			."' WHERE order_id=" . $dArray['order_id']);
    	if(!$complete){
    		return false;
    	}
    	return true;
	}

	public static function getTranslationOrder($order_id, $get_value){
		$con = Connect::con();
	    $get_order_info = mysqli_query($con, "SELECT $get_value FROM " . getenv("TBL_TRANSLATION_ORDERS") . " WHERE order_id =  '$order_id'");
		$order = mysqli_fetch_array($get_order_info);
		$get = $order[$get_value];
		// If whole record is required then return whole object
		return ($get_value === "*") ? $order : $get;
	}

	public static function getTranslationOrders($CustomerID){ // OVO
		$con = Connect::con();
		$project_query =  "SELECT * FROM " . getenv("TBL_TRANSLATION_ORDERS") . " WHERE user_id='$CustomerID' AND status  IN(1,2,7)  ORDER BY `order_id` DESC";
		$result = mysqli_query($con, $project_query);
		return $result;
	}

	// public static function getTranslationOrdersByOrderId($order_id){
	// 	$con = Connect::con();
	// 	$sql = "SELECT * FROM " . getenv("TBL_TRANSLATION_ORDERS") . " WHERE order_id = $order_id";
	// 	$result = mysqli_query($con, $sql);
	// 	$new = mysqli_fetch_array($result);
	// 	return $new;
	// }






}