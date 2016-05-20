<?php

namespace Allian\Http\Controllers;

use PHPMailer;
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
     * @ApiDescription(section="Login", description="Authenticate a user and return a jwt token with information, and data encrypted values. Store the token in the app, this is the auth token needed througth the app(other routes require this correct token)")
     * @ApiMethod(type="post")
     * @ApiRoute(name="/testgauss/login")
     * @ApiBody(sample="{ 'data': 'AwEPyQdyrx7LhlEdm9GylvlDftbe1MF6zt/gN04II3VldkFcTSoG72PLs87SRTU/m91E+MeXdthJzYB4/AwZ4KrAluli10YBk/3LlB7sAa0dzu+0wudujy68uAio+3VgSTnqKKIeWo77nivalHTXhrhUpay3DbLNOLeU9Svx91vTH2BlI5cIuwLlmnUvExV0OSo/1CSBL9YB7Ep/b9hvCOU969HuepCA28ekGrI8y4NyyHazXcdubgxttrHz2veyPk83m5iywDWK56i6JEibml1ZNwwuNw6WuOJPrySBPrMJs5oR/wog3MKJc+reGhCWSpTOeJ9i2N2mGWtZYbzGMOUgH7q3NnmLS2KbReNGdk/C4zOLzliB0dzENIO43Jkrb+WUmq3Lv49HwUF/51lumd67WBJrmJQIdZT0J0XmBRvCSZJ9xWLUICSRnyC+uDo59CwXj0/6s03wr02n604CbF8jWRwe29NLTwuweHEPyFwbO/S6v3V2B1xvfqWIXp3bHJjrhICuqp/2oTziolQURQpcoXI9VFUBRyRiaF4RzYbM/46Tfx29QKtVp8MvYe8R3xVpGkoyb1AfkReMc3IsjqnH'}")
     * @ApiParams(name="data", type="object", nullable=false, description="Users email & password json object to authenticate")
     * @ApiReturnHeaders(sample="HTTP 200 OK")
     * @ApiReturn(type="object", sample="{
		'token': 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzUxMiJ9.eyJpYXQiOjE0NjM1NTk4MjYsImp0aSI6IkRzWXFVOUlhWThmZmxQTmpjUEE2Q054eEZ0Qks2THRTUGl1cmtjalZ6a2s9IiwiaXNzIjoibG9jYWxob3N0IiwibmJmIjoxNDYzNTU5ODI2LCJleHAiOjE0NjQ3Njk0MjYsImRhdGEiOnsiU3VjY2VzcyI6IlN1Y2Nlc3MifX0.e8gHW54nu_NNLlfy2g9CM53I24gxBeZoj1_-A8Gv5rNawNrNg-rBPl-6jFGFbY2JgvevIPp7Dz7s4DjWeSdaWA',
		'data': 'AwFxTI/ybexQc7vI6zv4DsWY+NG+DhhBKwVTCmZZ8ZR5NEMjV+YDecggZmZ6ZVk6TgfLiuP5Gvt/XhN7PgmNipT10qZr58QwXK/zh1AI4Vwm2GwxNo++oeufZtSHPXU9FPyl9RMWjjzTCHM9qxW4nyipd7xK4hSKngfa+kdBypiKnapkpSpgW7wgRw4n7zH4w8iINo681xrBTZF92VQbEgI52Hg6jIGzvUG7tMY4CNLggJdlurWjvDmZhCYw3e9ybO6mymIQ8gGqEOVy4/0z7W+g'
		}")
		@ApiReturn(type="object", sample="{'token': null,'data': 'AwGFt1HkBpJ/8ZM6vTANUv3xikBid1ayJIIA0dggksZKNyYnvSYtRo80feStp08eZTjVEM3ruql7rmnFqyjgZR6U+kucKkn7BoxxB9/JNzl+yBuxVxem06vyROD6S7mKp/l2yN69V8SbGvOxAOzH1jgr201yxGkh2QbrrS+LVfazb7SaXkzKJEe1e3Kmo+Zq5f4='
		}")
     */
	public function postLogin($request, $response, $service, $app) {
		//Decrypt input data
		$cr_password = getenv("CRYPTOR");
		$cryptor = new \RNCryptor\Decryptor();
		$plaintext = $cryptor->decrypt($request->data, $cr_password);
		$data = json_decode($plaintext, true);

		// Validate input data
		$service->validate($data['email'], 'Invalid email address')->isLen(3,200)->isEmail();
		$service->validate($data['password'], 'Error: no password present')->notNull();
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

		$password = getenv("CRYPTOR");
		$cryptor = new \RNCryptor\Decryptor();
		$plaintext = $cryptor->decrypt($request->data, $password);
		// TODO dodati Type polje [0,1,2] 0-?, 1-pay-as-you-go, mijenja se. 2-fixan, NULL polje.
		// if($request->token){
		// 	try{
		// 		$jwt = $request->token;
		// 		$secretKey = base64_decode(getenv('jwtKey'));
  //   			$token = JWT::decode($jwt, $secretKey, array('HS512'));
		// 	} catch(ExpiredException $e) { // if result expired_token go to login page
		//    		return $response->json($this->errorJson($e->getMessage(), 'expired_token'));
		//     } catch(DomainException $e) {
		//    		return $response->json($this->errorJson($e->getMessage(), 'invalid_domain'));
		//     } catch(BeforeValidException $e){
		//    		return $response->json($this->errorJson($e->getMessage(), 'before_valid'));
		//     }
		// }

		//Decrypt input data
		$password = getenv("CRYPTOR");
		$cryptor = new \RNCryptor\Decryptor();
		$plaintext = $cryptor->decrypt($request->data, $password);
		$data = json_decode($plaintext, true);

		// Validate input data
		$service->validate($data['fname'], 'Error: no first name is present.')->isLen(3,200)->notNull();
		$service->validate($data['lname'], 'Error: no last name is present.')->isLen(3,200)->notNull();
		$service->validate($data['email'], 'Invalid email address.')->notNull()->isLen(3,200)->isEmail();
		$service->validate($data['phone'], 'Invalid phone number.')->isLen(3,200)->notNull();
		$service->validate($data['password'], 'Error: no password present.')->isLen(3,200)->notNull();
		$service->validate($data['phone_password'], 'Error: no phone password present.')->isLen(3,200)->notNull()->isInt();
		$service->validate($data['stripe_token'], 'No stripe token provided.')->notNull();
		// $service->validate($data['services'], 'Error: no service present.')->notNull;

		// Try to register customer with inputed data
		$customer = CustLogin::register($data);

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
     * @ApiDescription(section="updateProfile", description="Update customers profile information by giving the CustomerID and new info.")
     * @ApiMethod(type="post")
     * @ApiRoute(name="/testgauss/updateProfile")
     * @ApiBody(sample="{ 'data': 'AwEPyQdyrx7LhlEdm9GylvlDftbe1MF6zt/gN04II3VldkFcTSoG72PLs87SRTU/m91E+MeXdthJzYB4/AwZ4KrAluli10YBk/3LlB7sAa0dzu+0wudujy68uAio+3VgSTnqKKIeWo77nivalHTXhrhUpay3DbLNOLeU9Svx91vTH2BlI5cIuwLlmnUvExV0OSo/1CSBL9YB7Ep/b9hvCOU969HuepCA28ekGrI8y4NyyHazXcdubgxttrHz2veyPk83m5iywDWK56i6JEibml1ZNwwuNw6WuOJPrySBPrMJs5oR/wog3MKJc+reGhCWSpTOeJ9i2N2mGWtZYbzGMOUgH7q3NnmLS2KbReNGdk/C4zOLzliB0dzENIO43Jkrb+WUmq3Lv49HwUF/51lumd67WBJrmJQIdZT0J0XmBRvCSZJ9xWLUICSRnyC+uDo59CwXj0/6s03wr02n604CbF8jWRwe29NLTwuweHEPyFwbO/S6v3V2B1xvfqWIXp3bHJjrhICuqp/2oTziolQURQpcoXI9VFUBRyRiaF4RzYbM/46Tfx29QKtVp8MvYe8R3xVpGkoyb1AfkReMc3IsjqnH'}")
     * @ApiParams(name="data", type="object", nullable=false, description="Json must contain CustomerID, fname, lname, email, phone, password, phone_password, services. Example in body in the data.")
     @ApiParams(name="token", type="object", nullable=false, description="Autentication token for users autentication.")
     * @ApiReturnHeaders(sample="HTTP 200 OK")
     * @ApiReturn(type="object", sample="{
		'token': 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzUxMiJ9.eyJpYXQiOjE0NjM1NTk4MjYsImp0aSI6IkRzWXFVOUlhWThmZmxQTmpjUEE2Q054eEZ0Qks2THRTUGl1cmtjalZ6a2s9IiwiaXNzIjoibG9jYWxob3N0IiwibmJmIjoxNDYzNTU5ODI2LCJleHAiOjE0NjQ3Njk0MjYsImRhdGEiOnsiU3VjY2VzcyI6IlN1Y2Nlc3MifX0.e8gHW54nu_NNLlfy2g9CM53I24gxBeZoj1_-A8Gv5rNawNrNg-rBPl-6jFGFbY2JgvevIPp7Dz7s4DjWeSdaWA',
		'data': 'AwFxTI/ybexQc7vI6zv4DsWY+NG+DhhBKwVTCmZZ8ZR5NEMjV+YDecggZmZ6ZVk6TgfLiuP5Gvt/XhN7PgmNipT10qZr58QwXK/zh1AI4Vwm2GwxNo++oeufZtSHPXU9FPyl9RMWjjzTCHM9qxW4nyipd7xK4hSKngfa+kdBypiKnapkpSpgW7wgRw4n7zH4w8iINo681xrBTZF92VQbEgI52Hg6jIGzvUG7tMY4CNLggJdlurWjvDmZhCYw3e9ybO6mymIQ8gGqEOVy4/0z7W+g'
		}")
		@ApiReturn(type="object", sample="{'token': null,'data': 'AwGFt1HkBpJ/8ZM6vTANUv3xikBid1ayJIIA0dggksZKNyYnvSYtRo80feStp08eZTjVEM3ruql7rmnFqyjgZR6U+kucKkn7BoxxB9/JNzl+yBuxVxem06vyROD6S7mKp/l2yN69V8SbGvOxAOzH1jgr201yxGkh2QbrrS+LVfazb7SaXkzKJEe1e3Kmo+Zq5f4='
		}")
     */
	public function updateProfile($request, $response, $service, $app){
		if($request->token){ //editmain.php i editmainadd.php
			try{
				$jwt = $request->token;
				$secretKey = base64_decode(getenv('jwtKey'));
    			$token = JWT::decode($jwt, $secretKey, array('HS512'));
			} catch(ExpiredException $e) { // if result expired_token go to login page
		   		return $response->json($this->errorJson($e->getMessage(), 'expired_token'));
		    } catch(DomainException $e) {
		   		return $response->json($this->errorJson($e->getMessage(), 'invalid_domain'));
		    } catch(BeforeValidException $e){
		   		return $response->json($this->errorJson($e->getMessage(), 'before_valid'));
		    }


			// Decrypt input data
			$cr_password = getenv("CRYPTOR");
			$cryptor = new \RNCryptor\Decryptor();
			$plaintext = $cryptor->decrypt($request->data, $cr_password);
			$data = json_decode($plaintext, true);

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
     	}
	}

	/**
     * @ApiDescription(section="Forgot", description="First check if email is found in the database, then generate a new password, then email the user the new password, and only then change database password to the new password.")
     * @ApiMethod(type="post")
     * @ApiRoute(name="/testgauss/forgot")
     * @ApiBody(sample="{'data': 'AwEpDBFkKUA2WKqf5QRs+KNVWdiohvtc+HF6+Nd6HRdPyH7kv2auKWtA+Z6vemyeROI7PqKDxBwt2yNdRW8xAQaU5O7aAhNtRTgzjEJX4SQ9bIjdvqxCJQB70/SKZ+MjbhE1ZK8OWm9amkRg8S7UdFtg'}")
     *@ApiParams(name="data", type="object", nullable=false, description="Json must contain email.Be carefull not to enter some real users email. Gmail server and stuff is live, it's on a staging server where no real users data is important when changed, but still, the gmail smtp server will send an email saying there pass was changed. Use YOUR OWN EMAIL in the data.")
     * @ApiReturnHeaders(sample="HTTP 200 OK")
     * @ApiReturn(type="object", sample="{
     *  'data': 'AwFck5aTROqKMsBR/M1JLWkq8Z6jRB1kYvmyFcAe+xbG1tzBHSxA071R0EJrg6FFMlYbGo7/U4Z2ga9iM+OQjI9TTPCsBeuNhb0E/1ewEfRqmy1MoSJ5Kc5eNfYtoy0lkMeUO81HxjVakxLGygg15+QSYdXfT7j8wjihtIcjUqAZKE+iAjkEC+lIb9sBQsq4yLDgMf5Y+WtSY5HYEy+zwpXvm0dbYZnM0yOpjmgDky6vuDVAk0ljRlZgptN1w7aTBMKmVJB/MTuJV7sNr9FdVkJA'
     * }")
     */
	public function postForgot($request, $response, $service, $app) {
		// Take care of token expiration, validity
		if($request->token){
			try{
				$jwt = $request->token;
				$secretKey = base64_decode(getenv('jwtKey'));
    			$token = JWT::decode($jwt, $secretKey, array('HS512'));
			} catch(ExpiredException $e) {
		   		return $response->json($this->errorJson($e->getMessage(), 'expired_token'));
		   } catch(DomainException $e) {
		   		return $response->json($this->errorJson($e->getMessage(), 'invalid_domain'));
		   } catch(BeforeValidException $e){
		   		return $response->json($this->errorJson($e->getMessage(), 'before_valid'));
		   }

			//Decrypt input data
			$password = getenv("CRYPTOR");
			$cryptor = new \RNCryptor\Decryptor();
			$plaintext = $cryptor->decrypt($request->data, $password);
			$data = json_decode($plaintext, true);

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
	     }
	}



	/**
     * @ApiDescription(section="TelephonicAccess", description="Retrieve customers telephonicUserId & telephonicPassword for telephonic access. By giving the customerID. TODO encrypt customerIDs with secret key, so no one can just type an int and get someones access code.")
     * @ApiMethod(type="post")
     * @ApiRoute(name="/testgauss/telephonicAccess")
     @ApiBody(sample="{'data': 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzUxMiJ9.eyJpYXQiOjE0NjMzODc2MDYsImp0aSI6IjJtK3gyMEllNTdzRFFZOWFGc1kreDJJVFM2MGpXY1c2M1hJa2pDNWxlOUk9IiwiaXNzIjoiaHR0cDpcL1wvbG9jYWxob3N0XC8iLCJuYmYiOjE0NjMzODc2MDYsImV4cCI6MTQ2NDU5NzIwNiwiZGF0YSI6eyJjdXN0b21lcklEIjo3NTJ9fQ.78Anz9Q1gOnzDhxBNjiWjUgUpLJAU_-6QP65gBGndvmv8ZH0gqaeVCrSv0mDczh05bTpf0-EZ_HHQ6OMDEzhAw'}")
     * @ApiParams(name="data", type="object", nullable=false, description="Customers id. Stored in mobile phone, as this is the identifier for the user using the app. TODO encrypt the customerID.")
     * @ApiReturnHeaders(sample="HTTP 200 OK")
     * @ApiReturn(type="object", sample="{
     *  'data':'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzUxMiJ9.eyJpYXQiOjE0NjMzODg0MDIsImp0aSI6InlkS0V0YkVjRmdpYWFLMTBkS2xcL2pHT1g5MFVcLzZZOXZwRDlGMlllWVJzUT0iLCJpc3MiOiJsb2NhbGhvc3QiLCJuYmYiOjE0NjMzODg0MDIsImV4cCI6MTQ2NDU5ODAwMiwiZGF0YSI6eyJzdGF0dXMiOjEsInRlbGVwaG9uaWNVc2VySWQiOiIxMTMxNjgiLCJ0ZWxlcGhvbmljUGFzc3dvcmQiOiI0NTQzNSJ9fQ.3dEvH_n89oYAf-fyMq5hEd7MyuCr9SM42FOd_qRRDudMeY-16MYiQpoSLb5f49jwAlXx-Ml-5-2cyn1H7gFH7w'
     * }")
     *	@ApiReturn(type="object", sample="{
     *  'status': 0,
     *  'userMessage': 'THIS WILL ALSO BE TOKEN. FOR NOW NO NEED TO ENCRYPT THIS. No user found.'
     * }")
     *
     */
	public function postTelephonicAccess($request, $response, $service, $app){
		// ENcode and decode customerID with secret key while transfer for secutiry TODO
		if($request->token){
			try{
				$jwt = $request->token;
				$secretKey = base64_decode(getenv('jwtKey'));
    			$token = JWT::decode($jwt, $secretKey, array('HS512'));
			} catch(ExpiredException $e) { // if result expired_token go to login page
		   		return $response->json($this->errorJson($e->getMessage(), 'expired_token'));
		    } catch(DomainException $e) {
		   		return $response->json($this->errorJson($e->getMessage(), 'invalid_domain'));
		    } catch(BeforeValidException $e){
		   		return $response->json($this->errorJson($e->getMessage(), 'before_valid'));
		    }
		}
		$service->validate($token->data->customerID, 'Invalid id')->isInt();

		$customer = CustLogin::getCustomer($token->data->customerID);
		if(!$customer){
			$errorJson = $this->errorJson("No user found.");
			return $response->json($errorJson);
		}
		$jsonArray = array();
		$jsonArray['status'] = 1;
		$jsonArray['telephonicUserId'] = $customer->getValueEncoded('PhLoginId');
		$jsonArray['telephonicPassword'] = $customer->getValueEncoded('PhPassword');
		// $jsonArray['userMessage'] = "";
		$genToken = $this->generateResponseToken($jsonArray);
     	return $response->json($genToken);

	}

	/**
	 *
	 * Block comment
	 *
	 */
	public function generateResponseToken($dataArray){
		$secretKey = base64_decode(getenv('jwtKey'));

		$tokenId    = base64_encode(mcrypt_create_iv(32));
	    $issuedAt   = time();
	    $notBefore  = $issuedAt;
	    $expire     = $notBefore + 1209600;
	    $serverName = $_SERVER['SERVER_NAME'];

	    $data = array(
	        'iat'  => $issuedAt,         // Issued at: time when the token was generated
	        'jti'  => $tokenId,          // Json Token Id: an unique identifier for the token
	        'iss'  => $serverName,       // Issuer
	        'nbf'  => $notBefore,        // Not before
	        'exp'  => $expire,           // Expire
	        'data' => $dataArray
	    );

	    // Encode the new json payload data
	    $jwt = JWT::encode($data, $secretKey, 'HS512');
	    header('Content-type: application/json');

    	return $jwt;
	}


}