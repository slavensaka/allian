<?php

namespace Allian\Models;

use Database\DataObject;
use Database\Connect;
use Allian\Models\LangList;

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


  	 public static function realLangPairTrans(){
  		$conn = parent::connect();
	    $sql = "SELECT DISTINCT LangRate.L2, LangList.LangName FROM LangRate LEFT JOIN LangList ON LangRate.L2 = LangList.LangId WHERE LangRate.L1 = '$LangId' ORDER BY LangList.LangName";
	    try {
		    $st = $conn->prepare($sql);
		    // $st->bindValue(":LangId", $LangId, \PDO::PARAM_INT);
		    $st->execute();
		    $translationTo = array();
		    foreach ($st->fetchAll() as $row) {
		      	$translationTo[] = new LangRate($row);
	      	}
		    parent::disconnect($conn);
		    return array($translationTo);
	    } catch (\PDOException $e) {
		      parent::disconnect($conn);
		      throw new \Exception("Database retrieval problems experienced.");
	    }
  	}
}