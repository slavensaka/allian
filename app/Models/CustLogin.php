<?php

namespace Allian\Models;

use Database\DataObject;

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


  	public static function authenticate( $Email, $LoginPassword ) {
	    $conn = parent::connect();
	    $sql = "SELECT * FROM " . getenv('TBL_CUSTLOGIN') . " WHERE Email = :Email";
	    try {
		    $st = $conn->prepare( $sql );
		    $st->bindValue( ":Email", $Email, \PDO::PARAM_STR );
		    $st->execute();
		    $row = $st->fetch();
		    parent::disconnect( $conn );
		    if ( $row ) {
		    	if ($row['LoginPassword'] == $LoginPassword) {
		    		return new CustLogin( $row );
		    	}
		      	// return $row;
		    }
	    } catch ( \PDOException $e ) {
		      parent::disconnect( $conn );
		      die( "Query failed: " . $e->getMessage() );
	    }
  	}

  	public static function register($data){
  		$conn = parent::connect();
  		$sql = "SELECT PhLoginId FROM " . getenv('TBL_CUSTLOGIN') . " ORDER BY CustomerID DESC LIMIT 1";
  		$sql_1 = "INSERT INTO " . getenv('TBL_CUSTLOGIN') . "(FName, LName, Email, Phone, LoginPassword, PhPassword, PhLoginId) VALUES (:FName, :LName, :Email, :Phone, :LoginPassword, :PhPassword, :PhLoginId)";
  		try {
  			$last = $conn->prepare( $sql );
  			$last->execute();
  			$last_phloginid = $last->fetch();
  			parent::disconnect( $conn );
		    if ( $last_phloginid ) {
		    	$int_phloginid = (int)$last_phloginid['PhLoginId'];
		    	$new_phloginid = $int_phloginid + 1;
		    }
		} catch ( \PDOException $e ) {
	      	parent::disconnect( $conn );
	    }
	    try{
	    	$st = $conn->prepare( $sql_1 );
  			$st->bindValue( ":FName", $data['fname'], \PDO::PARAM_STR );
  			$st->bindValue( ":LName", $data['lname'], \PDO::PARAM_STR );
  			$st->bindValue( ":Email", $data['email'], \PDO::PARAM_STR );
  			$st->bindValue( ":Phone", $data['phone'], \PDO::PARAM_STR );
  			$st->bindValue( ":LoginPassword", $data['password'], \PDO::PARAM_STR );
  			$st->bindValue( ":PhPassword", $data['phone_password'], \PDO::PARAM_STR );
  			$st->bindValue( ":PhLoginId", $new_phloginid, \PDO::PARAM_INT );
  			// $st->bindValue( ":Services", $data['services'], \PDO::PARAM_STR );
  			$success = $st->execute();
  			parent::disconnect( $conn );
  			return $success;
	    } catch ( \PDOException $e ) {
	      parent::disconnect( $conn );
	      // $ra = array();
	      // $ra['status'] = 0;
	      // $ra['developerMessage'] = $e->getMessage();
	      // $ra['userMessage'] = "Error: Email already taken";
	      // return $ra;
	    }
  	}

  	public static function getMembers( $startRow, $numRows, $order ) {
	    $conn = parent::connect();
	    $sql = "SELECT SQL_CALC_FOUND_ROWS * FROM " . getenv('TBL_CUSTLOGIN') . " ORDER BY $order LIMIT :startRow, :numRows";

	    try {
		      $st = $conn->prepare( $sql );
		      $st->bindValue( ":startRow", $startRow, \PDO::PARAM_INT );
		      $st->bindValue( ":numRows", $numRows, \PDO::PARAM_INT );
		      $st->execute();
		      $members = array();
		      foreach ( $st->fetchAll() as $row ) {
		    	    $members[] = new CustLogin( $row );
	      	}
		      $st = $conn->query( "SELECT found_rows() AS totalRows" );
		      $row = $st->fetch();
		      parent::disconnect( $conn );
		      return array( $members, $row["totalRows"] );
	    } catch ( \PDOException $e ) {
		      parent::disconnect( $conn );
		      die( "Query failed: " . $e->getMessage() );
	    }
  	}

  	public static function getCustLogin( $CustomerID ) {
	    $conn = parent::connect();
	    $sql = "SELECT * FROM " . getenv('TBL_CUSTLOGIN') . " WHERE CustomerID = :CustomerID";
	    try {
		    $st = $conn->prepare( $sql );
		    $st->bindValue( ":CustomerID", $CustomerID, \PDO::PARAM_INT );
		    $st->execute();
		    $row = $st->fetch();
		    parent::disconnect( $conn );
		    if ( $row ) {
		      	// return new CustLogin( $row );
		      	return $row;
		    }
		      	// return new CustLogin( $row );
	    } catch ( \PDOException $e ) {
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
