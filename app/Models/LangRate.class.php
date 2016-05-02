<?php

require_once "/database/DataObject.class.php";

class LangRate extends DataObject {

	protected $data = array(
	    "PairID" => "",
	    "L1" => "",
	    "L2" => "",
	    "Rate" => "",
	);
}