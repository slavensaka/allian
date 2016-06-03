<?php

namespace Allian\Http\Controllers;

use Stripe\Token;
use Stripe\Stripe;
use \Dotenv\Dotenv;
use Stripe\Customer;
use Allian\Models\Stripe as StripeModel;
use Allian\Http\Controllers\StripeController;

class StripeController extends Controller {

	/**
	 *
	 * Block comment
	 *
	 */
	public function createToken($data){
		Stripe::setApiKey(getenv('STRIPE_KEY'));

		// $data['exp']

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

	public function createTokenNew($data, $exp_month, $exp_year){
		Stripe::setApiKey(getenv('STRIPE_KEY'));

		// $data['exp']
 // TODO IF stripe failed remove customerIF
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

	/**
     * @ApiDescription(section="UpdateStripe", description="Update customer information for card.")
     * @ApiMethod(type="post")
     * @ApiRoute(name="/testgauss/updateStripe")
     * @ApiBody(sample="{'data': 'AwEwDnY6e21y+dIb4OFbipILGf5oavrwZMWK1fAH73b3uLUPWeY6AafUyqV6pumPB99p0wXnOdnx3HOPqWH3WILPS7r7g6T1HSE2T+HINsGE1KsVfw4XGV5DGuHWn3MzR4QwCqBPz1MaALs8894RNRkCGQsewNvWGkC6sgMr0wmUPgPJsEoroLufv1ZMKngM+ExNs86NjuLknAu6jGlEpbRigIEn40fTDvEOWho4snn7p/h+/lnHq3ZVqOqGpIkAxIUkFbzV22qK+TzO4lkogT+p',
     'token': 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzUxMiJ9.eyJpYXQiOjE0NjQ2MDE1MTUsImp0aSI6InAwaFpucWxqaUpqWStDdmdrb3c0MjJITTQ1TkYweFVobCtHU2lWZFwvUlN3PSIsImlzcyI6ImxvY2FsaG9zdCIsIm5iZiI6MTQ2NDYwMTUxNSwiZXhwIjoxNDY1ODExMTE1LCJkYXRhIjp7IlN1Y2Nlc3MiOiJTdWNjZXNzIn19.wwxlnjSCmInwNYinJ-LIyHMOys3oYTeoQem2MJTfgNREFZ8rcDB9uZ61Hw6vHIVMh_8BKzJUKS-_0nwhfrJVxQ'}")
     *@ApiParams(name="data", type="object", nullable=false, description="Data")
     @ApiParams(name="token", type="object", nullable=false, description="Autentication token for users autentication.")
     * @ApiReturnHeaders(sample="HTTP 200 OK")
     * @ApiReturn(type="object", sample="{
     *  'data': 'AwE9VgXMPLiV77eah+tzjk33re4/ZMNkTqNHja+JYTJLnijcYLQJ0Tm3WyafkBLdW47Cn+3tMP74Kmw5+Ym6BOxfYd8rYL8wzaAiWbfC+yEDetjJdhF37UaL/7qOFfJbIJHPduBVUTuWcWoGybX9eiZTBtxY+tAU5A/Tv+tg1rsx1g=='
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
			$service->validate($data['sname'], 'Error: no stripe name present.')->notNull()->isLen(3,200);
			$service->validate($data['number'], 'Error: no credit card number present.')->notNull();
			$service->validate($data['exp_month'], 'Error: Expiration month not present.')->notNull();
			$service->validate($data['exp_year'], 'Error: Expiration year not present.')->notNull();
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
			$oneTimeToken = $stripe->createToken($data);
			// Retrieve customer stripe token
			$customer_id = StripeModel::customerToken($data['CustomerID']);
			$cu = Customer::retrieve($customer_id['token']);
			$email = $customer_id['Email'];
			$cu->description = "Gauss:app, update of card for user $email.";
			$cu->source = $oneTimeToken;
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
			return $response->json("No token provided");
		}
	}

	/**
	 *
	 * TODO
	 *
	 */
	public function chargeCustomer($request, $response, $service, $app){
		Stripe::setApiKey(getenv('STRIPE_KEY'));
		// $myCard = array('number' => '4242424242424242', 'exp_month' => 8, 'exp_year' => 2018);
		// $charge = \Stripe\Charge::create(array('card' => $myCard, 'amount' => 2000, 'currency' => 'usd'));
		// return $charge;
		// Check out linguist/register.php


	// 		$token = $_REQUEST['stripeToken'];
	// try{ 	// Create a Customer
	// 	$customer = Stripe_Customer::create(array("card" => $token,"description" => $desc));
	// }catch(Stripe_InvalidRequestError $e) { // The card has been declined
	// 	echo "Invalid request The card has been declined Try Again";
	// }catch(Stripe_CardError $e) {// The card has been declined
	// 	echo "Credit Card not Accepted The card has been declined Try Again";
	// }
	// if(is_null($e)){// no errors
	// 	$token = $customer->id;
	// 	$query = "update CustLogin set token='$token' where CustomerID='$cust_id'";
	// 	$result = mysqli_query($con,$query);
	// 	if($result and mysqli_affected_rows($con)>0){
	// 		...
	// 	}
	// }
	}
}

