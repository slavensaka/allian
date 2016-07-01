<?php

namespace Allian\Http\Controllers;

use PHPMailer;
use \Dotenv\Dotenv;
use Firebase\JWT\JWT;
use RNCryptor\Encryptor;
use RNCryptor\Decryptor;
use Allian\Helpers\ArrayValues;
use Allian\Helpers\Mail;
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
     * @ApiBody(sample="{ 'data': {
	    'email': 'slavensakacic@gmail.com',
	    'password': '12345'
  		}}")
     * @ApiParams(name="data", type="string", nullable=false, description="Encrypted customers email & password as json used for authentication.")
     * @ApiReturnHeaders(sample="HTTP 200 OK")
     * @ApiReturn(type="string", sample="{'token': 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzUxMiJ9.eyJpYXQiOjE0NjM3ODQ3OTgsImp0aSI6InlMcG1ORWhcL0JDQ1l0MkhBeUtqT0NWVWMwSDF6ck5vdFE0UEJiMWVicHNjPSIsImlzcyI6ImxvY2FsaG9zdCIsIm5iZiI6MTQ2Mzc4NDc5OCwiZXhwIjoxNDY0OTk0Mzk4LCJkYXRhIjp7IlN1Y2Nlc3MiOiJTdWNjZXNzIn19.W7j7_NnUcTjsnvTU_pKMMTgJlQeP6pWvn9q7hwNZXStzc4MOm3Vgr7_7dvr-Boj5W-HWfqRelcFf8Rrao2Mz8A', 'data': {
	    'CustomerID': '800',
	    'status': '1',
	    'email': 'slavensakacic@gmail.com',
	    'phonePassword': '45435',
	    'userMessage': 'Welcome.'
  			}
		}")
    */
	public function postLogin($request, $response, $service, $app) { // DONT CHANGE
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
     * @ApiBody(sample="{ 'data': {
		    'fname': 'Slaven',
		    'lname': 'Sakacic',
		    'email': 'njoke@gmail.com',
		    'phone': '773-732-6534',
		    'password': '12345',
		    'phonePassword': '45435',
		    'services': [
		      'Telephonic Interpreting',
		      'Translation Services',
		      'On-Site Interpreting',
		      'Transcription Services'
		    ],
		    'sname': 'Pero Perić',
		    'number': '4242424242424242',
		    'exp': '05/18',
		    'cvc': '314'
		  }}")
	     * @ApiParams(name="data", type="string", nullable=false, description="Json must contain fname, lname, email, phone, password, phonePassword, services, sname, number, exp_month, exp_year, cvc.")
	     * @ApiReturnHeaders(sample="HTTP 200 OK")
	     * @ApiReturn(type="string", sample="{'data': {
	    'status': '1',
	    'CustomerID': '801',
	    'userMessage': 'Welcome Slaven Sakacic.',
	    'userMessage1': 'Registration Successfull.'
	  },
	     'token': 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzUxMiJ9.eyJpYXQiOjE0NjQ0ODcxODMsImp0aSI6IitFWmtsOGt4bUh1S29vd0JXVHJER1pLbGloVlRYWmM2TTlpZWRlK1lBbEk9IiwiaXNzIjoibG9jYWxob3N0IiwibmJmIjoxNDY0NDg3MTgzLCJleHAiOjE0NjU2OTY3ODMsImRhdGEiOnsiU3VjY2VzcyI6IlN1Y2Nlc3MifX0.IkCI0SUiNZpOOFR1gXMEW8xqDsd4KqPjGXTWNfutosjU0R_Z_qvuUJ3Z6257agQMp8jbCH8sLVhb7NNPaqY4Dw' }")
     */
	public function postRegister($request, $response, $service, $app) { // DONT CHANGE
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
		// $customer = CustLogin::register($data);
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
		$updatedStripe = CustLogin::updateCustLoginStripe($stripeCustomer, $customer->getValueEncoded('CustomerID'));
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
     * @ApiBody(sample="{'data': {
	    'email': 'slavensakacic@gmail.com'
 		 }
 		 }")
     *@ApiParams(name="data", type="string", nullable=false, description="Json must contain email and CustomerID. Be carefull not to enter some real users email. Gmail server and stuff is live, it's on a staging server where no real users data is important when changed, but still, the gmail smtp server will send an email saying there pass was changed. Use YOUR OWN EMAIL in the data.")
     @ApiParams(name="token", type="string", nullable=false, description="Autentication token for users autentication.")
     * @ApiReturnHeaders(sample="HTTP 200 OK")
     * @ApiReturn(type="string", sample="{
     *  'data': {
	    'status': '1',
	    'userMessage': 'New password has been sent to your e-mail address. Please check your e-mail to retrieve your password.'
 		 }
     * }")
     */
	public function postForgot($request, $response, $service, $app) { // DONT CHANGE
		//Decrypt input data
		$data = $this->decryptValues($request->data);
		// Validate input data
		$service->validate($data['email'], 'Please enter a valid email address to retrieve your password.')->isLen(3,200)->isEmail();
		// Check if email is found in the database
		$isFound = CustLogin::checkEmail($data['email']);
		// Error if no email was found in the database
		if(!$isFound){
			$errorJson = $this->encryptValues(json_encode($this->errorJson("Email address does not exist. Please enter correct email address to retrieve your password.")));
			return $response->json(array('data' => $errorJson));
		}
		// Retrieve the customer
		$customer = CustLogin::getCustomerByEmail($data['email']);
		// Error if customer with CustomerID was not found in the database
		if(!$customer){
			$errorJson = $this->encryptValues(json_encode($this->errorJson("No user found with supplied email.")));
			return $response->json(array('data' => $errorJson));
		}
		// Generate a radnom password for the customer to email and store in the database
		$pass = $this->generatePassword();
		// Send an email with the new pass to the user
		$server = $this->serverEnv();
		if($server=="localhost"){
			$sentMail = Mail::newPassEmail($data['email'], $customer->getValueEncoded('FName'), $pass);
		} else if($server=="alliantranslate"){
			$sentMail = Mail::newPassProduction($data['email'], $customer->getValueEncoded('FName'), $pass);
		}
		// Error if sending of email failed
		if(!$sentEmail){
			$errorJson = $this->encryptValues(json_encode($this->errorJson("Error: Problem sending email. Contact support!")));
			return $response->json(array('data' => $errorJson));
		}
		// Insert the random generated password into the database for customer with CustomerID
		$insertPass = CustLogin::insertPasswordCustLogin($pass, $customer->getValueEncoded('CustomerID'));
		// Error while inserting pass into database
		if(!$insertPass){
			$errorJson = $this->encryptValues(json_encode($this->errorJson("Couldn't update users password in database.")));
			return $response->json(array('data' => $errorJson));
		}
		// Format data for encryption
		$base64Encrypted = $this->encryptValues(json_encode($this->emailValues($customer)));
		// Return as json token and encrypted data
     	return $response->json(array('data' => $base64Encrypted));
	}

	/**
     * @ApiDescription(section="ViewProfile", description="Retrieve customers information(only fname, lname, phone, phonePassword, email, services, telephoniUserID, telephonicPassword, type) stored in the database, based on the request CustomerID of user, a token for autentication.")
     * @ApiMethod(type="post")
     * @ApiRoute(name="/testgauss/viewProfile")
     * @ApiBody(sample="{'data': {
    	'CustomerID': '800'
  		},
     'token': 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzUxMiJ9.eyJpYXQiOjE0NjQ0ODc0ODUsImp0aSI6IndPSTlFRm9kY0RQVDdwc1d6RlphTUQ2dzlUMWhiSjUzV1BiSGxJSXFMZHc9IiwiaXNzIjoibG9jYWxob3N0IiwibmJmIjoxNDY0NDg3NDg1LCJleHAiOjE0NjU2OTcwODUsImRhdGEiOnsiU3VjY2VzcyI6IlN1Y2Nlc3MifX0.z03x_B2G6I3DdxaOsND3QMR0Rz8zCanxu6sf-4oH8x99x5nyvkhI0qClDMOzwXAC5ZU54D4OHgiJiiGoYU_4nQ'}")
     *@ApiParams(name="data", type="string", nullable=false, description="Json must contain CustomerID.")
     @ApiParams(name="token", type="string", nullable=false, description="Autentication token for users autentication.")
     * @ApiReturnHeaders(sample="HTTP 200 OK")
     * @ApiReturn(type="string", sample="{
     *  'data': {
	    'fname': 'Slaven',
	    'status': 1,
	    'lname': 'Sakacic',
	    'telephonicUserId': '113167',
	    'phonePassword': '45435',
	    'phone': '773-732-6534',
	    'type': '1',
	    'email': 'slavensakacic@gmail.com',
	    'services': [
	      'Telephonic Interpreting',
	      'Translation Services',
	      'On-Site Interpreting',
	      'Transcription Services'
	    ]
	  }
     * }")
     */
	public function viewProfile($request, $response, $service, $app){ // DONT CHANGE
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
				$base64Encrypted = $this->encryptValues(json_encode($this->errorJson("Authentication problems. CustomerID doesn't match that with token..")));
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
     * @ApiBody(sample="{ 'data': {
	    'CustomerID': '800',
	    'fname': 'Sla',
	    'lname': 'Saka',
	    'email': 'sakacic@gmail.com',
	    'phone': '111-111-111',
	    'password': '22222223',
	    'phonePassword': '11111',
	    'services': [
	      'Translation Services',
	      'On-Site Interpreting'
		    ]
		  }, 'token': 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzUxMiJ9.eyJpYXQiOjE0NjQ0ODc0ODUsImp0aSI6IndPSTlFRm9kY0RQVDdwc1d6RlphTUQ2dzlUMWhiSjUzV1BiSGxJSXFMZHc9IiwiaXNzIjoibG9jYWxob3N0IiwibmJmIjoxNDY0NDg3NDg1LCJleHAiOjE0NjU2OTcwODUsImRhdGEiOnsiU3VjY2VzcyI6IlN1Y2Nlc3MifX0.z03x_B2G6I3DdxaOsND3QMR0Rz8zCanxu6sf-4oH8x99x5nyvkhI0qClDMOzwXAC5ZU54D4OHgiJiiGoYU_4nQ'}")
	     * @ApiParams(name="data", type="string", nullable=false, description="Json must contain CustomerID, fname, lname, email, phone, password, phonePassword, services. Example in body in the data.")
	     @ApiParams(name="token", type="string", nullable=false, description="Autentication token for users autentication.")
	     * @ApiReturnHeaders(sample="HTTP 200 OK")
	     * @ApiReturn(type="string", sample="{
			'data': {
	    'status': 1,
	    'userMessage': 'Profile edited successfully'
	  	}
		}")
     */
	public function updateProfile($request, $response, $service, $app){ // DONT CHANGE

		if($request->token){ //editmain.php i editmainadd.php
			// Validate token if not expired, or tampered with
			$this->validateToken($request->token);
			// Decrypt input data
			$data = $this->decryptValues($request->data);
			// Validate input data
			$service->validate($data['CustomerID'], 'Error: No customer id is present.')->notNull()->isInt();
			$service->validate($data['fname'], 'Error: no first name is present.')->isLen(3,200);
			$service->validate($data['lname'], 'Error: no last name is present.')->isLen(3,200);
			$service->validate($data['email'], 'Invalid email address.')->isLen(3,200)->isEmail();
			$service->validate($data['phone'], 'Invalid phone number.')->isLen(3,200);
			$service->validate($data['password'], 'Error: no password present.')->isLen(3,200);
			$service->validate($data['phonePassword'], 'Error: no phone password present.')->isLen(3,200)->isInt();
			$service->validate($data['services'], 'Error: no service present.');
			// Validate token in database for customer stored
			$validated = $this->validateTokenInDatabase($request->token, $data['CustomerID']);
			// If error validating token in database
			if(!$validated){
				$base64Encrypted = $this->encryptValues(json_encode($this->errorJson("Authentication problems. CustomerID doesn't match that with token..")));
	     		return $response->json(array('data' => $base64Encrypted));
			}
			// Try to update customer with inputed data
			$updated = CustLogin::update($data);
			// If error updating
			if(!$updated){
				$base64Encrypted = $this->encryptValues(json_encode($this->errorJson("Profile not updated. Please, try Again. Please contact support if problem persists.")));
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
     @ApiBody(sample="{'data': {
    	'CustomerID': '800'
  		},
		     'token': 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzUxMiJ9.eyJpYXQiOjE0NjQ0ODc0ODUsImp0aSI6IndPSTlFRm9kY0RQVDdwc1d6RlphTUQ2dzlUMWhiSjUzV1BiSGxJSXFMZHc9IiwiaXNzIjoibG9jYWxob3N0IiwibmJmIjoxNDY0NDg3NDg1LCJleHAiOjE0NjU2OTcwODUsImRhdGEiOnsiU3VjY2VzcyI6IlN1Y2Nlc3MifX0.z03x_B2G6I3DdxaOsND3QMR0Rz8zCanxu6sf-4oH8x99x5nyvkhI0qClDMOzwXAC5ZU54D4OHgiJiiGoYU_4nQ'}")
		     * @ApiParams(name="data", type="string", nullable=false, description="Customers id. Stored in mobile phone, as this is the identifier for the user using the app.")
		      @ApiParams(name="token", type="string", nullable=false, description="Autentication token for users autentication.")
		     * @ApiReturnHeaders(sample="HTTP 200 OK")
		     * @ApiReturn(type="string", sample="{
		     *  'data': {
		    'flags': [
		      'http://alliantranscribe.com/img/us.png',
		      'http://alliantranscribe.com/img/canada.png',
		      'http://alliantranscribe.com/img/uk.png',
		      'http://alliantranscribe.com/img/australia.png'
		    ],
		    'tel': [
		      '1 855-733-6655',
		      '1 855-733-6655',
		      '+44 800 802 1231',
		      '+61 3 8609 8382'
		    ],
		    'status': 1,
		    'telephonicUserId': '113167',
		    'telephonicPassword': '11111',
		    'registeredUserHotline': '1 855-733-6655'
		  }
     * }")
     *
     */
	public function telephonicAccess($request, $response, $service, $app){ // DONT CHANGE
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
				// $base64Encrypted = $this->encryptValues(json_encode($this->errorJson("Authentication problems. CustomerID doesn't match that with token..")));
	     		$base64Encrypted = $this->encryptValues(json_encode($this->errorJson("Authentication problems. CustomerID doesn't match that with token..")));
	     		return $response->json(array('data' => $base64Encrypted));
			}
			// Retrieve telephones and pictures
			$flags =  ArrayValues::flags();
			$tel = ArrayValues::telephones();
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
			$jsonArray['registeredUserHotline'] = '1 855-733-6655'; //TODO NOT NEEDED REMOVE
			// Encrypt fomrat json response
			$result = array_merge($flags, $tel, $jsonArray);
			// Encrypt the data
			$base64Encrypted = $this->encryptValues(json_encode($result));
	     	return $response->json(array('data' => $base64Encrypted));
     	} else {
     		// $base64Encrypted = $this->encryptValues(json_encode($this->errorJson("No token provided in request")));
     		$base64Encrypted = $this->encryptValues(json_encode($this->errorJson("Authentication problems. Customer id doesn't match that with token.")));
	     		return $response->json(array('data' => $base64Encrypted));
     	}
	}

	/**
     * @ApiDescription(section="TelephonicAccessEmail", description="Send a user an email with the telephonic id and telephonic password and number to call")
     * @ApiMethod(type="post")
     * @ApiRoute(name="/testgauss/telephonicAccessEmail")
     @ApiBody(sample="{'data': {
	    'CustomerID': '800',
	    'tel': '1 855-733-6655'
  		},
	     'token': 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzUxMiJ9.eyJpYXQiOjE0NjQ0ODc0ODUsImp0aSI6IndPSTlFRm9kY0RQVDdwc1d6RlphTUQ2dzlUMWhiSjUzV1BiSGxJSXFMZHc9IiwiaXNzIjoibG9jYWxob3N0IiwibmJmIjoxNDY0NDg3NDg1LCJleHAiOjE0NjU2OTcwODUsImRhdGEiOnsiU3VjY2VzcyI6IlN1Y2Nlc3MifX0.z03x_B2G6I3DdxaOsND3QMR0Rz8zCanxu6sf-4oH8x99x5nyvkhI0qClDMOzwXAC5ZU54D4OHgiJiiGoYU_4nQ'}")
	     * @ApiParams(name="data", type="string", nullable=false, description="Customers id and telphone number selected by user. Stored in mobile phone, as this is the identifier for the user using the app.")
	      @ApiParams(name="token", type="string", nullable=false, description="Autentication token for users autentication.")
	     * @ApiReturnHeaders(sample="HTTP 200 OK")
	     * @ApiReturn(type="string", sample="{
	     *  'data': {
	    'status': 1,
	    	'userMessage': 'YOUR TELEPHONIC ACCESS CARD HAS BEEN E-MAILED TO: slavensakacic@gmail.com IF YOU DO NOT RECEIVE THE EMAIL, PLEASE E-MAIL SUPPORT AT cs@alliantranslate.com'
  		}
     * }")
     *
     */
	public function telephonicAccessEmail($request, $response, $service, $app){ // DONT CHANGE
		if($request->token){
			// Validate token if not expired, or tampered with
			$this->validateToken($request->token);
			//Decrypt input data
			$data = $this->decryptValues($request->data);
			// Validate input data
			$service->validate($data['CustomerID'], 'Error: No customer id is present.')->notNull()->isInt();
			$service->validate($data['tel'], 'Error: No telehpone is present.')->notNull();
			// Validate token in database for customer stored
			$validated = $this->validateTokenInDatabase($request->token, $data['CustomerID']);
			// If error validating token in database
			if(!$validated){
	     		$base64Encrypted = $this->encryptValues(json_encode($this->errorJson("Authentication problems. CustomerID doesn't match that with token.")));
	     		return $response->json(array('data' => $base64Encrypted));
			}
			$customer = CustLogin::getCustomer($data['CustomerID']);

			$values = array();
			$values['Email'] = $customer->getValueEncoded('Email');
			$values['FName'] = ucfirst($customer->getValueEncoded('FName'));
			$values['LName'] = ucfirst($customer->getValueEncoded('LName'));
			$values['telephonicUserId'] = $customer->getValueEncoded('PhLoginId');
			$values['telephonicPassword'] = $customer->getValueEncoded('PhPassword');
			$values['csEmail'] = getenv('CS_EMAIL');
			$values['tel'] = $data['tel'];
			// Send email
			$server = $this->serverEnv();
			if($server=="localhost"){
				$sent = Mail::telAccessEmail($values);
			} else if($server=="alliantranslate"){
				$sent = Mail::telAccessProduction($values);
			}
			if(!$sent){
				$errorJson = $this->encryptValues(json_encode($this->errorJson("Error: Problem sending email. Contact support!")));
				return $response->json(array('data' => $errorJson));
			}
			$jsonArray = array();
			$jsonArray['status'] = 1;
			$jsonArray['userMessage'] = "YOUR TELEPHONIC ACCESS CARD HAS BEEN E-MAILED TO: " . $values['Email'] . " IF YOU DO NOT RECEIVE THE EMAIL, PLEASE E-MAIL SUPPORT AT " . getenv('CS_EMAIL');
			$base64Encrypted = $this->encryptValues(json_encode($jsonArray));
	     	return $response->json(array('data' => $base64Encrypted));
		} else {
     		$base64Encrypted = $this->encryptValues(json_encode($this->errorJson("Authentication problems. CustomerID doesn't match that with token.")));
	     	return $response->json(array('data' => $base64Encrypted));
     	}
	}

	/**
     * @ApiDescription(section="Terms", description="Render view for terms & conditions")
     * @ApiMethod(type="get")
     * @ApiRoute(name="/testgauss/terms")
     * @ApiReturnHeaders(sample="HTTP 200 OK")
     */
	public function getTerms($request, $response, $service, $app){ // DONT CHANGE
		// Render a view with the terms and conditions
		$service->render('./resources/views/terms.html');
	}

	/**
     * @ApiDescription(section="Suport", description="Retrieve support telephones by country and there flags. And support email.")
     * @ApiMethod(type="get")
     * @ApiRoute(name="/testgauss/support")
     * @ApiReturnHeaders(sample="HTTP 200 OK")
     * @ApiReturn(type="string", sample="{
     *  'data': {
        'status': 1,
        'supportEmail': 'cs@alliantranslate.com',
        'supportFlags': [
            'http://alliantranscribe.com/img/us.png',
            'http://alliantranscribe.com/img/canada.png',
            'http://alliantranscribe.com/img/uk.png',
            'http://alliantranscribe.com/img/france.png',
            'http://alliantranscribe.com/img/spain.png',
            'http://alliantranscribe.com/img/italy.png',
            'http://alliantranscribe.com/img/german.png',
            'http://alliantranscribe.com/img/australia.png',
            'http://alliantranscribe.com/img/holland.png',
            'http://alliantranscribe.com/img/belgium.png',
            'http://alliantranscribe.com/img/mexico.png',
            'http://alliantranscribe.com/img/intl.png'
        ],
        'supportPhones': [
            '1 (877) 512 1195',
            '1(877) 512 1195',
            '+44 800 011 9648',
            '+33 9 75 18 41 68',
            '+34 518 88 82 27',
            '+39 06 9480 3714',
            '+49 157 3598 1132',
            '+61 8 7100 1671',
            '+31 85 888 5243',
            '+32 2 588 55 16',
            '+52 55 4161 3617',
            '+1 615 645 1041'
        ]
    	}
     * }")
     *
     */
	public function support($request, $response, $service, $app){ // FIX
		// $supportPhones = ArrayValues::supportPhones();
		$flags = ArrayValues::supportFlags();
		$supportTel = ArrayValues::supportTel();
		$ret = array();
		$ret['status'] = 1;
		$ret['supportEmail'] = getenv('CS_EMAIL');
		$new = array_merge($ret, $flags, $supportTel);
		return $response->json(array("data" => $new));
	}

	/**
     * @ApiDescription(section="Logout", description="Log out the customer.")
     * @ApiMethod(type="post")
     * @ApiRoute(name="/testgauss/logout")
	     @ApiBody(sample="{'data': {
	    	'CustomerID': '800'
	  		},
	     'token': 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzUxMiJ9.eyJpYXQiOjE0NjQ1OTIzNTEsImp0aSI6IlZQTG9MT2hUN3ZoeVROTWxhd1FsZWxKY0x3YnFlS3hWZ2NZSVpCb1dPUkU9IiwiaXNzIjoibG9jYWxob3N0IiwibmJmIjoxNDY0NTkyMzUxLCJleHAiOjE1OTA3MzYzNTEsImRhdGEiOnsiU3VjY2VzcyI6IlN1Y2Nlc3MifX0.Z7QGqAP2P-aqLcwTrzyyKX0d0VNuLA6nK3Fycg8u_0xrOD5-4c94u9bejsWyAfB8wHCC1_CPKcLzZCk3gVbtZQ'}")
	     * @ApiParams(name="data", type="string", nullable=false, description="Customers id. Stored in mobile phone, as this is the identifier for the user using the app.")
	      @ApiParams(name="token", type="string", nullable=false, description="Autentication token for users autentication.")
     * @ApiReturnHeaders(sample="HTTP 200 OK")
     * @ApiReturn(type="string", sample="{
     *  'data':{
	    'status': '1',
	    'userMessage': 'You have successfully logged out!'
	  	}, 'token': 'null'
     * }")
     *
     */
	public function logout($request, $response, $service, $app){ // DONT CHANGE
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
				$base64Encrypted = $this->encryptValues(json_encode($this->errorJson("Authentication problems. CustomerID doesn't match that with token.")));
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

	/**
     * @ApiDescription(section="KeepLoggedIn", description="Make the user be keept logged in. Or keep him default login of 2 weeks.")
     * @ApiMethod(type="post")
     * @ApiRoute(name="/testgauss/keepLoggedIn")
     	@ApiBody(sample="{'data': {
	    'CustomerID': '800',
	    'keepLoggedIn': '1'
	  	},
	     'token': 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzUxMiJ9.eyJpYXQiOjE0NjQ1OTIyNTcsImp0aSI6IkFUWm1QMG9lamJteXhWRDVRV0Vmbm9NQjJcL0RSMmtlNHdPY3JxZVRLT2NBPSIsImlzcyI6ImxvY2FsaG9zdCIsIm5iZiI6MTQ2NDU5MjI1NywiZXhwIjoxNDY1ODAxODU3LCJkYXRhIjp7IlN1Y2Nlc3MiOiJTdWNjZXNzIn19.RWyj8hd7D6Ti-zB-N11u6VS6fE6H5zJKwn_OBfICjuvTfY778GNBE_WdZZQLNMArtNJweff2dlX3DGrTg2e5-Q'}")
	     * @ApiParams(name="data", type="string", nullable=false, description="Customers id. Stored in mobile phone, as this is the identifier for the user using the app.")
	      @ApiParams(name="token", type="string", nullable=false, description="Autentication token for users autentication.")
	     * @ApiReturnHeaders(sample="HTTP 200 OK")
	     * @ApiReturn(type="string", sample="{
	     *  'token':'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzUxMiJ9.eyJpYXQiOjE0NjQ1OTIzNTEsImp0aSI6IlZQTG9MT2hUN3ZoeVROTWxhd1FsZWxKY0x3YnFlS3hWZ2NZSVpCb1dPUkU9IiwiaXNzIjoibG9jYWxob3N0IiwibmJmIjoxNDY0NTkyMzUxLCJleHAiOjE1OTA3MzYzNTEsImRhdGEiOnsiU3VjY2VzcyI6IlN1Y2Nlc3MifX0.Z7QGqAP2P-aqLcwTrzyyKX0d0VNuLA6nK3Fycg8u_0xrOD5-4c94u9bejsWyAfB8wHCC1_CPKcLzZCk3gVbtZQ',
	     'data': {
	    'status': 1,
	    'keepLoggedIn': 1,
	    'userMessage': 'Success! Your kept logged in!'
  		}
     * }")
     *
     */
	public function keepLoggedIn($request, $response, $service, $app){ // DONT CHANGE
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
				$base64Encrypted = $this->encryptValues(json_encode($this->errorJson("Authentication problems. CustomerID doesn't match that with token..")));
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
					$base64Encrypted = $this->encryptValues(json_encode($this->errorJson("Internal error. Contact support.")));
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