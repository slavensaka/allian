<?php

namespace Allian\Models;

use Database\DataObject;

class LangList extends DataObject {

	protected $data = array(
	    "LangId" => "",
	    "LangName" => "",
	    "PhoneKey" => "",
	    "TierType" => "",
	    "TierType_Interpret" => "",
	);

  	public static function langNames() {
	    $conn = parent::connect();
	     $sql = "SELECT * FROM " . getenv('TBL_LANG_LIST') . " ORDER BY LangName ASC";
	    try {
		      $st = $conn->prepare($sql);
		      $st->execute();
		      $langs = array();
		      foreach ($st->fetchAll() as $row) {
		    	    $langs[] = new LangList($row);
	      	}
		      parent::disconnect($conn);
		      return array($langs);
	    } catch (\PDOException $e) {
		      parent::disconnect($conn);
		      die("Query failed: " . $e->getMessage());
	    }
  	}




  	public static function langIdByName($LangName) {  // TODO
	    $conn = parent::connect();
	    // $sql = "SELECT * FROM " . getenv('TBL_LANG_LIST') . " WHERE LangName LIKE $LangName";
	    $sql = "SELECT * FROM" . getenv('TBL_LANG_LIST') . "where LangName LIKE  '%".trim($LangName)."%'";
	    try {
		 	$st = $conn->prepare($sql);
		 	$st->bindValue(":LangName", $LangName, \PDO::PARAM_STR);
		    $st->execute();
		    $row = $st->fetch();
		    parent::disconnect($conn);
		    if ($row) {
		      	return new LangList($row);
		    } else return false;
	    } catch (\PDOException $e) {
		      parent::disconnect($conn);
		      return false;
	    }
  	}




}