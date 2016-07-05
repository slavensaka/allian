<?php

namespace Allian\Models;

use Database\DataObject;
use Database\Connect;

class LangRate extends DataObject {

	protected $data = array(
	    "PairID" => "",
	    "L1" => "",
	    "L2" => "",
	    "Rate" => "",
	);

	function selectLangRate1($l1, $l2) {
		$con = Connect::con();
	    $get = mysqli_query($con,"SELECT * FROM LangRate WHERE L1= '$l1' and L2='$l2' ");
	    $lang = mysqli_fetch_array($get);
	    return $lang;
	}

	function selectLangRate2($l1, $l2) {
		$con = Connect::con();
	    $get = mysqli_query($con,"SELECT * FROM LangRate WHERE L1= '$l2' and L2='$l1' ");
	    $lang = mysqli_fetch_array($get);
	    return $lang;
	}
}