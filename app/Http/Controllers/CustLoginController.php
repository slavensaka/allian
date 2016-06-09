<?php

namespace Allian\Http\Controllers;

use PHPMailer;
use \Dotenv\Dotenv;
use Firebase\JWT\JWT;
use RNCryptor\Encryptor;
use RNCryptor\Decryptor;
use Allian\Models\CustLogin;
use Firebase\JWT\DomainException;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\BeforeValidException;
use Allian\Http\Controllers\StripeController;

class CustLoginController extends Controller {

	 /**
     * @ApiDescription(section="Login", description="Authenticate customer with email & password, response is a jwt token with expiration date and secret key encoding for server side validation of autenticity of customer, and a data key that's encrypted with RNCryptor (status, userMessage, fname, lname, CustomerID). <br><b>Storing this token in the app is esential, this is the auth token needed througt the app.</b>(other routes require this token).")
     * @ApiMethod(type="post")
     * @ApiRoute(name="/testgauss/login")
     * @ApiBody(sample="{ 'data': 'AwGTfsuNPRIe9flrvJ4VlNhTUraZY67pp5FPPmym+ptwSCHO/jQkN97t5GB364Ac0zfd2dYsWJiXvawzmmCKSQm4OamHp5cMfjtsNirigo6AFJX4izmUgEcRmxx93W79B0DYpCNrtigYQcAPTF7uZKpiROIhXKBQyotWhyVxFXL9jPYXHtYUNa+uhrZ7r4CzIuI='}")
     * @ApiParams(name="data", type="object", nullable=false, description="Encrypted customers email & password as json used for authentication.")
     * @ApiReturnHeaders(sample="HTTP 200 OK")
     * @ApiReturn(type="object", sample="{'token': 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzUxMiJ9.eyJpYXQiOjE0NjM3ODQ3OTgsImp0aSI6InlMcG1ORWhcL0JDQ1l0MkhBeUtqT0NWVWMwSDF6ck5vdFE0UEJiMWVicHNjPSIsImlzcyI6ImxvY2FsaG9zdCIsIm5iZiI6MTQ2Mzc4NDc5OCwiZXhwIjoxNDY0OTk0Mzk4LCJkYXRhIjp7IlN1Y2Nlc3MiOiJTdWNjZXNzIn19.W7j7_NnUcTjsnvTU_pKMMTgJlQeP6pWvn9q7hwNZXStzc4MOm3Vgr7_7dvr-Boj5W-HWfqRelcFf8Rrao2Mz8A', 'data': 'AwGTanQjtgGJWgWHL1plL/j664tWA6W1atWNUo8QyiuuOdnwLE+NP/9bHQqhkyNttLTN5LGoCtuc59yaWDsn/k1IJJLkezVO/FZdQADjhbqYW5bUCXCntjWN3CkrI8JswcSbgFjpYhfrSmy4elc4KNSB0FD7NpH4KDIWQDxNQbXStReUodOeQXmuAHOwKejkf1Lf7qlUCwJsnvmh07UwKGwdN625xrCLB3dZxe60IYNcJcpvblVgI7NIsR4NsGndrkZU4XqVUt2DhlQhJjuPEr3U1Fp9ih10aWj4lUqq9t43jw=='
		}")
     */
	public function postLogin($request, $response, $service, $app) {
		//Decrypt input data
		$data = $this->decryptValues($request->data);
		// Validate input data
		$service->validate($data['email'], 'Invalid email address given.')->notNull()->isLen(3,200)->isEmail();
		$service->validate($data['password'], 'No password is present.')->notNull();
    	$email = $data['email'];
    	$password = $data['password'];
    	// Authenticate the user in the database
		$customer = CustLogin::authenticate($email, $password);
		// If error, return encrypted message
		if(!$customer){
			$base64Encrypted = $this->encryptValues(json_encode($this->errorJson("Authentication was unsuccessful, please try again.")));
     		return $response->json(array('data' =>$base64Encrypted));
		}
		// Generate token
		$genToken = $this->generateInfiniteToken(array("Success" => "Success"));
		// Store the newly created token
		$storeToken = $this->storeToken($genToken, $customer->getValueEncoded('CustomerID'));
		// If internal error, return encrypted message
		if(!$storeToken){
			$base64Encrypted = $this->encryptValues(json_encode($this->errorJson("Internal error. Contact support")));
     		return $response->json(array('data' =>$base64Encrypted));
		}
		// Format data for encryption
		$base64Encrypted = $this->encryptValues(json_encode($this->loginValues($customer)));
		// Make the response json
		$resp = array('token' => $genToken, 'data' => $base64Encrypted);
		// Return as json token and encrypted data
     	return $response->json($resp);
	}

	/**
     * @ApiDescription(section="Register", description="Register a new user in the database")
     * @ApiMethod(type="post")
     * @ApiRoute(name="/testgauss/register")
     * @ApiBody(sample="{ 'data': 'AwFzz+kLR6+kmfgeq7fR9qNY1pQy25i/hP2EyoMTArxZ47P+/K9ZgG5sZtGTuZCtoWwzzzWKsFwWu+1Tfy/7iJExIikIn1ZUEezlSS6vjtR+w81pxEngwFd6kplrVqi+uNqRZEOSasR6imanWoU59ADmCkzjP1cw24QT/Njkxq9MJmFDyAh0b4zrv7HY4FdwcbbBWa0tO2R/thyp30HOc6Ptl4r9eqtxaYJJnWWS8nZ5eiBDgDFo1BeS6LaRjpwLUtWoVqSnTiPgqEIqVWSkA7yYoCpOUDGZlmf1qFiPo8Fw9vD0xDJSfCA08G2wqj52WKE02civhufAnvzO7JmtizQ7OSs9aY+NdLxjbaoYKuFcKHszvV7meboPkdRumzWPocouL+GsLn6azZQx0dj2SVpCTWLnMKkuI8JwyneV4lsDevYv1/cEX4cgyMEAlnkjrquZadukfK4s5/miY6hED9g/anRV/sC3fCfEbkrvOouyLLmkSPlrrIWGs0zH9KlemI6J2JfjwY5e7byd0Hm5K7iGogukYEV3PqaGA/HkEk9YjX8DBixKD1Pvi+XbUSVwffiG7ogxTokJijZf8zK+OzxMwEt2UuzoxU0xjQmcf3FJx3YXAGxOmqb4FtPbiJvRy8+1OpfztkpBMX0tSS7ikSfy'}")
     * @ApiParams(name="data", type="object", nullable=false, description="Json must contain fname, lname, email, phone, password, phonePassword, services, sname, number, exp_month, exp_year, cvc.")
     * @ApiReturnHeaders(sample="HTTP 200 OK")
     * @ApiReturn(type="object", sample="{'data': 'AwHLdqaPYWUqunsX7Q7xnJ9ac0UzPgFo95bWo7kxZctQHXT6aqrgp1DuoEc7+MmpW/zFoj7BICGqcoBU+s49icpz2dTOQz14klgx/x+JQlU1Sp7fJOts1LEz7+takbBmHxmhuK3ulnxrf4BlpPXluNgg2y91HQ4AmqPfGKilkKWIilMRUFFzNoFVuQEideWzE8Q=',
     'token': 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzUxMiJ9.eyJpYXQiOjE0NjQ0ODcxODMsImp0aSI6IitFWmtsOGt4bUh1S29vd0JXVHJER1pLbGloVlRYWmM2TTlpZWRlK1lBbEk9IiwiaXNzIjoibG9jYWxob3N0IiwibmJmIjoxNDY0NDg3MTgzLCJleHAiOjE0NjU2OTY3ODMsImRhdGEiOnsiU3VjY2VzcyI6IlN1Y2Nlc3MifX0.IkCI0SUiNZpOOFR1gXMEW8xqDsd4KqPjGXTWNfutosjU0R_Z_qvuUJ3Z6257agQMp8jbCH8sLVhb7NNPaqY4Dw' }")
     */
	public function postRegister($request, $response, $service, $app) {
		//Decrypt input data
		$data = $this->decryptValues($request->data);
		// Validate input data
		$service->validate($data['fname'], 'Error: no first name is present.')->isLen(3,200)->notNull();
		$service->validate($data['lname'], 'Error: no last name is present.')->isLen(3,200)->notNull();
		$service->validate($data['email'], 'Invalid email address.')->notNull()->isLen(3,200)->isEmail();
		$service->validate($data['phone'], 'Invalid phone number.')->notNull();
		$service->validate($data['password'], 'Error: no password present.')->isLen(3,200)->notNull();
		$service->validate($data['phonePassword'], 'Error: no phone password present.')->isLen(3,200)->notNull()->isInt();
		$service->validate($data['services'], 'Error: no service present.')->notNull();
		$service->validate($data['sname'], 'Error: no stripe name present.')->notNull()->isLen(3,200);
		$service->validate($data['number'], 'Error: no credit card number present.')->notNull();
		$service->validate($data['exp'], 'Error: Expiration month not present.')->notNull();
		// $service->validate($data['exp_year'], 'Error: Expiration year not present.')->notNull();
		$service->validate($data['cvc'], 'Error: Cvc not present.')->notNull();

		// Try to register customer with inputed data
		$customer = CustLogin::register($data);
		// On error, return message
		if(!$customer){
			$base64Encrypted = $this->encryptValues(json_encode($this->errorJson("There was a problem during registration.")));
     		return $response->json(array('data' => $base64Encrypted));
		}
		// Stripe token then customer create
		$stripe = new StripeController();

		// Format exp_month and exp_year
		$exp = explode("/", $data['exp']);
		$exp_month = ltrim($exp[0], '0');
		$exp_year = "20".$exp[1];

		// Create a token of customer
		$tokenResult = $stripe->createTokenNew($data, $exp_month, $exp_year);
		// Create a stripe customer
		$stripeCustomer = $stripe->createCustomer($data['email'], $tokenResult);
		// Update user in database, store customer stripe token
		$updatedStripe = CustLogin::updateStripe($stripeCustomer, $customer->getValueEncoded('CustomerID'));
		// If error while updateding stripe token in database
		if(!$updatedStripe){
			// Remove customer from database
			$deleteCustomer = CustLogin::deleteCustomer($customer->getValueEncoded('CustomerID'));
			//Format json resonse
			$base64Encrypted = $this->encryptValues(json_encode($this->errorJson("There was a problem during stripe card registration.")));
     		return $response->json(array('data' => $base64Encrypted));
		}


		// Generate jwt token
		$genToken = $this->generateInfiniteToken(array("Success" => "Success"));
		// Store the newly created token
		$storeToken = $this->storeToken($genToken, $customer->getValueEncoded('CustomerID'));
		// If internal error, return encrypted message
		if(!$storeToken){
			$base64Encrypted = $this->encryptValues(json_encode($this->errorJson("Internal error. Contact support")));
     		return $response->json(array('data' => $base64Encrypted));
		}
		// Format response
		$jsonArray = array();
		$jsonArray['status'] = 1;
		$jsonArray['CustomerID'] = $customer->getValueEncoded('CustomerID');
		$fname = $customer->getValueEncoded('FName');
		$lname = $customer->getValueEncoded('LName');
		$jsonArray['userMessage'] = "Welcome $fname $lname.";
		$jsonArray['userMessage1'] = "Registration Successfull.";
		// Format data for encryption
		$base64Encrypted = $this->encryptValues(json_encode($jsonArray));
		// Make the response json
		$resp = array('token' => $genToken, 'data' => $base64Encrypted);
		// Return as json token and encrypted data
     	return $response->json($resp);
	}

	/**
     * @ApiDescription(section="Forgot", description="First check if email is found in the database, then generate a new password, then email the user the new password, and only then change database password to the new password.")
     * @ApiMethod(type="post")
     * @ApiRoute(name="/testgauss/forgot")
     * @ApiBody(sample="{'data': 'AwGkOu8KPYWUOJaJF5Bkvag/LmJHxUbIk9YvVvPj0pQTNFDj8Lku/ErqTvh/LLNzaL3XqA71uwhG9MPwF2CrweM+fLGaFNQSdu7lSHRb0DP4Ltaht+Fe3N56Szy5yHxZSM4wvlc+uqkwZLNEY66MI5NRGs5NRdmnjZnxDnUkERKV2g==',
     'token': 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzUxMiJ9.eyJpYXQiOjE0NjQ0ODc0ODUsImp0aSI6IndPSTlFRm9kY0RQVDdwc1d6RlphTUQ2dzlUMWhiSjUzV1BiSGxJSXFMZHc9IiwiaXNzIjoibG9jYWxob3N0IiwibmJmIjoxNDY0NDg3NDg1LCJleHAiOjE0NjU2OTcwODUsImRhdGEiOnsiU3VjY2VzcyI6IlN1Y2Nlc3MifX0.z03x_B2G6I3DdxaOsND3QMR0Rz8zCanxu6sf-4oH8x99x5nyvkhI0qClDMOzwXAC5ZU54D4OHgiJiiGoYU_4nQ'}")
     *@ApiParams(name="data", type="object", nullable=false, description="Json must contain email and CustomerID. Be carefull not to enter some real users email. Gmail server and stuff is live, it's on a staging server where no real users data is important when changed, but still, the gmail smtp server will send an email saying there pass was changed. Use YOUR OWN EMAIL in the data.")
     @ApiParams(name="token", type="object", nullable=false, description="Autentication token for users autentication.")
     * @ApiReturnHeaders(sample="HTTP 200 OK")
     * @ApiReturn(type="object", sample="{
     *  'data': 'AwF+61zGl0vFQTcyzyKhhuFJ8vFwOl8QNZsen0Gy+IgcKO6E6uoWa/XNxuXrcXbbHcJz7ZkfgJZcQKYm7OzXCkx2XgkpO18cBSxvXDfhxV9qLydXmjvxMpsFO0DV9H7zwylMTofFFJyTW00To1V3KVv9pbePNOhZeSTGmWSDpMHrOrYmH7qqGHPTz51NcoTqr3eFKPTFQAnyBizTM/yxRJiU/QYULs3TEAlH3xh9NCNJYEYZIcFQToJQFCqFWROv+zX9+KQt5TtFprHOazk7AUgH'
     * }")
     */
	public function postForgot($request, $response, $service, $app) {
		// Take care of token expiration, validity
		// if($request->token){
			// Validate token if not expired, or tampered with
			// $this->validateToken($request->token);
			//Decrypt input data
			$data = $this->decryptValues($request->data);
			// Validate input data
			$service->validate($data['email'], 'Please enter a valid email address to retrieve your password.')->isLen(3,200)->isEmail();
			// $service->validate($data['CustomerID'], 'Error: No customer id is present.')->notNull()->isInt();
			// Validate token in database for customer stored
			// $validated = $this->validateTokenInDatabase($request->token, $data['CustomerID']);
			// If error validating token in database
			// if(!$validated){
			// 	$base64Encrypted = $this->encryptValues(json_encode($this->errorJson("Authentication problems present")));
	  //    		return $response->json(array('data' => $base64Encrypted));
			// }
			// Check if email is found in the database
			$isFound = CustLogin::checkEmail($data['email']);
			// Error if no email was found in the database
			if(!$isFound){
				$errorJson = $this->encryptValues(json_encode($this->errorJson("Email address does not exist. Please enter correct email address to retrieve your password.")));
				return $response->json(array('data' => $errorJson));
			}
			// Retrieve the customer
			$customer = CustLogin::getCustomer1($data['email']);
			// Error if customer with CustomerID was not found in the database
			if(!$customer){
				$errorJson = $this->encryptValues(json_encode($this->errorJson("No user found with supplied email.")));
				return $response->json(array('data' => $errorJson));
			}
			// Generate a radnom password for the customer to email and store in the database
			$pass = $this->generatePassword();
			// Send an email with the new pass to the user
			$sentEmail = $this->newPassEmail($data['email'], $customer->getValueEncoded('FName'), $pass);
			// Error if sending of email failed
			if(!$sentEmail){
				$errorJson = $this->encryptValues(json_encode($this->errorJson("Error: Problem sending email to customer.")));
				return $response->json(array('data' => $errorJson));
			}
			// Insert the random generated password into the database for customer with CustomerID
			$insertPass = CustLogin::insertPass($pass, $customer->getValueEncoded('CustomerID'));
			// Error while inserting pass into database
			if(!$insertPass){
				$errorJson = $this->encryptValues(json_encode($this->errorJson("Couldn't update users password in database.")));
				return $response->json(array('data' => $errorJson));
			}
			// Format data for encryption
			$base64Encrypted = $this->encryptValues(json_encode($this->emailValues($customer)));
			// Return as json token and encrypted data
	     	return $response->json(array('data' => $base64Encrypted));
	    // } else {
	    //  	return $response->json("No token given");
	    // }
	}

	/**
     * @ApiDescription(section="ViewProfile", description="Retrieve customers information(only fname, lname, phone, phonePassword, email, services, telephoniUserID, telephonicPassword, type) stored in the database, based on the request CustomerID of user, a token for autentication.")
     * @ApiMethod(type="post")
     * @ApiRoute(name="/testgauss/viewProfile")
     * @ApiBody(sample="{'data': 'AwE9pWLBHNSOeTEz2Eyx536/giGgYqW/lzCyxq3r86DrvXA0rSegs70FRlcoHyzlhZSBK2OwBrIXSWogDr74mm3yw0WEtjI2u+YzK6Q2EenuCq/5uM5HUmmVThx/URapFX4=',
     'token': 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzUxMiJ9.eyJpYXQiOjE0NjQ0ODc0ODUsImp0aSI6IndPSTlFRm9kY0RQVDdwc1d6RlphTUQ2dzlUMWhiSjUzV1BiSGxJSXFMZHc9IiwiaXNzIjoibG9jYWxob3N0IiwibmJmIjoxNDY0NDg3NDg1LCJleHAiOjE0NjU2OTcwODUsImRhdGEiOnsiU3VjY2VzcyI6IlN1Y2Nlc3MifX0.z03x_B2G6I3DdxaOsND3QMR0Rz8zCanxu6sf-4oH8x99x5nyvkhI0qClDMOzwXAC5ZU54D4OHgiJiiGoYU_4nQ'}")
     *@ApiParams(name="data", type="object", nullable=false, description="Json must contain CustomerID.")
     @ApiParams(name="token", type="object", nullable=false, description="Autentication token for users autentication.")
     * @ApiReturnHeaders(sample="HTTP 200 OK")
     * @ApiReturn(type="object", sample="{
     *  'data': 'AwE9f9gpmJoh6/j011t1FRhb2rGuwfi8QRxDcDSjATaNgFJXNQXmbnjv732Qn4d6ORpTHBnSoVgqj1ix50wmUhh6IMlaVteR2nosOuplAIN45Mj984KlapZtRRFV1RoN6/1rVaV2gYnjFSqhwjjNm4byIaRCC7ASJiq6itEtYq8mh1Fk5ZE2FmA1IhyDamvTTdssikdnFtVgMuqPYUtI8LgDpr1ChIdWmitvbTiNhD6O03h8I0h9DXqgTIKeWwtDcXYcRu9wkjnGXAwEbqbUsrvn49BtmdENAx9J1ZlZbAEESbhp85EvymUOHsw4N3l9YsOja0V7IlzEttXYalw/DMvunFVYMvaDjbX3JLOkqxlAEMVOR7rhrSIeADAuTOrYVluwBaGp5LcqeWCa23EpaVxBxKM2CON3NUM1cd2naxBgLVHsEuMtGnJnsuQZ7/VYxvYE+4PrCsuCY745hxtSkUJEE7wxx+ilCdRY9vcLErMIYg=='
     * }")
     */
	public function viewProfile($request, $response, $service, $app){
		if($request->token){
			// Validate token if not expired, or tampered with
			$this->validateToken($request->token);
			// Decrypt input data
			$data = $this->decryptValues($request->data);
			// Validate input data
			$service->validate($data['CustomerID'], 'Error: No customer id is present.')->notNull()->isInt();
			//Validate the jwt token in the database
			$validated = $this->validateTokenInDatabase($request->token, $data['CustomerID']);
			// If error validating token in database
			if(!$validated){
				$base64Encrypted = $this->encryptValues(json_encode($this->errorJson("Authentication problems present")));
	     		return $response->json(array('data' => $base64Encrypted));
			}
			// Retrieve the customer
			$customer = CustLogin::getCustomer($data['CustomerID']);
			// Error if customer with CustomerID was not found in the database
			if(!$customer){
				$errorJson = $this->encryptValues(json_encode($this->errorJson("No user found with supplied id.")));
				return $response->json(array('data' => $errorJson));
			}
			// Format the json response
			$values = array();
			$values['fname'] = $customer->getValueEncoded('FName');
			$values['status'] = 1;
			$values['lname'] = $customer->getValueEncoded('LName');
			$values['telephonicUserId'] = $customer->getValueEncoded('PhLoginId');
			$values['phonePassword'] = $customer->getValueEncoded('PhPassword');
			$values['phone'] = $customer->getValueEncoded('Phone');
			$values['type'] = $customer->getValueEncoded('Type');
			$values['email'] = $customer->getValueEncoded('Email');
			$values['services'] = explode(":", $customer->getValueEncoded('Services'));
			// Format data for encryption
			$base64Encrypted = $this->encryptValues(json_encode($values));
			// Return response json
			return $response->json(array('data' => $base64Encrypted));
     	} else {
     		$base64Encrypted = $this->encryptValues(json_encode($this->errorJson("No token provided in request")));
     		return $response->json(array('data' => $base64Encrypted));
     	}
	}

	/**
     * @ApiDescription(section="UpdateProfile", description="Update customers profile information by giving the CustomerID to recognize him in the database and new info.")
     * @ApiMethod(type="post")
     * @ApiRoute(name="/testgauss/updateProfile")
     * @ApiBody(sample="{ 'data': 'AwFI1Szn84FEu2Qsivkw25sKBqkhIXRDhIoOh0ciG9xxEB3B7Qo7E7eDSyBdNOme++45Hz4hxySPWWRE0TRefQ6cq+T7oAAubWvC7G4XRMgDWPFPdJ2uv0Zlq7JTInDG8xG7w053p2NW0sJmfNPlbQzbNww3JmiW57tAFqhOfVKz6Ucbr2bpaOdL10PsPWkwUoHvjfslgOITIZArwMO2sY23XC5uxU2iZAOAAYqUUYY5KMiiMjl+RAYSgfTRrVR/wM7MOm4aADK+zGdOaMBULPCMOC/XQOcVekMqkYjk+jggXD2imqGVIV7g2edU5zl7n4qKI2MVjqQ2qUGgJS1kv+fyEekj+TIWdIO1hjW3HiAJTotesk+C0UZgI4qe1yHvicERvz8mcR5CCv5FxfL3YiTIllNbt9sJwcDvXANfeCBwhA==', 'token': 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzUxMiJ9.eyJpYXQiOjE0NjQ0ODc0ODUsImp0aSI6IndPSTlFRm9kY0RQVDdwc1d6RlphTUQ2dzlUMWhiSjUzV1BiSGxJSXFMZHc9IiwiaXNzIjoibG9jYWxob3N0IiwibmJmIjoxNDY0NDg3NDg1LCJleHAiOjE0NjU2OTcwODUsImRhdGEiOnsiU3VjY2VzcyI6IlN1Y2Nlc3MifX0.z03x_B2G6I3DdxaOsND3QMR0Rz8zCanxu6sf-4oH8x99x5nyvkhI0qClDMOzwXAC5ZU54D4OHgiJiiGoYU_4nQ'}")
     * @ApiParams(name="data", type="object", nullable=false, description="Json must contain CustomerID, fname, lname, email, phone, password, phonePassword, services. Example in body in the data.")
     @ApiParams(name="token", type="object", nullable=false, description="Autentication token for users autentication.")
     * @ApiReturnHeaders(sample="HTTP 200 OK")
     * @ApiReturn(type="object", sample="{
		'data': 'AwEMUdm0+s+VPN8+MJ5wQ2Xi8eu2dSTZPhRCMuSfk1uyQC+qlU2p6pu3tkdgD7WvQD5UthC8t2kwZqqTf5sDTCfvWL+MFzO7J3/4vAcoT97A+R5/Cnqj9n5tHrL5HIvNnBV2bWV8AGfgmvdOWO4XyrbwbpkM6okXczDYDCSlWsADJQ=='
		}")
     */
	public function updateProfile($request, $response, $service, $app){

		if($request->token){ //editmain.php i editmainadd.php
			// Validate token if not expired, or tampered with
			$this->validateToken($request->token);
			// Decrypt input data
			$data = $this->decryptValues($request->data);
			// Validate input data
			$service->validate($data['CustomerID'], 'Error: No customer id is present.')->notNull()->isInt();
			$service->validate($data['fname'], 'Error: no first name is present.')->notNull()->isLen(3,200);
			$service->validate($data['lname'], 'Error: no last name is present.')->notNull()->isLen(3,200);
			$service->validate($data['email'], 'Invalid email address.')->notNull()->isLen(3,200)->isEmail();
			$service->validate($data['phone'], 'Invalid phone number.')->notNull()->isLen(3,200);
			$service->validate($data['password'], 'Error: no password present.')->notNull()->isLen(3,200);
			$service->validate($data['phonePassword'], 'Error: no phone password present.')->notNull()->isLen(3,200)->isInt();
			$service->validate($data['services'], 'Error: no service present.')->notNull();
			// Validate token in database for customer stored
			$validated = $this->validateTokenInDatabase($request->token, $data['CustomerID']);
			// If error validating token in database
			if(!$validated){
				$base64Encrypted = $this->encryptValues(json_encode($this->errorJson("Authentication problems present")));
	     		return $response->json(array('data' => $base64Encrypted));
			}
			// Try to update customer with inputed data
			$updated = CustLogin::update($data);
			// If error updating
			if(!$updated){
				$base64Encrypted = $this->encryptValues(json_encode($this->errorJson("Profile not edited. Try Again. Please contact support if problem persists.")));
	     		return $response->json(array('data' => $base64Encrypted));
			}
			// Format response
			$jsonArray = array();
			$jsonArray['status'] = 1;
			$jsonArray['userMessage'] = "Profile edited successfully";
			// Format data for encryption
			$base64Encrypted = $this->encryptValues(json_encode($jsonArray));
			// Return as json token and encrypted data
	     	return $response->json(array('data' => $base64Encrypted));
     	} else {
     		$base64Encrypted = $this->encryptValues(json_encode($this->errorJson("No token provided in request")));
     		return $response->json(array('data' => $base64Encrypted));
     	}
	}

	/**
     * @ApiDescription(section="TelephonicAccess", description="Retrieve customers telephonicUserId registeredUserHostline & telephonicPassword for telephonic access. By giving the CustomerID.")
     * @ApiMethod(type="post")
     * @ApiRoute(name="/testgauss/telephonicAccess")
     @ApiBody(sample="{'data': 'AwGzeHMznmut3LsRZ5qLXL60XdsS8dY6g08KR7HuXgzFm7sMqm/eJRSkxUExI56Crtoi9DXPCgAIjKcw37WYLcF1iaJbXopvUgLC0KkWaFRaBXz0368T53jczZQHzHVKtpc=',
     'token': 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzUxMiJ9.eyJpYXQiOjE0NjQ0ODc0ODUsImp0aSI6IndPSTlFRm9kY0RQVDdwc1d6RlphTUQ2dzlUMWhiSjUzV1BiSGxJSXFMZHc9IiwiaXNzIjoibG9jYWxob3N0IiwibmJmIjoxNDY0NDg3NDg1LCJleHAiOjE0NjU2OTcwODUsImRhdGEiOnsiU3VjY2VzcyI6IlN1Y2Nlc3MifX0.z03x_B2G6I3DdxaOsND3QMR0Rz8zCanxu6sf-4oH8x99x5nyvkhI0qClDMOzwXAC5ZU54D4OHgiJiiGoYU_4nQ'}")
     * @ApiParams(name="data", type="object", nullable=false, description="Customers id. Stored in mobile phone, as this is the identifier for the user using the app.")
      @ApiParams(name="token", type="object", nullable=false, description="Autentication token for users autentication.")
     * @ApiReturnHeaders(sample="HTTP 200 OK")
     * @ApiReturn(type="object", sample="{
     *  'data':'AwEcWRJiA7jnmOvZjnE3EgGYHXRIGRO+1YIC+GBuUikQpt2V7PB5i+5xRtTxQTnBgfGQX23PjuRrI2b32/WjhYnuMJRce440j8KAWd5cEAdgp2/9Gr3bSVOLrrgusJj0+Afs1+DLLCV0dDooFjGMqXfBQFpBtm1G2Iz1p/MefoH+ZETcpzZURo9hNQ1ERe4mr2YpqoPwQmP6yy6B7y9lMinwk6ns+vyo0MunPMDlYFH4JqeLohUxNIZ3QctjP0YjXL8i9FkoOFb7K/nvDrfsoBVkHEQSwx+e3cbxVIzjAJXzBhKXdpIaQ3R6uFtPkao4+csuyW15NAU/5oYBJfpKW5tevs560Z5y1DsVXgfDHC0CnKzdD/l/BsDUmBz4oeOnDDHLECbebYUwbNfWtdd9F5ky/HHFU7o7bl/4WzcJivlZ4EMDwFb0Y75SJnr56t0b6v41WS5tH5RAuYwAFvJZf/ivLkfYGIxMpPwrWzYcf90qZTYf7YYCo+0Vh2fG0AuhEv5Ha0sfZKZ00QzoAIw9ejDgLpiySoY4egoivuTJUpL7lTAcB3lZvvQx4C0X07TdqOhqJx84ZbxlSKcRwqSIcozBwYQE+6brEhMWUAKuNbtF4PhTMLykNxSLfAvAj2w6j/PHx9v5T5CaSKgLtaIH3XzgFKlJfd/XUnLaphbOCt0VGfAHeGFQDZ8C6hbWHQVATED16RgVFiO61FLy3ARnhMsPT7X/IbcU6CwxWAtGKJX3MhuIb1cV60M8rQEDavhMo/Rg5xSeDpuhGFOeBEgHAkqjRPfe1qGrAFZeyDI+iQl24tjbsSwM3XgFK2m4COnVy2uygKLO2L4xWPgqnx1a+HQLSGhZSOGYClL+Cgy+KfW/QvX74c6YRAXqfpf52jcN3h3NN3X9/gtr1FNx9dARmQMaQuLPq3DycG9fnhUlnJXy/9qWBateUJG2o1A3zfVjRCd+h8qOy2a+pDwl4BH5hJbMVHqkqnALM1EG7v38F01o7mnCPYA4ja4d1dmFTWsN7dJIqkpSrAwXBugenLeBumeTah0UGb8aqA05WQE/QUWMvmt4E63aY1ia/Gc7yNbj+iXclcVLbqI/TU+nmYSZqmMOH6Se1JeTSXPHCyvtovQwA235a5L4sr4desUy6ZjjHDGtZJxt7ZlUu+qCEC9nw7iE8/grXkj2RBUPlUi4kt0J4WvWJ1yS2dK3csgn6jUoPoIk+fNs0HfmACSbnva4rxw+qAqQXnqAMWpxulAO3DczF4GG9ICKphYyqv0+1Nq0Xo+bxqL1KFkwmKZ4HNaKwwWDXvJK4XGv1iRqPYe+/jB/6A=='
     * }")
     *
     */
	public function telephonicAccess($request, $response, $service, $app){
		if($request->token){
			// Validate token if not expired, or tampered with
			$this->validateToken($request->token);
			//Decrypt input data
			$data = $this->decryptValues($request->data);
			// Validate input data
			$service->validate($data['CustomerID'], 'Error: No customer id is present.')->notNull()->isInt();
			// Validate token in database for customer stored
			$validated = $this->validateTokenInDatabase($request->token, $data['CustomerID']);
			// If error validating token in database
			if(!$validated){
				// $base64Encrypted = $this->encryptValues(json_encode($this->errorJson("Authentication problems present")));
	     		$base64Encrypted = $this->encryptValues(json_encode($this->errorJson("Authentication problems present")));
	     		return $response->json(array('data' => $base64Encrypted));
			}
			// Retrieve telephones and pictures
			// $t = include getcwd() . "/resources/assets/telAccess.php";
			$tel = array(
				 'http://alliantranscribe.com/img/us.png',
				 'http://alliantranscribe.com/img/canada.png',
				 'http://alliantranscribe.com/img/uk.png',
				 'http://alliantranscribe.com/img/australia.png',);
			$tele = array("1 855-733-6655"
					,"1 855-733-6655",
					"+44 800 802 1231",
					"+61 3 8609 8382",);

			// Retrieve the customer
			$customer = CustLogin::getCustomer($data['CustomerID']);
			//If error retrieving customer
			if(!$customer){
				$errorJson = $this->encryptValues(json_encode($this->errorJson("No user found with supplied id.")));
				return $response->json(array('data' => $errorJson));
			}
			//Create response array
			$jsonArray = array();
			$jsonArray['status'] = 1;
			$jsonArray['telephonicUserId'] = $customer->getValueEncoded('PhLoginId');
			$jsonArray['telephonicPassword'] = $customer->getValueEncoded('PhPassword');
			$jsonArray['registeredUserHotline'] = '1 855-733-6655'; //TODO make into dictionary, or dinamy
			// Encrypt fomrat json response
			$result = array_merge(array('flags' =>$tel), array('tel' =>$tele), $jsonArray);
			// $novi = array_merge(, $jsonArray);



			// Encrypt the data
			$base64Encrypted = $this->encryptValues(json_encode($result));
	     	return $response->json(array('data' => $base64Encrypted));
     	} else {
     		// $base64Encrypted = $this->encryptValues(json_encode($this->errorJson("No token provided in request")));
     		$base64Encrypted = $this->encryptValues(json_encode($this->errorJson("Authentication problems present")));
	     		return $response->json(array('data' => $base64Encrypted));

     	}
	}

	public function telephonicAccessEmail($request, $response, $service, $app){
		if($request->token){
			// Validate token if not expired, or tampered with
			$this->validateToken($request->token);
			//Decrypt input data
			$data = $this->decryptValues($request->data);
			// Validate input data
			$service->validate($data['CustomerID'], 'Error: No customer id is present.')->notNull()->isInt();
			// Validate token in database for customer stored
			$validated = $this->validateTokenInDatabase($request->token, $data['CustomerID']);
			// If error validating token in database
			if(!$validated){
				// $base64Encrypted = $this->encryptValues(json_encode($this->errorJson("Authentication problems present")));
	     		$base64Encrypted = $this->encryptValues(json_encode($this->errorJson("Authentication problems present")));
	     		return $response->json(array('data' => $base64Encrypted));
			}
			$customer = CustLogin::getCustomer($data['CustomerID']);
			return $customer->getValueEncoded('Email');
			//TODO
		} else {
     		// $base64Encrypted = $this->encryptValues(json_encode($this->errorJson("No token provided in request")));
     		$base64Encrypted = $this->encryptValues(json_encode($this->errorJson("Authentication problems present")));
	     		return $response->json(array('data' => $base64Encrypted));
     	}
	}

	/**
     * @ApiDescription(section="Terms", description="Render view for terms & conditions")
     * @ApiMethod(type="get")
     * @ApiRoute(name="/testgauss/terms")
     * @ApiReturnHeaders(sample="HTTP 200 OK")
     */
	public function getTerms($request, $response, $service, $app){
		$service->render('./resources/views/terms.html');
	}

	/**
     * @ApiDescription(section="Suport", description="Retrieve telephone and email for support")
     * @ApiMethod(type="get")
     * @ApiRoute(name="/testgauss/support")
     * @ApiReturnHeaders(sample="HTTP 200 OK")
     */
	public function support($request, $response, $service, $app){
		$r = array('tel' => "1 (877) 512 1195", "email" => "support@alliantransalte.com");
		return $response->json(array("data" => $r));
	}

	/**
     * @ApiDescription(section="Logout", description="Log out the customer. ")
     * @ApiMethod(type="post")
     * @ApiRoute(name="/testgauss/logout")
     @ApiBody(sample="{'data': 'AwEC/hx9UTeuTIk+Gv1B46wx0Z4/jrz9NWyOM+XDz+WZ+wDVOSiWvdhHbNbvLo8qxFsUJieK0Hx7KMJTMplGVo4J4fYdmlK8pDjbn3H/0rnkKWrEfgVEBQP1HUcWkzlQYP8=',
     'token': 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzUxMiJ9.eyJpYXQiOjE0NjQ1OTIzNTEsImp0aSI6IlZQTG9MT2hUN3ZoeVROTWxhd1FsZWxKY0x3YnFlS3hWZ2NZSVpCb1dPUkU9IiwiaXNzIjoibG9jYWxob3N0IiwibmJmIjoxNDY0NTkyMzUxLCJleHAiOjE1OTA3MzYzNTEsImRhdGEiOnsiU3VjY2VzcyI6IlN1Y2Nlc3MifX0.Z7QGqAP2P-aqLcwTrzyyKX0d0VNuLA6nK3Fycg8u_0xrOD5-4c94u9bejsWyAfB8wHCC1_CPKcLzZCk3gVbtZQ'}")
     * @ApiParams(name="data", type="object", nullable=false, description="Customers id. Stored in mobile phone, as this is the identifier for the user using the app.")
      @ApiParams(name="token", type="object", nullable=false, description="Autentication token for users autentication.")
     * @ApiReturnHeaders(sample="HTTP 200 OK")
     * @ApiReturn(type="object", sample="{
     *  'data':'AwETP+chxus+QxPWnTivcHRAS+Yz+Ro7bKqQJCGVNkqZIozIbZ8jligQWwSGlABFaFD1DqhLkg3fVO5Or3IH6gR2qmgoduVmqggk+mJGFyLwAgSXlIjfBokcVzN/ACmypbYhhlXssvNSCP1drNXREd9JSLXzVvGYWNLHd2jIVk3SOw==', 'token': 'null'
     * }")
     *
     */
	public function logout($request, $response, $service, $app){
		if($request->token){
			// Validate token if not expired, or tampered with
			$this->validateToken($request->token);
			//Decrypt input data
			$data = $this->decryptValues($request->data);
			// Validate input data
			$service->validate($data['CustomerID'], 'Error: No customer id is present.')->notNull()->isInt();
			// $service->validate($data['loggedIn'], 'Error: something went wrong.')->isInt()->notNull();
			// Validate token in database for customer stored
			$validated = $this->validateTokenInDatabase($request->token, $data['CustomerID']);
			// Error if validation of jwt token
			if(!$validated){
				$base64Encrypted = $this->encryptValues(json_encode($this->errorJson("Authentication problems present")));
	     		return $response->json(array('data' => $base64Encrypted));
			}
			// Null the jwt in the database for CustomerID
			$storeToken = $this->nullToken($data['CustomerID']);
			// Format the response
			$jsonArray = array();
			$jsonArray['status'] = 1;
			$jsonArray['userMessage'] = "You have successfully logged out!";
			// Encrypt format json response
			$base64Encrypted = $this->encryptValues(json_encode($jsonArray));
	     	return $response->json(array('data' => $base64Encrypted, 'token' => null));
		} else {
			$base64Encrypted = $this->encryptValues(json_encode($this->errorJson("No token provided in request")));
     		return $response->json(array('data' => $base64Encrypted));
		}
	}

	// data = keepLoggedIn = 1, CustomerID = 761
	// Ako je mob request $keepLoggedIn = 0 onda se postavi na forever i vrati $keepLoggedIn = 1
	// Ako je mob request $keepLoggedIn = 1 onda se postavi na 2 tjedna i vrati $keepLoggedIn = 0
	/**
     * @ApiDescription(section="KeepLoggedIn", description="Make the user be keept logged in. Or keep him default login of 2 weeks.")
     * @ApiMethod(type="post")
     * @ApiRoute(name="/testgauss/keepLoggedIn")
     @ApiBody(sample="{'data': 'AwEMfPjVMFk/pyyzzo04fZVEpASkjuKHze/IoHKL3vfaxzhaBtIFc2/bldxOOajQChYzFg3cz+l39p920D2Hdw2xvEWlqppodf56m/cqbknx5YgLPj/NslqXatMC9Re99TLNIARfbqQVFqSmDuhIUso6',
     'token': 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzUxMiJ9.eyJpYXQiOjE0NjQ1OTIyNTcsImp0aSI6IkFUWm1QMG9lamJteXhWRDVRV0Vmbm9NQjJcL0RSMmtlNHdPY3JxZVRLT2NBPSIsImlzcyI6ImxvY2FsaG9zdCIsIm5iZiI6MTQ2NDU5MjI1NywiZXhwIjoxNDY1ODAxODU3LCJkYXRhIjp7IlN1Y2Nlc3MiOiJTdWNjZXNzIn19.RWyj8hd7D6Ti-zB-N11u6VS6fE6H5zJKwn_OBfICjuvTfY778GNBE_WdZZQLNMArtNJweff2dlX3DGrTg2e5-Q'}")
     * @ApiParams(name="data", type="object", nullable=false, description="Customers id. Stored in mobile phone, as this is the identifier for the user using the app.")
      @ApiParams(name="token", type="object", nullable=false, description="Autentication token for users autentication.")
     * @ApiReturnHeaders(sample="HTTP 200 OK")
     * @ApiReturn(type="object", sample="{
     *  'token':'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzUxMiJ9.eyJpYXQiOjE0NjQ1OTIzNTEsImp0aSI6IlZQTG9MT2hUN3ZoeVROTWxhd1FsZWxKY0x3YnFlS3hWZ2NZSVpCb1dPUkU9IiwiaXNzIjoibG9jYWxob3N0IiwibmJmIjoxNDY0NTkyMzUxLCJleHAiOjE1OTA3MzYzNTEsImRhdGEiOnsiU3VjY2VzcyI6IlN1Y2Nlc3MifX0.Z7QGqAP2P-aqLcwTrzyyKX0d0VNuLA6nK3Fycg8u_0xrOD5-4c94u9bejsWyAfB8wHCC1_CPKcLzZCk3gVbtZQ',
     'data':'AwF+nSRYBu0vfhUssqcG6b0zfi332aCkZQ8rcQ4+pDw6zZcu54h+pqWAwYnB8IfimaqZ/VrEBdKgvS7JUAYuCVH2CCaGUmR9vUMqdFhzcedNrxpHuudHog6A6xkqw07IWlTI6+WTDg/qYMjBEwosS7oGSp+Sk/9RNFyE3CLfiSVHTmv4VZZyOMW1hp5+tYAp2CQ='
     * }")
     *
     */
	public function keepLoggedIn($request, $response, $service, $app){
		if($request->token){
			// Validate token if not expired, or tampered with
			$this->validateToken($request->token);
			//Decrypt input data
			$data = $this->decryptValues($request->data);
			// Validate input data
			$service->validate($data['keepLoggedIn'], 'Error: No keepLoggedIn flag present.')->notNull()->isInt();
			$service->validate($data['CustomerID'], 'Error: No customer id is present.')->notNull()->isInt();
			// Validate token in database for customer stored
			$validated = $this->validateTokenInDatabase($request->token, $data['CustomerID']);
			// Error if validation of jwt token
			if(!$validated){
				$base64Encrypted = $this->encryptValues(json_encode($this->errorJson("Authentication problems present")));
	     		return $response->json(array('data' => $base64Encrypted));
			}
			// If flag 1, user wants to be logged in forever
			if($data['keepLoggedIn']){
				// Generate 4 years token(i.e. forever), logged in
				$genToken = $this->generateInfiniteToken(array("Success" => "Success"));
				// Store the token in the database
				$storeToken = $this->storeToken($genToken, $data['CustomerID']);
				// If internal error, return encrypted message
				if(!$storeToken){
					$base64Encrypted = $this->encryptValues(json_encode($this->errorJson("Internal error. Contact support.")));
		     		return $response->json($base64Encrypted);
				}
				// Format the response
				$jsonArray = array();
				$jsonArray['status'] = 1;
				$jsonArray['keepLoggedIn'] = 1;
				$jsonArray['userMessage'] = "Success! Your kept logged in!";
				// Return that the user is keept logged in
				$base64Encrypted = $this->encryptValues(json_encode($jsonArray));
				return $response->json(array('token' => $genToken, 'data' => $base64Encrypted));
				// If flag 1, user wants to have an expiration time of token
			} else {
				// Generate ordinary 2 week token
				$genToken = $this->generateResponseToken(array("Success" => "Success"));
				// Store the token in the database
				$storeToken = $this->storeToken($genToken, $data['CustomerID']);
				// If internal error, return encrypted message
				if(!$storeToken){
					$base64Encrypted = $this->encryptValues(json_encode($this->errorJson("Internal error. Contact support")));
		     		return $response->json($base64Encrypted);
				}
				// Format the response
				$jsonArray = array();
				$jsonArray['status'] = 1;
				$jsonArray['keepLoggedIn'] = 0;
				$jsonArray['userMessage'] = "Success! Your Not kept logged in anymore!";
				// Return that the user is NOT keept logged in
				$base64Encrypted = $this->encryptValues(json_encode($jsonArray));
				return $response->json(array('token' => $genToken, 'data' => $base64Encrypted));
			}
		} else {
			$base64Encrypted = $this->encryptValues(json_encode($this->errorJson("No token provided in request")));
     		return $response->json(array('data' => $base64Encrypted));
		}
	}



}