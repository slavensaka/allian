<?php

require_once "/database/DataObject.class.php";

use Dotenv\Dotenv;

class CustLogin extends DataObject {

	protected $data = array(
	    "CustomerID" => "",
	    "PhLoginId" => "",
	    "PhPassword" => "",
	    "FName" => "",
	    "LName" => "",
	    "BName" => "",
	    "Street" => "",
	    "Line2" => "",
	    "City" => "",
	    "State" => "",
	    "Postal" => "",
	    "Country" => "",
	    "Services" => "",
	    "InvName" => "",
	    "InvEmail" => "",
	    "InvPhone" => "",
	    "InvInvNeed" => "",
	    "Email" => "",
	    "LoginPassword" => "",
	    "Type" => "",
	    "token" => "",
	    "Phone" => "",
	    "totalcharhed" => "",
	    "totalbilled" => "",
	    "Saved" => "",
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

  	public static function getMembers( $startRow, $numRows, $order ) {
	    $conn = parent::connect();
	    $sql = "SELECT SQL_CALC_FOUND_ROWS * FROM " . getenv('TBL_CUSTLOGIN') . " ORDER BY $order LIMIT :startRow, :numRows";

	    try {
		      $st = $conn->prepare( $sql );
		      $st->bindValue( ":startRow", $startRow, PDO::PARAM_INT );
		      $st->bindValue( ":numRows", $numRows, PDO::PARAM_INT );
		      $st->execute();
		      $members = array();
		      foreach ( $st->fetchAll() as $row ) {
		    	    $members[] = new CustLogin( $row );
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

  	public static function getCustLogin( $CustomerID ) {
	    $conn = parent::connect();
	    $sql = "SELECT * FROM " . getenv('TBL_CUSTLOGIN') . " WHERE CustomerID = :CustomerID";
	    try {
		    $st = $conn->prepare( $sql );
		    $st->bindValue( ":CustomerID", $CustomerID, PDO::PARAM_INT );
		    $st->execute();
		    $row = $st->fetch();
		    parent::disconnect( $conn );
		    if ( $row ) {
		      	// return new CustLogin( $row );
		      	return $row;
		    }
		      	// return new CustLogin( $row );
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
