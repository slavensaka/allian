<?php

require_once "/database/DataObject.class.php";

class LangList extends DataObject {

	protected $data = array(
	    "LangId" => "",
	    "LangName" => "",
	    "PhoneKey" => "",
	    "TierType" => "",
	    "TierType_Interpret" => "",
	);
}