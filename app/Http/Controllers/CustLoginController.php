<?php

namespace Allian\Http\Controllers;

use PHPMailer;
use Allian\Models\CustLogin;
use Firebase\JWT\JWT;
use \Dotenv\Dotenv;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\DomainException;
use Firebase\JWT\BeforeValidException;

class CustLoginController extends Controller {
	 /**
     * @ApiDescription(section="Login", description="Authenticate a user and return a jwt token with information. Store this token in the app, this is the auth token needed througt the app(other routes require this correct token)")
     * @ApiMethod(type="post")
     * @ApiRoute(name="/testgauss/login")
     * @ApiBody(sample="{ 'email': 'slavensakacic@gmail.com', 'password': 'zaL2a2nQ' }")
     * @ApiParams(name="data", type="object", nullable=false, description="Users email & password json object to authenticate")
     * @ApiReturnHeaders(sample="HTTP 200 OK")
     * @ApiReturn(type="object", sample="{
     *  'token':'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpYXQiOjE0NjI5NjI0MjUsImp0aSI6IjJlbldZRElqams5U3ZYaWRwXC9kamtUT2tpZ2dCSXBKSFwvVmRGRDBxeTRsST0iLCJpc3MiOiJsb2NhbGhvc3QiLCJuYmYiOjE0NjI5NjI0MjUsImV4cCI6MTQ2NDE3MjAyNSwiZGF0YSI6eyJzdGF0dXMiOjEsImZuYW1lIjoiU2xhdmVuIiwibG5hbWUiOiJTbGF2ZW4iLCJ1c2VyTWVzc2FnZSI6IkF1dGhlbnRpY2F0aW9uIHdhcyBzdWNjZXNzZnVsIGZvciBTbGF2ZW4gU2xhdmVuLiJ9fQ.xusY8M-qS3-egQoULq825Kw8rIFdUOB-Dy6EDGBAakg'
     * }")
     *	@ApiReturn(type="object", sample="{
     *  'status': 0,
     *  'userMessage': 'Authentication was unsuccessful, please try again.'
     * }")
     *	@ApiReturn(type="object", sample="{
     *  'status': 0,
     *  'userMessage': 'Invalid email address'
     * }")
     * @ApiReturn(type="object", sample="{
     *  'status': 0,
     *  'userMessage': 'Error: no password present'
     * }")
     */
	public function postLogin($request, $response, $service, $app) {
		$data = json_decode($request->data, true);
		$service->validate($data['email'], 'Invalid email address')->isLen(3,200)->isEmail();
		$service->validate($data['password'], 'Error: no password present')->notNull();
    	$email = $data['email'];
    	$password = $data['password'];

    	// Authenticate the user in the database
		$customer = CustLogin::authenticate($email, $password);
		if(!$customer){
			$errorJson = $this->errorJson("Authentication was unsuccessful, please try again.");
			$response->json($errorJson);
			exit;
		}
		$genToken = $this->generateResponseToken($this->userValues($customer));
     	return $response->json($genToken);
	}

	  /**
     * @ApiDescription(section="Register", description="Register a new user in the database")
     * @ApiMethod(type="post")
     * @ApiRoute(name="/testgauss/register")
     * @ApiBody(sample="{ 'token': 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzUxMiJ9.eyJpYXQiOjE0NjMwMzQ5NDMsImp0aSI6InFZeHRjWWtBZGZEcEtxOHBtNkdnXC9FK1pSVmVPTys2SHM2VGpTcmlyYVBZPSIsImlzcyI6Imh0dHA6XC9cL2xvY2FsaG9zdFwvIiwibmJmIjoxNDYzMDM0OTQzLCJleHAiOjE0NjQyNDQ1NDMsImRhdGEiOnsiZm5hbWUiOiJTbGF2ZW4iLCJsbmFtZSI6IlNha2FjaWMiLCJlbWFpbCI6InNsYXZlbnNha2FjaWNAZ21haWwuY29tIiwicGhvbmUiOiI3NzMtNzMyLTY1MzQiLCJwYXNzd29yZCI6IjEyMzQ1IiwicGhvbmVfcGFzc3dvcmQiOiI0NTQzNSIsInNlcnZpY2VzIjpbInRlbGVwaG9uaWNfaW50ZXJwcmV0aW5nIiwidHJhbnNsYXRpb25fc2VydmljZXMiLCJvbnNpdGVfaW50ZXJwcmV0aW5nIiwidHJhbnNjcmlwdGlvbl9zZXJ2aWNlcyJdLCJzdHJpcGVfdG9rZW4iOiJjdXNfNm5ORkRSVkdqZDF3VWUifX0.NSRnFGamaT9ruYap8D5s-SxMq0Qk5jE7M2dd0o2rGz7N7C9UNbdjEQEnkoWbJp0ijDWVAlRGB6LKVK8JnAiC1w'}")
     * @ApiParams(name="token", type="object", nullable=false, description="Json must contain fname, lname, email, phone, password, phone_password, services, stripe_token. Example in body in the token. Check the contents of this token at https://jwt.io website by putting token in the Encoded input field.")
     * @ApiReturnHeaders(sample="HTTP 200 OK")
     * @ApiReturn(type="object", sample="{'token': 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzUxMiJ9.eyJpYXQiOjE0NjI5OTk5MDUsImp0aSI6ImNOZ3NQRXg1R2t5djUrUHc1UnQzSXRVdEVYSzMzbk9zN1lTYnlGRGdjOU09IiwiaXNzIjoibG9jYWxob3N0IiwibmJmIjoxNDYyOTk5OTA1LCJleHAiOjE0NjQyMDk1MDUsImRhdGEiOnsic3RhdHVzIjoxLCJ1c2VyTWVzc2FnZSI6IlJlZ2lzdHJhdGlvbiBTdWNjZXNmdWxsIn19.h0UnxdBneDQdQHXJk3WXJYOsLw_QROoY4ZbuFhBFKt2XZ6eQosUadH2rOusDxkHQAaPaTXdla55K01MeQrMF0A' }")
     *	@ApiReturn(type="object", sample="{
     *  'status': 0,
     *  'userMessage': 'Email already registered.'
     * }")
     */
	public function postRegister($request, $response, $service, $app) {
		// TODO dodati Type polje [0,1,2] 0-?, 1-pay-as-you-go, mijenja se. 2-fixan, NULL polje.
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
		$service->validate($token->data->fname, 'Error: no first name is present.')->isLen(3,200)->notNull();
		$service->validate($token->data->lname, 'Error: no last name is present.')->isLen(3,200)->notNull();
		$service->validate($token->data->email, 'Invalid email address.')->notNull()->isLen(3,200)->isEmail();
		$service->validate($token->data->phone, 'Invalid phone number.')->isLen(3,200)->notNull();
		$service->validate($token->data->password, 'Error: no password present.')->isLen(3,200)->notNull();
		$service->validate($token->data->phone_password, 'Error: no phone password present.')->isLen(3,200)->notNull()->isInt();
		$service->validate($token->data->stripe_token, 'No stripe token provided.')->notNull();
		$service->validate($data['services'], 'Error: no service present.')->notNull;

		$message = CustLogin::register($token->data);

		$genToken = $this->generateResponseToken($this->successJson($message));
     	return $response->json($genToken);
	}

	/**
     * @ApiDescription(section="Forgot", description="First check if email is found in the database, then generate a new password, then email the user the new password, and only then change database password to the new password.")
     * @ApiMethod(type="post")
     * @ApiRoute(name="/testgauss/forgot")
     * @ApiBody(sample="{'token': 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzUxMiJ9.eyJpYXQiOjE0NjMwMzU0MTAsImp0aSI6Ijc4U29BbzI0SEhvS3JCTCtVUmdWeVIxTFhuTVRXaWNqdmZ0dGwrZUp2WTA9IiwiaXNzIjoiaHR0cDpcL1wvbG9jYWxob3N0XC8iLCJuYmYiOjE0NjMwMzU0MTAsImV4cCI6MTQ2NDI0NTAxMCwiZGF0YSI6eyJlbWFpbCI6InNsYXZlbnNha2FjaWNAZ21haWwuY29tIn19.HLHV-GAFNb4dSafuAaTc0mT7zVS9v-KwY87DOsr_kJj86dJIWXXDR6eJ8EIef6rLlzGgv1Nd_w6iqriTjDwjcA'}")
     *@ApiParams(name="token", type="object", nullable=false, description="Json must contain email. Example in body in the token. Check the contents of this token at https://jwt.io website by putting token in the Encoded input field. Be carefull not to enter some real users email. Gmail server and stuff is live, it's on a staging server where no real users data is important when changed, but still, the gmail smtp server will send an email saying there pass was changed. Use YOUR OWN EMAIL in the token.")
     * @ApiReturnHeaders(sample="HTTP 200 OK")
     * @ApiReturn(type="object", sample="{
     *  'token': 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzUxMiJ9.eyJpYXQiOjE0NjMwMzY1NTQsImp0aSI6IkxZUUwyRkJERmR5dUdnYnNDSkV6eUIrNCtCRHhONmQ0aFMzYXkybnBRQUE9IiwiaXNzIjoibG9jYWxob3N0IiwibmJmIjoxNDYzMDM2NTU0LCJleHAiOjE0NjQyNDYxNTQsImRhdGEiOnsic3RhdHVzIjoxLCJ1c2VyTWVzc2FnZSI6Ik5ldyBwYXNzd29yZCBoYXMgYmVlbiBzZW50IHRvIHlvdXIgZW1haWwgYWRkcmVzcy4ifX0.2JXKlCVjU-71crUSbeVbzyoqtI0StQyBB5U_xjved-mC4qGp6u2QdVk5YjdMoKxqBzzLQOwbYGnK7_ezSJCx9w'
     * }")
     */
	public function postForgot($request, $response, $service, $app) {
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
		}
		$service->validate($token->data->email, 'Please enter a valid email address to retrieve your password.')->isLen(3,200)->isEmail();
		$CustomerID = CustLogin::checkEmail($token->data->email);
		if(!$CustomerID){
			$errorJson = $this->errorJson("Email address does not exist. Please enter correct email address to retrieve your password.");
			return $response->json($errorJson);
		}
		$customer = CustLogin::getCustomer($CustomerID['CustomerID']);
		if(!$customer){
			$errorJson = $this->errorJson("No user found with supplied email.");
			return $response->json($errorJson);
		}
		$pass = $this->generatePassword(); // Generate new pass
		$sentEmail = $this->newPassEmail($token->data->email, $customer->getValueEncoded('FName'), $pass); // Send email with pass
		if(!$sentEmail){
			$errorJson = $this->errorJson("Error: Problem sending email to customer.");
			return $response->json($errorJson);
		}
		$insertPass = CustLogin::insertPass($pass, $CustomerID['CustomerID']); // Insert pass where CustomerID
		if(!$insertPass){
			$errorJson = $this->errorJson("Couldn't update users password in database.");
			return $response->json($errorJson);
		}
		$genToken = $this->generateResponseToken($this->emailValues($customer));
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
    	$token = array('token' => $jwt);
    	return $token;
	}

	/**
	 *
	 * Maybe create Mail Model?
	 *
	 */
	public function newPassEmail($email, $FName, $LoginPassword){
		//SMTP needs accurate times, and the PHP time zone MUST be set
		//This should be done in your php.ini, but this is how to do it if you don't have access to that
		date_default_timezone_set('Etc/UTC');

		$message = file_get_contents('resources/views/emails/newpassword.php');
		$message = str_replace('%FName%', $FName, $message);
		$message = str_replace('%logo%', getenv('LOGO'), $message);
		$message = str_replace('%LoginPassword%', $LoginPassword, $message);

		$mail = new PHPMailer;
		$mail->isSMTP();
		$mail->CharSet='UTF-8';
		$mail->SMTPAuth = true;

		$mail->Host = getenv('MAIL_HOST');
		$mail->Port = getenv('MAIL_PORT');
		$mail->SMTPSecure = getenv('MAIL_ENCRYPTION');
		$mail->Username = getenv('MAIL_USERNAME');
		$mail->Password = getenv('MAIL_PASSWORD');
		$mail->setFrom(getenv('MAIL_FROM'), 'Allian Translate');
		$mail->addReplyTo(getenv('MAIL_REPLY_TO'), 'Allian Translate');

		$mail->addAddress($email, $FName);
		$mail->IsHTML(true);
		$mail->Subject = 'Allian Translate new password.';
		$mail->MsgHTML($message);
		if (!$mail->send()) {
		   return false;
		} else {
		    return true;
		}
	}

	/**
	 *
	 * Block comment
	 *
	 */
	public function userValues($customer){
		$jsonArray = array();
		$fname = $customer->getValueEncoded('FName');
		$lname = $customer->getValueEncoded('LName');
		$jsonArray['status'] = 1;
		$jsonArray['fname'] = $fname;
		$jsonArray['lname'] = $lname;
		$jsonArray['userMessage'] = "Authentication Successfull. Welcome $fname $lname.";
		return $jsonArray;
	}

	/**
	 *
	 * Block comment
	 *
	 */
	public function emailValues($customer){
		$jsonArray = array();
		$jsonArray['status'] = 1;
		$jsonArray['userMessage'] = "New password has been sent to your e-mail address. Please check your e-mail to retrieve your password";
		return $jsonArray;
	}
}