<?php

namespace Allian\Models;

use Database\DataObject;
use Allian\Models\LangList;

class LangPairTrans extends DataObject {

	protected $data = array(
	    "IPID" => "",
	    "Lang1" => "",
	    "Lang2" => "",
	    "Approved" => "",
	    'LangName' => "", // Dodano
 	);

 	public static function getLanguages() {
	    $conn = parent::connect();
	    $sql = "SELECT * FROM " . getenv('TBL_LANG_LIST') . " ORDER BY LangName";
	    try {
		    $st = $conn->prepare($sql);
		    $st->execute();
		    $langauges = array();
		    foreach ($st->fetchAll() as $row) {
		    	    $langauges[] = new LangList($row);
	      	}
		    parent::disconnect($conn);
		    return array($langauges);

		    	// foreach($row as $r){
		     //  		$message = self::retrieveLangPairTrans($r);
		     //  	}
		     //  	return $message;

		    // return false;
	    } catch ( \PDOException $e ) {
		      parent::disconnect( $conn );
		      throw new \Exception("Database retrieval problems experienced.");
	    }
  	}

  	public static function retrieveLangPairTrans($LangId){
  		$conn = parent::connect();
	    $sql = "SELECT DISTINCT LangPair_Trans.Lang2, LangList.LangName FROM LangPair_Trans LEFT JOIN LangList ON LangPair_Trans.Lang2 = LangList.LangId WHERE LangPair_Trans.Lang1 = '$LangId' AND Approved = 1 AND Lang2 IS NOT NULL AND Lang2 <> 'N/A' ORDER BY LangList.LangName";
	    try {
		    $st = $conn->prepare($sql);
		    $st->bindValue(":LangId", $LangId, \PDO::PARAM_INT);
		    $st->execute();
		    $translationTo = array();
		    foreach ($st->fetchAll() as $row) {
		      	$translationTo[] = new LangPairTrans($row);
	      	}
		    parent::disconnect($conn);
		    return array($translationTo);
	    } catch (\PDOException $e) {
		      parent::disconnect($conn);
		      throw new \Exception("Database retrieval problems experienced.");
	    }
  	}
}