<?php

require_once "/database/DataObject.class.php";

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
}