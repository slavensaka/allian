<?php

namespace Allian\Http\Controllers;

use Stripe\Token;
use Stripe\Stripe;
use \Dotenv\Dotenv;
use Stripe\Customer;
use Stripe\Charge;
use Allian\Models\Stripe as StripeModel;
use Allian\Http\Controllers\StripeController;
use Allian\Models\CustLogin;

class StripeController extends Controller {

	/**
     * @ApiDescription(section="UpdateStripe", description="Update customer information for card.")
     * @ApiMethod(type="post")
     * @ApiRoute(name="/testgauss/updateStripe")
     * @ApiBody(sample="{'data': {
	    'CustomerID': '800',
	    'sname': 'Pero Perić',
	    'number': '4012888888881881',
	    'exp': '05/18',
	    'cvc': '314'
	  },
     'token': 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzUxMiJ9.eyJpYXQiOjE0NjQ2MDE1MTUsImp0aSI6InAwaFpucWxqaUpqWStDdmdrb3c0MjJITTQ1TkYweFVobCtHU2lWZFwvUlN3PSIsImlzcyI6ImxvY2FsaG9zdCIsIm5iZiI6MTQ2NDYwMTUxNSwiZXhwIjoxNDY1ODExMTE1LCJkYXRhIjp7IlN1Y2Nlc3MiOiJTdWNjZXNzIn19.wwxlnjSCmInwNYinJ-LIyHMOys3oYTeoQem2MJTfgNREFZ8rcDB9uZ61Hw6vHIVMh_8BKzJUKS-_0nwhfrJVxQ'}")
     *@ApiParams(name="data", type="string", nullable=false, description="Data")
     @ApiParams(name="token", type="string", nullable=false, description="Autentication token for users autentication.")
     * @ApiReturnHeaders(sample="HTTP 200 OK")
     * @ApiReturn(type="string", sample="{
     *  'data': {
	    'status': 1,
	    'userMessage': 'Stripe information updated.'
	  	}
     * }")
     */
	public function updateStripe($request, $response, $service, $app) {
		Stripe::setApiKey(getenv('STRIPE_KEY'));

		if($request->token){
			// Validate token if not expired, or tampered with
			$this->validateToken($request->token);
			// Decrypt input data
			$data = $this->decryptValues($request->data);
			// Validate input data
			$service->validate($data['CustomerID'], 'Error: No customer id is present.')->notNull()->isInt();
			// $service->validate($data['sname'], 'Error: no stripe name present.')->notNull()->isLen(3,200);
			// $service->validate($data['number'], 'Error: no credit card number present.')->notNull();
			// $service->validate($data['exp_month'], 'Error: Expiration month not present.')->notNull();
			// $service->validate($data['exp_year'], 'Error: Expiration year not present.')->notNull();
			// $service->validate($data['cvc'], 'Error: Cvc not present.')->notNull();

			$service->validate($data['sname'], 'Error: no stripe name present.')->notNull()->isLen(3,200);
			$service->validate($data['number'], 'Error: no credit card number present.')->notNull();
			$service->validate($data['exp'], 'Error: Expiration month not present.')->notNull();
			// $service->validate($data['exp_year'], 'Error: Expiration year not present.')->notNull();
			$service->validate($data['cvc'], 'Error: Cvc not present.')->notNull();

			//Validate the jwt token in the database
			$validated = $this->validateTokenInDatabase($request->token, $data['CustomerID']);
			// If error validating token in database
			if(!$validated){
				$base64Encrypted = $this->encryptValues(json_encode($this->errorJson("Authentication problems present")));
	     		return $response->json(array('data' => $base64Encrypted));
			}
			// Stripe token then customer create
			$stripe = new StripeController();
			// Format exp_month and exp_year
			$exp = explode("/", $data['exp']);
			$exp_month = ltrim($exp[0], '0');
			$exp_year = "20".$exp[1];
			$tokenResult = $stripe->createTokenNew($data, $exp_month, $exp_year);
			// Retrieve customer stripe token
			$customer_id = StripeModel::customerToken($data['CustomerID']);
			$cu = Customer::retrieve($customer_id['token']);
			$email = $customer_id['Email'];
			$cu->description = "Gauss:app, update of card for user $email.";
			$cu->source = $tokenResult;
	    	$cu->save();
	    	// Format response
			$jsonArray = array();
			$jsonArray['status'] = 1;
			$jsonArray['userMessage'] = "Stripe information updated.";
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
     * @ApiDescription(section="ViewStripe", description="View stripe credit card information.")
     * @ApiMethod(type="post")
     * @ApiRoute(name="/testgauss/viewStripe")
     * @ApiBody(sample="{'data': {
    	'CustomerID': '800'
  		},
     'token': 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzUxMiJ9.eyJpYXQiOjE0NjUyODA1MDIsImp0aSI6IlVheUZlOUJTcEE5empHWUNneVpnNTJEVFYzRXZ4NFE5YXNKdTQ4MHdEY289IiwiaXNzIjoibG9jYWxob3N0IiwibmJmIjoxNDY1MjgwNTAyLCJleHAiOjE0NjY0OTAxMDIsImRhdGEiOnsiU3VjY2VzcyI6IlN1Y2Nlc3MifX0.qkGUG0WdaW_Q1aysAgfaEC5300Hk4X9VFEZRGsTOxE4X-P27EdCEfAnDPY0SaXD_VfsHiVYaGwwKxO-Bz0N8Yg'}")
     *@ApiParams(name="data", type="string", nullable=false, description="Data")
     @ApiParams(name="token", type="string", nullable=false, description="Autentication token for users autentication.")
     * @ApiReturnHeaders(sample="HTTP 200 OK")
     * @ApiReturn(type="string", sample="{
     *  'data': {
	    'sname': 'Pero Perić',
	    'exp': '5/18',
	    'country': 'US',
	    'brand': 'Visa',
	    'number': '1881',
	    'status': 1
	  }
     * }")
     */
	public function viewStripe($request, $response, $service, $app){
		Stripe::setApiKey(getenv('STRIPE_KEY'));

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
			$customer = CustLogin::getCustomer($data['CustomerID']);

			$cu = Customer::retrieve($customer->getValueEncoded('token'));

			$name = explode(" ", $cu->sources->data[0]->name);

			$rArray =  array();
			// $rArray['fname']= $name[0];
			// $rArray['lname'] =$name[1];
			$exp_year = substr($cu->sources->data[0]->exp_year, 2);
			$rArray['sname'] = $cu->sources->data[0]->name;
			$rArray['exp'] =  $cu->sources->data[0]->exp_month . '/' . $exp_year;

			$rArray['country'] =$cu->sources->data[0]->country;
			$rArray['brand']= $cu->sources->data[0]->brand;
			$rArray['number'] =$cu->sources->data[0]->last4;
			$rArray['status'] = 1;
			// Format response
			$base64Encrypted = $this->encryptValues(json_encode($rArray));
	     	return $response->json(array('data' => $base64Encrypted));
		} else {
			$base64Encrypted = $this->encryptValues(json_encode($this->errorJson("No token provided in request")));
     		return $response->json(array('data' => $base64Encrypted));
		}
	}

	/**
	 *
	 * Charge the customer with stripe token
	 *
	 */
	public function chargeCustomer($charge_amount, $token){
		Stripe::setApiKey(getenv('STRIPE_KEY'));
		try { // "amount" => ($charge_amount) TODO
	        $charge = Charge::create(array("amount" => (30*100), "currency" => "usd", "customer" => $token,"description" => "Bit će email"));
			// Check that it was paid:
			if ($charge->paid == true) {
				return $charge->id;
				// Store the order in the database. MAYBE TODO
			} else { // Charge was not paid!
				throw new \Exception("Payment System Error! Your payment could NOT be processed (i.e., you have not been charged) because the payment system rejected the transaction. You can try again or use another card.");
			}
		} catch (\Stripe\Error\Card $e) {
		    // Card was declined.
			return $e->getJsonBody();
			 $response = $e->getMessage();
	        // echo "Credit Card not Accepted: <em>The card has been declined</em><br><br> Stripe Response: " . $response;
	        // $card = array("card"=>$_POST["card"],"cvc"=>$_POST["cvc"],"exp_month"=>$_POST["exp_month"],
			 //"exp_year"=>$_POST["exp_year"],"reason"=>$response);
	        // send_card_declined_email($con,$card);
			// $err = $e_json['error'];
			// $errors['stripe'] = $err['message'];
		} catch (\Stripe\Error\ApiConnection $e) {
			throw new \Exception($e->getJsonBody());
		} catch (\Stripe\Error\InvalidRequest $e) {
			throw new \Exception($e->getJsonBody());
		} catch (\Stripe\Error\Api $e) {
			throw new \Exception($e->getJsonBody());
		} catch (\Stripe\Error\Base $e) {
			throw new \Exception($e->getJsonBody());
		}
	}

	/**
	 *
	 * Block comment
	 *
	 */
	public function createToken($data){
		Stripe::setApiKey(getenv('STRIPE_KEY'));
		// Create a token for customer credit card details
		$result = Token::create( array("card" => array(
			"name" => $data['sname'],
			"number" => $data['number'],
			"exp_month" => $data['exp_month'],
			"exp_year" => $data['exp_year'],
			"cvc" => $data['cvc']
		)));
		// Return one time created stripe token
		return $result['id'];
	}

	/**
	 *
	 * Block comment
	 *
	 */
	public function createTokenNew($data, $exp_month, $exp_year){
		Stripe::setApiKey(getenv('STRIPE_KEY'));

		// $data['exp']
		// Create a token for customer credit card details
		$result = Token::create( array("card" => array(
			"name" => $data['sname'],
			"number" => $data['number'],
			"exp_month" => (int)$exp_month,
			"exp_year" => (int)$exp_year,
			"cvc" => $data['cvc']
		)));
		// Return one time created stripe token
		return $result['id'];
	}

	/**
	 *
	 * Block comment
	 *
	 */
	public function createCustomer($email, $token){
		// Create a stripe customer based on the token generate
		$customer = Customer::create(array(
			"description" => "Gauss:app, CustLogin with $email email",
			"source" => $token
		));
		//Return tthe customer cus_6odw... token
  		return $customer['id'];
	}
}

