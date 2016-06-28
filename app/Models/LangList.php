<?php

namespace Allian\Models;

use Database\DataObject;
use Database\Connect;

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
		      throw new \Exception("Failed to retrieve langauges names from database");
	    }
  	}




  	public static function langIdByName($langName, $get = 'LangId') {
	    $con = Connect::con();
		$query = "SELECT $get FROM `LangList` where LangName LIKE  '%".trim($langName)."%'";
		$get_lang_info = mysqli_query($con, $query);
		$lang = mysqli_fetch_array($get_lang_info);
		$get = $lang[$get];
		return $get;
  	}


	/**
	 *
	 * Block comment
	 *
	 */
	function get_language_name($langID, $get = 'LangName') {
		$con = Connect::con();
	    $get_lang_info = mysqli_query($con, "SELECT $get FROM `LangList` where LangId = $langID");
	    $lang = mysqli_fetch_array($get_lang_info);
	    $get = $lang[$get];
	    return $get;
	}




}