<?php

namespace Allian\Models;

use Database\DataObject;
use Database\Connect;
use Allian\Helpers\PassHash;

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
	    "jwt_token",
	    "deviceToken",
	);

  	/**
  	 *
  	 * Block comment
  	 *
  	 */
  	public static function authenticate($Email, $LoginPassword) {
	    $conn = parent::connect();
	    $sql = "SELECT * FROM " . getenv('TBL_CUSTLOGIN') . " WHERE Email = :Email";
	    try {
		    $st = $conn->prepare($sql);
		    $st->bindValue(":Email", $Email, \PDO::PARAM_STR);
		    $st->execute();
		    $row = $st->fetch();
		    parent::disconnect($conn);
		    if ($row) {
		    	// New hashed password
			    if(PassHash::checkPass($row['LoginPassword'], $LoginPassword)) {
				    return new CustLogin($row);
				 // Old customers with no secure password
				} elseif($row['LoginPassword'] == $LoginPassword) {
		    		return new CustLogin($row);
		    	} else {
				    return false;
				}
			}
	    } catch (\PDOException $e) {
		    parent::disconnect($conn);
		    throw new \Exception('Authentication failed.');
	    }
  	}

  	public static function updatePassword($data){
  		$conn = parent::connect();
		$sql = "UPDATE " . getenv('TBL_CUSTLOGIN') . " SET LoginPassword = :LoginPassword WHERE CustomerID = :CustomerID";
		try{
			$st = $conn->prepare($sql);
			$st->bindValue(":LoginPassword", PassHash::hash($data['LoginPassword']), \PDO::PARAM_STR);
			$st->bindValue(":CustomerID", $data['CustomerID'], \PDO::PARAM_STR);
			$success = $st->execute();
			parent::disconnect($conn);
  			if($success){
  				return true;
  			} else {
  				return false;
  			}
		} catch (\PDOException $e) {
	    	parent::disconnect($conn);
	    	throw new \Exception("Internal error occurred.");
	    }

  	}

  	/**
  	 *
  	 * Block comment
  	 *
  	 */
  	public static function register($data){
  		$conn = parent::connect();
  		$services = implode(":", $data['services']);
  		$sql_1 = "INSERT INTO " . getenv('TBL_CUSTLOGIN') . "(FName, LName, Email, Phone, LoginPassword, PhPassword, PhLoginId, Services, Type, Saved) VALUES (:FName, :LName, :Email, :Phone, :LoginPassword, :PhPassword, :PhLoginId, :Services, :Type, :Saved)";
	    try{
	    	$st = $conn->prepare($sql_1);
  			$st->bindValue(":FName", $data['fname'], \PDO::PARAM_STR);
  			$st->bindValue(":LName", $data['lname'], \PDO::PARAM_STR);
  			$st->bindValue(":Email", $data['email'], \PDO::PARAM_STR);
  			$st->bindValue(":Phone", $data['phone'], \PDO::PARAM_STR);
  			$st->bindValue(":LoginPassword", PassHash::hash($data['password']), \PDO::PARAM_STR);
  			$st->bindValue(":PhPassword", $data['phonePassword'], \PDO::PARAM_STR);
  			$st->bindValue(":PhLoginId", time(), \PDO::PARAM_INT);
  			$st->bindValue(":Services", $services, \PDO::PARAM_STR);
  			if (is_null($data['type'])) { $value = 1; } else { $value =  $data['type']; }
            $st->bindValue(":Type", $value, \PDO::PARAM_INT);
            $st->bindValue(":Saved", 1, \PDO::PARAM_INT);
  			$success = $st->execute();
  			parent::disconnect($conn);
  			if($success){
  				$customer = self::authenticate($data['email'], $data['password']);
  				$PhLoginId = $customer->getValueEncoded('CustomerID');
  				$update = self::updateRegLoginId($PhLoginId);
  				return $customer;
  			} else {
  				throw new \Exception("There was a problem during registration.");
  			}
	    } catch (\PDOException $e) {
	    	parent::disconnect($conn);
	    	if ($e->errorInfo[1] == 1062) {
    			throw new \Exception("Email already taken. Please try another email.");
			}
	    }
  	}

  	/**
  	 *
  	 * Block comment
  	 *
  	 */
  	public static function updateRegLoginId($PhLoginId){
  		$conn = parent::connect();
  		$CustomerID = $PhLoginId;
		$const = 112450;
		$login = $const + $CustomerID;
		$sql = "UPDATE " . getenv('TBL_CUSTLOGIN') . " SET PhLoginId = :login WHERE CustomerID = :CustomerID";
		try{
			$st = $conn->prepare($sql);
			$st->bindValue(":login", $login, \PDO::PARAM_STR);
			$st->bindValue(":CustomerID", $CustomerID, \PDO::PARAM_STR);
			$success = $st->execute();
			parent::disconnect($conn);
  			if($success){
  				return true;
  			}
		} catch (\PDOException $e) {
	    	parent::disconnect($conn);
	    	throw new \Exception("Internal error occurred.");
	    }
  	}

  	/**
  	 *
  	 * Block comment
  	 *
  	 */
  	public static function deleteCustomer($CustomerID){
  		$conn = parent::connect();
		$sql = "DELETE FROM " . getenv('TBL_CUSTLOGIN') . " WHERE CustomerID= :CustomerID";
		try {
	    	$st = $conn->prepare($sql);
  			$st->bindValue(":CustomerID", $CustomerID, \PDO::PARAM_STR);
  			$success = $st->execute();
  			parent::disconnect($conn);
  			if($success){
  				return true;
  			} else {
  				return false;
  			}
	    } catch (\PDOException $e) {
	      parent::disconnect($conn);
	      return false;
	    }
  	}

  	/**
  	 *
  	 * Block comment NOT USED
  	 *
  	 */
  	public static function deleteCustomerByEmail($Email, $LoginPassword){
  		$conn = parent::connect();
		$sql = "DELETE FROM " . getenv('TBL_CUSTLOGIN') . " WHERE Email= :Email AND LoginPassword= :LoginPassword AND token IS NULL";
		try {
	    	$st = $conn->prepare($sql);
  			$st->bindValue(":Email", $Email, \PDO::PARAM_STR);
  			$st->bindValue(":LoginPassword", $LoginPassword, \PDO::PARAM_STR);
  			$success = $st->execute();
  			parent::disconnect($conn);
  			if($success){ // TODO find if token is null, and password
  				return true;
  			} else {
  				return false;
  			}
	    } catch (\PDOException $e) {
	      parent::disconnect($conn);
	      return false;
	    }
  	}

  	/**
  	 *
  	 * Block comment
  	 *
  	 */
  	public static function updateToken($jwt_token, $CustomerID){
  		$conn = parent::connect();
		$sql = "UPDATE " . getenv('TBL_CUSTLOGIN') . " SET jwt_token = :jwt_token WHERE CustomerID= :CustomerID";
		try {
	    	$st = $conn->prepare($sql);
  			$st->bindValue(":jwt_token", $jwt_token, \PDO::PARAM_STR);
  			$st->bindValue(":CustomerID", $CustomerID, \PDO::PARAM_STR);
  			$success = $st->execute();
  			parent::disconnect($conn);
  			if($success){
  				return true;
  			} else {
  				return false;
  			}
	    } catch (\PDOException $e) {
	      parent::disconnect($conn);
	      return false;
	    }
  	}

  	public static function nullToken($CustomerID){
  		$conn = parent::connect();
		$sql = "UPDATE " . getenv('TBL_CUSTLOGIN') . " SET jwt_token = :jwt_token WHERE CustomerID= :CustomerID";
		try {
	    	$st = $conn->prepare( $sql );
  			$st->bindValue(":jwt_token", null, \PDO::PARAM_STR);
  			$st->bindValue(":CustomerID", $CustomerID, \PDO::PARAM_STR);
  			$success = $st->execute();
  			parent::disconnect($conn);
  			if($success){
  				return true;
  			} else {
  				return false;
  			}
	    } catch (\PDOException $e) {
	      parent::disconnect($conn);
	      return false;
	    }
  	}
  	/**
  	 *
  	 * Block comment
  	 *
  	 */
  	public static function retrieveTokenInDatabase($CustomerID){
  		$conn = parent::connect();
		$sql = "SELECT jwt_token FROM " . getenv('TBL_CUSTLOGIN') . " WHERE CustomerID= :CustomerID";
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
	      return false;
	      exit;
	    }
  	}

  	/**
  	 *
  	 * Block comment
  	 *
  	 */
  	public static function updateCustLoginStripe($stripeToken, $CustomerID){
  		$conn = parent::connect();
		$sql = "UPDATE " . getenv('TBL_CUSTLOGIN') . " SET token = :token WHERE CustomerID= :CustomerID";
		try {
	    	$st = $conn->prepare($sql);
  			$st->bindValue(":token", $stripeToken, \PDO::PARAM_STR);
  			$st->bindValue(":CustomerID", $CustomerID, \PDO::PARAM_STR);
  			$success = $st->execute();
  			parent::disconnect($conn);
  			if($success){
  				return true;
  			} else {
  				return false;
  			}
	    } catch (\PDOException $e) {
	      parent::disconnect($conn);
	      return false;
	    }
  	}

  	/**
  	 *
  	 * Block comment
  	 *
  	 */
  	public static function update($data){
  		$conn = parent::connect();
  		$services = implode(":", $data['services']);
		$sql = "UPDATE " . getenv('TBL_CUSTLOGIN') . " SET FName = :FName, LName = :LName, Email = :Email, Phone = :Phone , LoginPassword = :LoginPassword, PhPassword = :PhPassword, Services = :Services WHERE CustomerID= :CustomerID";
		try {
	    	$st = $conn->prepare($sql);
  			$st->bindValue(":FName", $data['fname'], \PDO::PARAM_STR);
  			$st->bindValue(":LName", $data['lname'], \PDO::PARAM_STR);
  			$st->bindValue(":Email", $data['email'], \PDO::PARAM_STR);
  			$st->bindValue(":Phone", $data['phone'], \PDO::PARAM_STR);
  			$st->bindValue(":LoginPassword", PassHash::hash($data['password']), \PDO::PARAM_STR);
  			$st->bindValue(":PhPassword", $data['phonePassword'], \PDO::PARAM_STR);
  			$st->bindValue(":CustomerID", $data['CustomerID'], \PDO::PARAM_INT);
  			$st->bindValue(":Services", $services, \PDO::PARAM_STR);
  			$success = $st->execute();
  			parent::disconnect($conn);
  			if($success){
  				return true;
  			} else {
  				return false;
  			}
	    } catch (\PDOException $e) {
	      	parent::disconnect($conn);
			throw new \Exception("Email address already taken. Try entering another email address.");
	    }
  	}

  	/**
  	 *
  	 * Block comment
  	 *
  	 */
  	public static function checkEmail($Email){
  		$conn = parent::connect();
  		$sql = "SELECT Email FROM " . getenv('TBL_CUSTLOGIN') . " WHERE Email=:Email";
  		// $sql = "SELECT Email FROM " . getenv('TBL_CUSTLOGIN') . " WHERE Email=:Email LIMIT 1";
  		 try {
		    $st = $conn->prepare($sql);
		    $st->bindValue(":Email", $Email, \PDO::PARAM_INT);
		    $st->execute();
		    $row = $st->fetch();
		    parent::disconnect($conn);
		    if ($row['Email'] == $Email) {
		      	return true;
		    } else
		    	return false;
	    } catch (\PDOException $e) {
		      parent::disconnect($conn);
		      return false;
	    }
  	}

  	/**
  	 *
  	 * Block comment
  	 *
  	 */
  	public static function getCustomer($CustomerID) {
	    $conn = parent::connect();
	    $sql = "SELECT * FROM " . getenv('TBL_CUSTLOGIN') . " WHERE CustomerID = :CustomerID";
	    try {
		    $st = $conn->prepare($sql );
		    $st->bindValue(":CustomerID", $CustomerID, \PDO::PARAM_INT);
		    $st->execute();
		    $row = $st->fetch();
		    parent::disconnect($conn);
		    if ($row) {
		      	return new CustLogin($row);
		    } else return false;
	    } catch (\PDOException $e) {
		      parent::disconnect($conn);
		      return false;
	    }
  	}

  	/*
		get_customer function returns the whole record against Customer ID (cid) from CustLogin database table
		@Param $con: Connection to database. Required Argument
		@Param $cid: Customer ID against what the record is returned. Required Argument
		Usage:
		1: Please press Ctrl+Shift+f
		2: A search Window asks to search specific function. You may search "get_interpret_order(" without double quotes and with opening parentheses.
		3: choose directory to search within and press "Find" Button
		4: The "Search Results" panel will search and display the pages where this functions has been used in the code.
	*/
	function get_customer($cid) {
		$con = Connect::con();
	    $get_cust_info = mysqli_query($con, "SELECT * FROM " . getenv('TBL_CUSTLOGIN') . " WHERE CustomerID =  '$cid'");
	    $cust = mysqli_fetch_array($get_cust_info);
	    return $cust;
	}

  	/**
  	 *
  	 * Block comment
  	 *
  	 */
  	public static function getCustomerByEmail($Email) {
	    $conn = parent::connect();
	    $sql = "SELECT * FROM " . getenv('TBL_CUSTLOGIN') . " WHERE Email = :Email";
	    try {
		    $st = $conn->prepare($sql);
		    $st->bindValue(":Email", $Email, \PDO::PARAM_INT);
		    $st->execute();
		    $row = $st->fetch();
		    parent::disconnect($conn);
		    if ($row) {
		      	return new CustLogin($row);
		    } else {
		    	return false;
		    }
	    } catch(\PDOException $e) {
		      parent::disconnect($conn);
		      return false;
	    }
  	}

  	/**
  	 *
  	 * Block comment
  	 *
  	 */
  	public static function insertPasswordCustLogin($LoginPassword, $CustomerID){
  		$conn = parent::connect();
  		$sql = "UPDATE " . getenv('TBL_CUSTLOGIN') . " SET LoginPassword=:LoginPassword WHERE CustomerID = :CustomerID";
  		try{
	    	$st = $conn->prepare($sql);
  			$st->bindValue(":LoginPassword", PassHash::hash($LoginPassword), \PDO::PARAM_STR);
  			$st->bindValue(":CustomerID", $CustomerID, \PDO::PARAM_INT);
  			$success = $st->execute();
  			parent::disconnect($conn);
  			return $success;
	    } catch (\PDOException $e) {
	      parent::disconnect($conn);
	      throw new \Exception("Problems with storing password. Try again!");
	    }
  	}

  	/**
  	 *
  	 * Block comment
  	 *
  	 */
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

  	/**
  	 *
  	 * Store the deviceToken in the database
  	 *
  	 */
  	public static function setDeviceToken($deviceToken, $CustomerID){
  		$conn = parent::connect();
		$sql = "UPDATE " . getenv('TBL_CUSTLOGIN') . " SET deviceToken = :deviceToken WHERE CustomerID= :CustomerID";
		try {
	    	$st = $conn->prepare($sql);
  			$st->bindValue(":deviceToken", $deviceToken, \PDO::PARAM_STR);
  			$st->bindValue(":CustomerID", $CustomerID, \PDO::PARAM_STR);
  			$success = $st->execute();
  			parent::disconnect($conn);
  			if($success){
  				return true;
  			} else {
  				return false;
  			}
	    } catch (\PDOException $e) {
	      parent::disconnect($conn);
	      return false;
	    }
  	}
}
