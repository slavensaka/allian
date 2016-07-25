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
	    'LangName' => "", // Dodano
	);

  	/**
  	 *
  	 * Retrieve all the LandRate L1 langauges
  	 *
  	 */
  	public static function retrieveLangRateL1() {
		$conn = parent::connect();
	    $sql = "SELECT L1, LangList.LangName FROM LangRate LEFT JOIN LangList ON LangRate.L1 = LangList.LangId GROUP BY L1 ORDER BY LangList.LangName";
	     try {
		    $st = $conn->prepare($sql);
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

	/**
	 *
	 * Retrieve the second langauges based on the first
	 *
	 */
	public static function retrieveLangRateL2($L1) {
		$conn = parent::connect();
	    $sql = "SELECT L2 FROM LangRate WHERE L1='$L1'";
		 try {
		    $st = $conn->prepare($sql);
		    $st->bindValue(":L1", $L1, \PDO::PARAM_INT);
		    $st->execute();
		    $returnIt = array();
		    foreach($st->fetchAll() as $row) {
		      	$returnIt[] = new LangRate($row);
	      	}
		    parent::disconnect($conn);
		    return array($returnIt);
	    } catch (\PDOException $e) {
		      parent::disconnect($conn);
		      throw new \Exception("Database retrieval problems experienced.");
	    }
	}

}