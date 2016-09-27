<?php

namespace Allian\Models;

use Database\DataObject;
use Database\Connect;
use Allian\Helpers\PassHash;

class CustLogin extends DataObject {

	// Columns in database
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
  	 * With email and pass auth the customer
  	 * Check pass that's hashed and not hashed for old users
  	 *	@param string $Email Customers email address
  	 *  @param string $LoginPassword Customer Loginpassword
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
		    	// Check hashed passwords
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

  	/**
  	 *
  	 * Update customers password
  	 *	@param array $data['LoginPassword', 'CustomerID']
  	 */
  	public static function updatePassword($data){
  		$conn = parent::connect();
		$sql = "UPDATE " . getenv('TBL_CUSTLOGIN') . " SET LoginPassword = :LoginPassword WHERE CustomerID = :CustomerID";
		try {
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
	    	throw new \Exception("Internal error occurred 1. Please contact customer support at cs@alliantranslate.com and provide the error code. The error code is 1.");
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
  				throw new \Exception("There was a problem during registration. Please try again.");
  			}
	    } catch (\PDOException $e) {
	    	parent::disconnect($conn);
	    	if ($e->errorInfo[1] == 1062) {
    			throw new \Exception("Email already taken. Please try another email.");
			} else {
				throw new \Exception("Internal error occurred 2. Please contact customer support at cs@alliantranslate.com and provide the error code. The error code is 2.");
			}
	    }
  	}

  	/**
  	 *
  	 * Update the PhLoginId based on the way it's already used on the site.
	 * Used the CustomerID & const and add them together and update PhLoginId
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
	    	throw new \Exception("Internal error occurred code: 3. Please contact customer support at cs@alliantranslate.com and provide the error code. The error code is 3");
	    }
  	}

  	/**
  	 *
  	 * Delete the customer from the database
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
  	 * Used for easy customer deletion if registration all steps are not complete
  	 * If stripe fails, you need to delete him.
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
  	 * Used to update the jwt token
  	 * @param string $jwt_token
  	 * @param string $CustomerID
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

  	/**
  	 *
  	 * Null the jwtToken, that is update the token to null
  	 * meaning, user has logged out
  	 */
  	public static function nullToken($CustomerID){
  		$conn = parent::connect();
		$sql = "UPDATE " . getenv('TBL_CUSTLOGIN') . " SET jwt_token = :jwt_token WHERE CustomerID= :CustomerID";
		try {
	    	$st = $conn->prepare($sql);
  			$st->bindValue(":jwt_token", null, \PDO::PARAM_STR);
  			$st->bindValue(":CustomerID", $CustomerID, \PDO::PARAM_STR);
  			$success = $st->execute();
  			parent::disconnect($conn);
  			if($success){
  				return true;
  			} else {
  				return false;
  			}
	    } catch (\PDOException $e){
	      parent::disconnect($conn);
	      return false;
	    }
  	}

  	/**
  	 *
  	 * Reteieve the jwt_token from the database
  	 *
  	 */
  	public static function retrieveTokenInDatabase($CustomerID){
  		$conn = parent::connect();
		$sql = "SELECT jwt_token FROM " . getenv('TBL_CUSTLOGIN') . " WHERE CustomerID= :CustomerID";
		try {
	    	$st = $conn->prepare($sql);
  			$st->bindValue(":CustomerID", $CustomerID, \PDO::PARAM_STR);
  			$st->execute();
  			$success = $st->fetch();
  			parent::disconnect($conn);
  			if($success){
  				return $success;
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
  	 * On register, update the users stripe token in the database.
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
  	 * Update customers registration information
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
  	 * Used for forgotten password
  	 * Check that the email if found in database
  	 *
  	 */
  	public static function checkEmail($Email){
  		$conn = parent::connect();
  		$sql = "SELECT Email FROM " . getenv('TBL_CUSTLOGIN') . " WHERE Email=:Email";
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
  	 * get all customers information
  	 *
  	 */
  	public static function getCustomer($CustomerID) {
	    $conn = parent::connect();
	    $sql = "SELECT * FROM " . getenv('TBL_CUSTLOGIN') . " WHERE CustomerID = :CustomerID";
	    try {
		    $st = $conn->prepare($sql);
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
  	 * Get the customer from his emial for forgotten password
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
  	 * Hash and update the customer password
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
