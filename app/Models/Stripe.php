<?php

namespace Allian\Models;

use Database\DataObject;

class Stripe extends DataObject {

	public static function customerToken($CustomerID){
		$conn = parent::connect();
		$sql = "SELECT token, Email FROM " . getenv('TBL_CUSTLOGIN') . " WHERE CustomerID= :CustomerID";
		try {
	    	$st = $conn->prepare( $sql );
  			$st->bindValue(":CustomerID", $CustomerID, \PDO::PARAM_STR);
  			$st->execute();
  			$success = $st->fetch();
  			parent::disconnect($conn);
  			if($success){
  				return $success;
  			} else {
  				return false;
  			}
	    } catch ( \PDOException $e ) {
	      parent::disconnect( $conn );
	      // return $e->getMessage();
	      return false;
	      exit;
	    }
	}
}