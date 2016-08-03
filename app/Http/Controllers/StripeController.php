<?php

namespace Allian\Http\Controllers;

use \Dotenv\Dotenv;
use Stripe\Token;
use Stripe\Stripe;
use Stripe\Charge;
use Stripe\Coupon;
use Stripe\Customer;
use Allian\Models\CustLogin;
use Allian\Models\Stripe as StripeModel;
use Allian\Http\Controllers\StripeController;

class StripeController extends Controller {

	/**
	 *
	 * Retrieve the stripe token based on the
	 *
	 */
	public function getStripeKey(){
		$server = trim($_SERVER['HTTP_HOST']);
		$server=trim($server);
		if($server=="localhost"){
			return Stripe::setApiKey(getenv('STRIPE_KEY'));
		} else if($server=="alliantranslate.com"){
			return Stripe::setApiKey(getenv('STRIPE_KEY_ALLIAN_TEST'));
		} else {
			return Stripe::setApiKey(getenv('STRIPE_KEY'));
		}
	}

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
	public function updateStripe($request, $response, $service, $app) { // DONT CHANGE
		if($request->token){
			// Validate token if not expired, or tampered with
			$this->validateToken($request->token);
			// Decrypt input data
			$data = $this->decryptValues($request->data);
			// Validate input data
			$service->validate($data['CustomerID'], 'Error: No customer id is present.')->notNull()->isInt();
			$service->validate($data['sname'], 'Error: no stripe name present.')->notNull()->isLen(3,200);
			$service->validate($data['number'], 'Error: no credit card number present.')->notNull();
			$service->validate($data['exp'], 'Error: Expiration month not present.')->notNull();
			$service->validate($data['cvc'], 'Error: Cvc not present.')->notNull();
			//Validate the jwt token in the database
			$validated = $this->validateTokenInDatabase($request->token, $data['CustomerID']);
			// If error validating token in database
			if(!$validated){
				$base64Encrypted = $this->encryptValues(json_encode($this->errorJson("Authentication problems present")));
	     		return $response->json(array('data' => $base64Encrypted));
			}
			$this->getStripeKey();
			// Stripe token then customer create
			$stripe = new StripeController();
			// Format exp_month and exp_year
			$exp = explode("/", $data['exp']);
			$exp_month = ltrim($exp[0], '0');
			$exp_year = "20" . $exp[1];
			$tokenResult = $stripe->createTokenNew($data, $exp_month, $exp_year);
			// Retrieve customer stripe token
			$customer_id = StripeModel::customerToken($data['CustomerID']);
			$cu = Customer::retrieve($customer_id['token']);
			$email = $customer_id['Email'];
			$cu->description = "Gauss:app, Customer UPDATED card with email $email.";
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
	public function viewStripe($request, $response, $service, $app){ // DONT CHANGE
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
			$this->getStripeKey();
			// Get the customer from db
			$customer = CustLogin::getCustomer($data['CustomerID']);
			// Retrieve the stripe customer from db stripe token
			$cu = Customer::retrieve($customer->getValueEncoded('token'));
			// Get the stripe customer name
			$name = explode(" ", $cu->sources->data[0]->name);
			// Get stripe exp year
			$exp_year = substr($cu->sources->data[0]->exp_year, 2);
			$rArray =  array();
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
	public function chargeCustomer($amount, $token, $email){ // DONT CHANGE
		// Check the host, and get the stripeKey based on that
		$this->getStripeKey();
		try {
	        $charge = Charge::create(array("amount" => ($amount * 100), "currency" => "usd", "customer" => $token, "description" => "Gauss:app, Customer CHARGED with email $email"));
			if ($charge->paid == true) {
				return $charge->id;
			} else {
				throw new \Exception("Payment System Error! Your payment could NOT be processed (i.e., you have not been charged) because the payment system rejected the transaction. You can try again or use another card.");
			}
		} catch (\Stripe\Error\Card $e) {
			return $e->getMessage();
		} catch (\Stripe\Error\ApiConnection $e) {
			throw new \Exception($e->getMessage());
		} catch (\Stripe\Error\InvalidRequest $e) {
			throw new \Exception($e->getMessage());
		} catch (\Stripe\Error\Api $e) {
			throw new \Exception($e->getMessage());
		} catch (\Stripe\Error\Base $e) {
			throw new \Exception($e->getMessage());
		}
	}

	/**
	 *
	 * Charge the customer with stripe token
	 *
	 */
	public function preAuthCustomer($token){ // DONT CHANGE
		$server = trim($_SERVER['HTTP_HOST']);
		$server=trim($server);
		if($server=="localhost"){
			Stripe::setApiKey(getenv('STRIPE_KEY'));
		} else if($server=="alliantranslate.com"){
			Stripe::setApiKey(getenv('STRIPE_KEY_ALLIAN_TEST'));
		} else {
			Stripe::setApiKey(getenv('STRIPE_KEY'));
		}
		try {
			// $return=shell_exec ('curl https://api.stripe.com/v1/charges  -u ' . getenv('STRIPE_KEY') . ' -d amount=3000 -d currency=usd  -d capture=false -d customer='.$token.' -d "description=Pre autherizing 30$"');
	        $result = Charge::create(array("amount" => 3000, "capture" => false, "currency" => "usd", "customer" => $token, "description" => "Gauss:app, Pre autherizing 30$"));
			if ($result->id) {
				return $result->id;
			} else {
				throw new \Exception("Payment System Error! Your payment could NOT be processed (i.e., you have not been charged) because the payment system rejected the transaction. You can try again or use another card.");
			}
		} catch (\Stripe\Error\Card $e) {
			return $e->getMessage();
		} catch (\Stripe\Error\ApiConnection $e) {
			throw new \Exception($e->getMessage());
		} catch (\Stripe\Error\InvalidRequest $e) {
			throw new \Exception($e->getMessage());
		} catch (\Stripe\Error\Api $e) {
			throw new \Exception($e->getMessage());
		} catch (\Stripe\Error\Base $e) {
			throw new \Exception($e->getMessage());
		}
	}

	/**
	 * Retrieve stripe coupon based on $coupon_code, check type of coupon
	 * Calculate the discount and return response
	 *
	 * @param string $coupon_code
	 * @return array Response
	 *         status
	 *         response
	 *		   discount
	 *         type
	 */
	public function promoCode($coupon_code){
		$server = trim($_SERVER['HTTP_HOST']);
		$server=trim($server);
		if($server=="localhost"){
			Stripe::setApiKey(getenv('STRIPE_KEY'));
		} else if($server=="alliantranslate.com"){
			Stripe::setApiKey(getenv('STRIPE_KEY_ALLIAN_TEST'));
		} else {
			Stripe::setApiKey(getenv('STRIPE_KEY'));
		}
		try {
	    	// retrieve the coupon from STRIPE System
	    	$coupon = Coupon::retrieve($coupon_code);
	    	if(is_null($coupon['amount_off'])){
	     		// Calculate the discount percent
	     		$discount_off = $coupon['percent_off'];
	     		$discount_type = "%";
	     		$response = $discount_off . "%";
	 		}else{
	         	$discount_off = $coupon['amount_off'] / 100;
	         	$discount_type = "$";
	         	$response = "$".$discount_off;
			}
			$r = array("status" => 1, "response" => $response, "discount" => $discount_off, "type" => $discount_type);
	        return $r;
    	} catch (Stripe_InvalidRequestError $e) {
	        throw new \Exception($e->getMessage());
		}
	}

	/**
	 *
	 * Create a new stripe token based on user's card info
	 *
	 */
	public function createTokenNew($data, $exp_month, $exp_year){
		$this->getStripeKey();
		$result = Token::create( array("card" => array( "name" => $data['sname'], "number" => $data['number'],
			"exp_month" => (int)$exp_month, "exp_year" => (int)$exp_year, "cvc" => $data['cvc'] )));
		// Return one time created stripe token
		return $result['id'];
	}

	/**
	 *
	 * Create a stripe customer based on the token generated
	 * 	in createTokenNew()
	 *
	 */
	public function createCustomer($email, $token){ // DONT CHANGE
		$customer = Customer::create(array( "description" => "Gauss:app, Customer CREATED with email $email", "source" => $token));
  		return $customer['id'];
	}
}

