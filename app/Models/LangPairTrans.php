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
 	);

 	public static function retrieveLangPair() {
	    $conn = parent::connect();
	    $sql = "SELECT * FROM " . getenv('TBL_LANG_LIST') . " ORDER BY LangName";
	    try {
		    $st = $conn->prepare( $sql );
		    $st->execute();
		    $members = array();
		    foreach ( $st->fetchAll() as $row ) {
		    	    $members[] = new LangList( $row );
	      	}
		    parent::disconnect( $conn );
		    return array($members);

		    	// foreach($row as $r){
		     //  		$message = self::retrieveLangPairTrans($r);
		     //  	}
		     //  	return $message;

		    // return false;
	    } catch ( \PDOException $e ) {
		      parent::disconnect( $conn );
		      return false;
		      die( "Query failed: " . $e->getMessage() );
	    }
  	}

  	public static function retrieveLangPairTrans($LangId, $LangName){
  		$conn = parent::connect();
	    $sql = "SELECT DISTINCT langpair_trans.Lang2 AS lang2, langlist.LangName AS langName FROM langpair_trans LEFT JOIN langlist ON langpair_trans.Lang2 = langlist.LangId WHERE langpair_trans.Lang1 = '$LangId' AND Approved = 1 AND Lang2 IS NOT NULL AND Lang2 <> 'N/A' ORDER BY langlist.LangName";
	    try {
		      $st = $conn->prepare( $sql );
		      // $st->bindValue( ":startRow", $startRow, \PDO::PARAM_INT );
		      // $st->bindValue( ":numRows", $numRows, \PDO::PARAM_INT );
		      $st->execute();
		      $members = array();
		      foreach ( $st->fetchAll() as $row ) {
		    	    $members[] =$row;
	      	}
		      parent::disconnect( $conn );
		      return array( $LangName, $members );
	    } catch ( \PDOException $e ) {
		      parent::disconnect( $conn );
		      die( "Query failed: " . $e->getMessage() );
	    }
  	}
}