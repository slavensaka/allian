<?php

namespace Allian\Models;

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

	public static function insertScheduleFinal($data){
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

	}


}