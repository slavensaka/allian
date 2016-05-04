<?php

namespace Allian\Models;

class CallIdentify extends DataObject {

	protected $data = array(
	    "UnqID" => "",
	    "Type" => "",
	    "starttime" => "",
	    "CustomerID" => "",
	    "FromNumber" => "",
	    "state" => "",
	    "duration" => "",
	    "IPID" => "",
	    "PairID" => "",
	    "Rate" => "",
	    "Charged" => "",
	    "Billed" => "",
	);

  	private $_genres = array(
	    "crime" => "Crime",
	    "horror" => "Horror",
	    "thriller" => "Thriller",
	    "romance" => "Romance",
	    "sciFi" => "Sci-Fi",
	    "adventure" => "Adventure",
	    "nonFiction" => "Non-Fiction"
  	);

  	public static function getCallIdenti( $startRow, $numRows, $order ) {
	    $conn = parent::connect();
	    $sql = "SELECT SQL_CALC_FOUND_ROWS * FROM " . TBL_MEMBERS . " ORDER BY $order LIMIT :startRow, :numRows";

	    try {
		      $st = $conn->prepare( $sql );
		      $st->bindValue( ":startRow", $startRow, PDO::PARAM_INT );
		      $st->bindValue( ":numRows", $numRows, PDO::PARAM_INT );
		      $st->execute();
		      $members = array();
		      foreach ( $st->fetchAll() as $row ) {
		    	    $members[] = new Member( $row );
	      	}
		      $st = $conn->query( "SELECT found_rows() AS totalRows" );
		      $row = $st->fetch();
		      parent::disconnect( $conn );
		      return array( $members, $row["totalRows"] );
	    } catch ( PDOException $e ) {
		      parent::disconnect( $conn );
		      die( "Query failed: " . $e->getMessage() );
	    }
  	}

  	public static function getCallIdentify( $UnqID ) {
	    $conn = parent::connect();
	    $sql = "SELECT * FROM " . getenv('TBL_CALLIDENTIFY') . " WHERE UnqID = :UnqID";

	    try {
		      $st = $conn->prepare( $sql );
		      $st->bindValue( ":UnqID", $UnqID, PDO::PARAM_INT );
		      $st->execute();
		      $row = $st->fetch();
		      parent::disconnect( $conn );
		      if ( $row ) return new CallIdentify( $row );
	    } catch ( PDOException $e ) {
		      parent::disconnect( $conn );
		      die( "Query failed: " . $e->getMessage() );
	    }
  	}

  	public function getGenderString() {
    	return ( $this->data["gender"] == "f" ) ? "Female" : "Male";
  	}

  	public function getFavoriteGenreString() {
    	return ( $this->_genres[$this->data["favoriteGenre"]] );
  	}
}
