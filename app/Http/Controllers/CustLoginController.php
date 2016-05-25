<?php

namespace Allian\Http\Controllers;

use PHPMailer;
use Allian\Http\Controllers\StripeController;
use Allian\Models\CustLogin;
use Firebase\JWT\JWT;
use \Dotenv\Dotenv;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\DomainException;
use Firebase\JWT\BeforeValidException;
use RNCryptor\Encryptor;
use RNCryptor\Decryptor;

class CustLoginController extends Controller {

	 /**
     * @ApiDescription(section="Login", description="Authenticate the customer with his email and password and return a jwt token with expiration date for server side validation of autenticity of customer, also returns a data key thats encrypted with RNCryptor with the data values . <br><b>Store the token in the app, this is the auth token needed througth the app</b>(other routes require this correct token).")
     * @ApiMethod(type="post")
     * @ApiRoute(name="/testgauss/login")
     * @ApiBody(sample="{ 'data': 'AwGTfsuNPRIe9flrvJ4VlNhTUraZY67pp5FPPmym+ptwSCHO/jQkN97t5GB364Ac0zfd2dYsWJiXvawzmmCKSQm4OamHp5cMfjtsNirigo6AFJX4izmUgEcRmxx93W79B0DYpCNrtigYQcAPTF7uZKpiROIhXKBQyotWhyVxFXL9jPYXHtYUNa+uhrZ7r4CzIuI='}")
     * @ApiParams(name="data", type="object", nullable=false, description="Users email & password json object used for authentication.")
     * @ApiReturnHeaders(sample="HTTP 200 OK")
     * @ApiReturn(type="object", sample="{'token': 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzUxMiJ9.eyJpYXQiOjE0NjM3ODQ3OTgsImp0aSI6InlMcG1ORWhcL0JDQ1l0MkhBeUtqT0NWVWMwSDF6ck5vdFE0UEJiMWVicHNjPSIsImlzcyI6ImxvY2FsaG9zdCIsIm5iZiI6MTQ2Mzc4NDc5OCwiZXhwIjoxNDY0OTk0Mzk4LCJkYXRhIjp7IlN1Y2Nlc3MiOiJTdWNjZXNzIn19.W7j7_NnUcTjsnvTU_pKMMTgJlQeP6pWvn9q7hwNZXStzc4MOm3Vgr7_7dvr-Boj5W-HWfqRelcFf8Rrao2Mz8A', 'data': 'AwGTanQjtgGJWgWHL1plL/j664tWA6W1atWNUo8QyiuuOdnwLE+NP/9bHQqhkyNttLTN5LGoCtuc59yaWDsn/k1IJJLkezVO/FZdQADjhbqYW5bUCXCntjWN3CkrI8JswcSbgFjpYhfrSmy4elc4KNSB0FD7NpH4KDIWQDxNQbXStReUodOeQXmuAHOwKejkf1Lf7qlUCwJsnvmh07UwKGwdN625xrCLB3dZxe60IYNcJcpvblVgI7NIsR4NsGndrkZU4XqVUt2DhlQhJjuPEr3U1Fp9ih10aWj4lUqq9t43jw=='
		}")
     */
	public function postLogin($request, $response, $service, $app) {
		//Decrypt input data
		$data = $this->decryptValues($request->data);

		// Validate input data
		$service->validate($data['email'], 'Invalid email address given.')->isLen(3,200)->isEmail();
		$service->validate($data['password'], 'No password is present.')->notNull();
    	$email = $data['email'];
    	$password = $data['password'];

    	// Authenticate the user in the database
		$customer = CustLogin::authenticate($email, $password);

		// If error, return encrypted message
		if(!$customer){
			$base64Encrypted = $this->encryptValues(json_encode($this->errorJson("Authentication was unsuccessful, please try again.")));
			$resp = $this->makeLoginResponse($base64Encrypted);
     		return $response->json($resp);
		}

		// Generate token
		$genToken = $this->generateResponseToken(array("Success" => "Success"));

		// Format data for encryption
		$base64Encrypted = $this->encryptValues(json_encode($this->loginValues($customer)));

		// Make the response json
		$resp = $this->makeLoginResponse($base64Encrypted, $genToken);

		// Return as json token and encrypted data
     	return $response->json($resp);
	}

	  /**
     * @ApiDescription(section="Register", description="Register a new user in the database")
     * @ApiMethod(type="post")
     * @ApiRoute(name="/testgauss/register")
     * @ApiBody(sample="{ 'data': 'AwEPyQdyrx7LhlEdm9GylvlDftbe1MF6zt/gN04II3VldkFcTSoG72PLs87SRTU/m91E+MeXdthJzYB4/AwZ4KrAluli10YBk/3LlB7sAa0dzu+0wudujy68uAio+3VgSTnqKKIeWo77nivalHTXhrhUpay3DbLNOLeU9Svx91vTH2BlI5cIuwLlmnUvExV0OSo/1CSBL9YB7Ep/b9hvCOU969HuepCA28ekGrI8y4NyyHazXcdubgxttrHz2veyPk83m5iywDWK56i6JEibml1ZNwwuNw6WuOJPrySBPrMJs5oR/wog3MKJc+reGhCWSpTOeJ9i2N2mGWtZYbzGMOUgH7q3NnmLS2KbReNGdk/C4zOLzliB0dzENIO43Jkrb+WUmq3Lv49HwUF/51lumd67WBJrmJQIdZT0J0XmBRvCSZJ9xWLUICSRnyC+uDo59CwXj0/6s03wr02n604CbF8jWRwe29NLTwuweHEPyFwbO/S6v3V2B1xvfqWIXp3bHJjrhICuqp/2oTziolQURQpcoXI9VFUBRyRiaF4RzYbM/46Tfx29QKtVp8MvYe8R3xVpGkoyb1AfkReMc3IsjqnH'}")
     * @ApiParams(name="data", type="object", nullable=false, description="Json must contain fname, lname, email, phone, password, phone_password, services, stripe_token.")
     * @ApiReturnHeaders(sample="HTTP 200 OK")
     * @ApiReturn(type="object", sample="{'data': 'AwHLdqaPYWUqunsX7Q7xnJ9ac0UzPgFo95bWo7kxZctQHXT6aqrgp1DuoEc7+MmpW/zFoj7BICGqcoBU+s49icpz2dTOQz14klgx/x+JQlU1Sp7fJOts1LEz7+takbBmHxmhuK3ulnxrf4BlpPXluNgg2y91HQ4AmqPfGKilkKWIilMRUFFzNoFVuQEideWzE8Q=' }")
     * @ApiReturn(type="object", sample="{'data': 'AwFoZ4u4exWaV9YdN6S90zKdMKITd8Zq/mjlW4OLLQ7uiHCFGOrRwUe2QlByqsYtyTNJ/TzAiB67Zm20S9H7mFhZApmxsyB6VZdw39B/RlxLjYpuOUZSDf+ebGrGSF6+0QnJytAEKdxHcufbziBpm+epD/fVWgwCTUhXEDonYwwhQAIKfZ/YBjllD5MniOfMusg=' }")
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
		$service->validate($data['phone_password'], 'Error: no phone password present.')->isLen(3,200)->notNull()->isInt();
		$service->validate($data['services'], 'Error: no service present.')->notNull();
		$service->validate($data['sname'], 'Error: no stripe name present.')->notNull()->isLen(3,200);
		$service->validate($data['number'], 'Error: no credit card number present.')->notNull();
		$service->validate($data['exp_month'], 'Error: Expiration month not present.')->notNull();
		$service->validate($data['exp_year'], 'Error: Expiration year not present.')->notNull();
		$service->validate($data['cvc'], 'Error: Cvc not present.')->notNull();
		// TODO generate jwt token, store in database, response token
		// Stripe token then customer create
		$stripe = new StripeController();
		$tokenResult = $stripe->createToken($data);
		$stripeCustomer = $stripe->createCustomer($data['email'], $tokenResult);

		// Try to register customer with inputed data
		$customer = CustLogin::register($data, $stripeCustomer);

		// On error, return message
		if(!$customer){
			$base64Encrypted = $this->encryptValues(json_encode($this->errorJson("There was a problem while registration.")));
     		return $response->json(array('data' => $base64Encrypted));
		}

		// Format response
		$jsonArray = array();
		$jsonArray['status'] = 1;
		$jsonArray['customerID'] = $customer->getValueEncoded('CustomerID');
		$jsonArray['userMessage'] = "Registration Succesfull";

		// Format data for encryption
		$base64Encrypted = $this->encryptValues(json_encode($jsonArray));

		// Return as json token and encrypted data
     	return $response->json(array('data' => $base64Encrypted));
	}

	 /**
     * @ApiDescription(section="UpdateProfile", description="Update customers profile information by giving the CustomerID to recognize him in the database and new info.")
     * @ApiMethod(type="post")
     * @ApiRoute(name="/testgauss/updateProfile")
     * @ApiBody(sample="{ 'data': 'AwH+LYr35RjCYynhPQfMXqpbfxZUYH0UDOZpRZWUanLY8VAAOMlbKB5Gh6TWtqo/pYdHnbcpaGwKz8237jdKFlwZxPpUdiGqUfY1OXAN01AF1HRaEwogw5i0cWnpmV5k1LhWf0e7YoVI6ZBZsG/41JI3DATxeW21XqFyAOcngd84glPdL1tTJiRcjUZLq1eANkQE+0zrKWprddq3NH/Oy0Tzg5/rTT0n3P20gCfzSzbBeluW3fl3cLV57f6jX4nAR1BMFFcLE6f04FZD6XH8uMQpqOIZa/IHUd3NPkaC3QSUUFQ7BhnAkkAaQ136Mewl8A6trhtcTFx6klBVtnQNF0hS5DitIK3MkR+ptP8pQZan/iW9f7htLV6sHwTiInP/NBpL3QesF7ahX3RDQXmfxZYNhN6GEC/NMmwul5kiczR18AXKqNBznjbBgvXl+HgoOg+ssrDlyNpKD3zztLMPOUDgtvE+SCq3zlCEHl2+0FKpYg=='}")
     * @ApiParams(name="data", type="object", nullable=false, description="Json must contain CustomerID, fname, lname, email, phone, password, phone_password, services. Example in body in the data.")
     @ApiParams(name="token", type="object", nullable=false, description="Autentication token for users autentication.")
     * @ApiReturnHeaders(sample="HTTP 200 OK")
     * @ApiReturn(type="object", sample="{
		'data': 'AwFxTI/ybexQc7vI6zv4DsWY+NG+DhhBKwVTCmZZ8ZR5NEMjV+YDecggZmZ6ZVk6TgfLiuP5Gvt/XhN7PgmNipT10qZr58QwXK/zh1AI4Vwm2GwxNo++oeufZtSHPXU9FPyl9RMWjjzTCHM9qxW4nyipd7xK4hSKngfa+kdBypiKnapkpSpgW7wgRw4n7zH4w8iINo681xrBTZF92VQbEgI52Hg6jIGzvUG7tMY4CNLggJdlurWjvDmZhCYw3e9ybO6mymIQ8gGqEOVy4/0z7W+g'
		}")
		@ApiReturn(type="object", sample="{'data': 'AwGFt1HkBpJ/8ZM6vTANUv3xikBid1ayJIIA0dggksZKNyYnvSYtRo80feStp08eZTjVEM3ruql7rmnFqyjgZR6U+kucKkn7BoxxB9/JNzl+yBuxVxem06vyROD6S7mKp/l2yN69V8SbGvOxAOzH1jgr201yxGkh2QbrrS+LVfazb7SaXkzKJEe1e3Kmo+Zq5f4='
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
			$service->validate($data['fname'], 'Error: no first name is present.')->isLen(3,200);
			$service->validate($data['lname'], 'Error: no last name is present.')->isLen(3,200);
			$service->validate($data['email'], 'Invalid email address.')->isLen(3,200)->isEmail();
			$service->validate($data['phone'], 'Invalid phone number.')->isLen(3,200);
			$service->validate($data['password'], 'Error: no password present.')->isLen(3,200);
			$service->validate($data['phone_password'], 'Error: no phone password present.')->isLen(3,200)->isInt();

			// Try to update customer with inputed data
			$updated = CustLogin::update($data);
			if(!$updated){
				$base64Encrypted = $this->encryptValues(json_encode($this->errorJson("Profile not edited. Try Again. Please contact Admin if problem persists")));
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
     		return $response->json("No token in request TODO. ");
     	}
	}

	/**
     * @ApiDescription(section="ViewProfile", description="Retrieve customers information(only fname, lname, phone, phone_password, email, services) stored in the database, based on the request CustomerID of user, a token for autentication.")
     * @ApiMethod(type="post")
     * @ApiRoute(name="/testgauss/viewProfile")
     * @ApiBody(sample="{'data': 'AwF4kbZkGDOPqdLCfBmjud1rkhaBpSaWVfGbWX9EvUWKRKzNMXK8Blk/0YkIstz+W7YXE5pH1ScQrk0kfgRujEUWURB+yUqp0a3d2L1ZvgpcJpOpXfLWEGyMklXzOIw0ZL4='}")
     *@ApiParams(name="data", type="object", nullable=false, description="Json must contain CustomerID.")
     @ApiParams(name="token", type="object", nullable=false, description="Autentication token for users autentication.")
     * @ApiReturnHeaders(sample="HTTP 200 OK")
     * @ApiReturn(type="object", sample="{
     *  'data': 'AwF3uEH8m278NgsBfgrfjEPQOoRzRtGZqgcMr/mNsa/ngF+YRl+jLtsV1pP0akBhK2kaqLNy6iHTbE3xqyhYfyBgb0pvVkIZTU4osNlIM5jXQnRFuwJCKG19akehIj30mz0B2lDnswiW7o9l7DbOQIFHHxe3KpKI46rin05Izy/Z+eXl8qDA/h2rvjIDl6e90c3zolAPz8pAjpmnN0Bt47KQKb5jRxeOZvt+GTsPxQh/q+m6Ki/XeTPlv6e0GHcGwGQVnnfufbaPthzM1ENlAgFmR7dfspPuYp7qhmZXTWLT/0y3IIVPMiv44ZDYaUcMgri42nfMMiSlsvdQElnglkCi4RNJvMGMeMdWBdNS5k5WxA=='
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

			// Retrieve the customer
			$customer = CustLogin::getCustomer($data['CustomerID']);

			// Format the json response
			$values = array();
			$values['fname'] = $customer->getValueEncoded('FName');
			$values['lname'] = $customer->getValueEncoded('LName');
			$values['phone_number'] = $customer->getValueEncoded('Phone');
			$values['phone_password'] = $customer->getValueEncoded('PhPassword');
			$values['email'] = $customer->getValueEncoded('Email');
			$values['services'] = $customer->getValueEncoded('Services'); // TODO format

			// Format data for encryption
			$base64Encrypted = $this->encryptValues(json_encode($values));

			// Return response json
			return $response->json(array('data' => $base64Encrypted));
     	} else {
     		return $response->json("No token in request TODO. ");
     	}
	}

	/**
     * @ApiDescription(section="Forgot", description="First check if email is found in the database, then generate a new password, then email the user the new password, and only then change database password to the new password.")
     * @ApiMethod(type="post")
     * @ApiRoute(name="/testgauss/forgot")
     * @ApiBody(sample="{'data': 'AwEpDBFkKUA2WKqf5QRs+KNVWdiohvtc+HF6+Nd6HRdPyH7kv2auKWtA+Z6vemyeROI7PqKDxBwt2yNdRW8xAQaU5O7aAhNtRTgzjEJX4SQ9bIjdvqxCJQB70/SKZ+MjbhE1ZK8OWm9amkRg8S7UdFtg'}")
     *@ApiParams(name="data", type="object", nullable=false, description="Json must contain email.Be carefull not to enter some real users email. Gmail server and stuff is live, it's on a staging server where no real users data is important when changed, but still, the gmail smtp server will send an email saying there pass was changed. Use YOUR OWN EMAIL in the data.")
     @ApiParams(name="token", type="object", nullable=false, description="Autentication token for users autentication.")
     * @ApiReturnHeaders(sample="HTTP 200 OK")
     * @ApiReturn(type="object", sample="{
     *  'data': 'AwEuAgSwBgKF6UacdLb6zUt4uNxHOwjPqG40pUv5ZigXZBuxOaoKoorjUY8Rx0U11D4p/lL/CXHpwoiQix17N2C+oAgEhpPg8BYZF0oxablhikGDHnSlQTPT2zVGsfp1Jxc6nChPdNCsohw5/5v8/aIUQSJcNH9eNTSviWnfjyLaBcDA5aYr2HZJCVYLnXKm3n/aqKIlDAdeXd70vCP7aXDzfnGznVOtua8YMhqvOiETN8l7aTPeda4Zck1WnUURIxVVWcF1TGZsAE7+Xoi2/x4p'
     * }")
     */
	public function postForgot($request, $response, $service, $app) {
		// Take care of token expiration, validity
		if($request->token){
			// Validate token if not expired, or tampered with
			$this->validateToken($request->token);

			//Decrypt input data
			$data = $this->decryptValues($request->data);

			// Validate input data
			$service->validate($data['email'], 'Please enter a valid email address to retrieve your password.')->isLen(3,200)->isEmail();

			// Check if email is found in the database
			$CustomerID = CustLogin::checkEmail($data['email']);

			// Error if no email was found in the database
			if(!$CustomerID){
				$errorJson = $this->errorJson("Email address does not exist. Please enter correct email address to retrieve your password.");
				return $response->json($errorJson);
			}

			// Retrieve the customer
			$customer = CustLogin::getCustomer($CustomerID['CustomerID']);

			// Error if customer with CustomerID was not found in the database
			if(!$customer){
				$errorJson = $this->errorJson("No user found with supplied email.");
				return $response->json($errorJson);
			}

			// Generate a radnom password for the customer to email and store in the database
			$pass = $this->generatePassword(); // Generate new pass

			// Send an email with the new pass to the user
			$sentEmail = $this->newPassEmail($data['email'], $customer->getValueEncoded('FName'), $pass);

			// Error if sending of email failed
			if(!$sentEmail){
				$errorJson = $this->errorJson("Error: Problem sending email to customer.");
				return $response->json($errorJson);
			}

			// Insert the random generated password into the database for customer with CustomerID
			$insertPass = CustLogin::insertPass($pass, $CustomerID['CustomerID']);

			// Error while inserting pass into database
			if(!$insertPass){
				$errorJson = $this->errorJson("Couldn't update users password in database.");
				return $response->json($errorJson);
			}

			// Format data for encryption
			$base64Encrypted = $this->encryptValues(json_encode($this->emailValues($customer)));

			// Return as json token and encrypted data
	     	return $response->json(array('data' => $base64Encrypted));
	     } else {
	     	return $response->json("No token given");
	     }
	}



	/** TUUUUTUTUTU
     * @ApiDescription(section="TelephonicAccess", description="Retrieve customers telephonicUserId & telephonicPassword for telephonic access. By giving the customerID. TODO encrypt customerIDs with secret key, so no one can just type an int and get someones access code.")
     * @ApiMethod(type="post")
     * @ApiRoute(name="/testgauss/telephonicAccess")
     @ApiBody(sample="{'data': 'AwF4kbZkGDOPqdLCfBmjud1rkhaBpSaWVfGbWX9EvUWKRKzNMXK8Blk/0YkIstz+W7YXE5pH1ScQrk0kfgRujEUWURB+yUqp0a3d2L1ZvgpcJpOpXfLWEGyMklXzOIw0ZL4='}")
     * @ApiParams(name="data", type="object", nullable=false, description="Customers id. Stored in mobile phone, as this is the identifier for the user using the app.")
      @ApiParams(name="token", type="object", nullable=false, description="Autentication token for users autentication.")
     * @ApiReturnHeaders(sample="HTTP 200 OK")
     * @ApiReturn(type="object", sample="{
     *  'data':'AwElQ4j7Wa2tomUyQ75rEpKSMUF0pznqdbTURvy4yzuUWVULrM0MctckP1/qSHI74VU0hkYAxKT5HR2yQZnZWzjlkjJZRthP3Hs2n17nOA1eeHwOaNuG5DKl5xtSyaiSIfK0hIuA/ND3+EIBlbOfbwpltlAZXg0mlTpya7rTJQsNYDoINP/havTIMpABtMzjzu0='
     * }")
     *
     */
	public function postTelephonicAccess($request, $response, $service, $app){
		// Encode and decode customerID with secret key while transfer for secutiry TODO
		if($request->token){
			// Validate token if not expired, or tampered with
			$this->validateToken($request->token);

			//Decrypt input data
			$data = $this->decryptValues($request->data);

			// Validate input data
			$service->validate($data['CustomerID'], 'Error: No customer id is present.')->notNull()->isInt();

			// Retrieve the customer
			$customer = CustLogin::getCustomer($data['CustomerID']);

			if(!$customer){
				$errorJson = $this->errorJson("No user found.");
				return $response->json($errorJson);
			}

			$jsonArray = array();
			$jsonArray['status'] = 1;
			$jsonArray['telephonicUserId'] = $customer->getValueEncoded('PhLoginId');
			$jsonArray['telephonicPassword'] = $customer->getValueEncoded('PhPassword');

			// Encrypt fomrat json response
			$base64Encrypted = $this->encryptValues(json_encode($jsonArray));
	     	return $response->json(array('data' => $base64Encrypted));
     	} else {
     		return $response->json("No token given");
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

	public function keepLoggedIn($request, $response, $service, $app){

	}

	public function logout($request, $response, $service, $app){

		if($request->token){ //editmain.php i editmainadd.php

			// Validate token if not expired, or tampered with
			$this->validateToken($request->token);

			//Decrypt input data
			$data = $this->decryptValues($request->data);

			// Validate input data
			$service->validate($data['loggedIn'], 'Error: something went wrong.')->isInt()->notNull();

			if($data['loggedIn']){
				// token infinite, store int database token
				// vrati token_infinite i spremi u app mob umjesto dosad napravljenog.
				// loggedIn = 1
				// status = 1
			} else {
				// token - expired, store into database token,
				//status = 1
				// loggedIn = 0
				// vrati token_invalidated i
				// spremi u app mob umjesto dosad napravljenog NIJE OBVEZNO
			}

		} else {
			return $response->json("No token provided");
		}
	}

}