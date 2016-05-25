<?php

namespace Allian\Http\Controllers;

// require getcwd() .  '/vendor/autoload.php';

use Firebase\JWT\JWT;
use \Dotenv\Dotenv;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\DomainException;
use Firebase\JWT\BeforeValidException;
use RNCryptor\Encryptor;
use RNCryptor\Decryptor;
use Stripe\Stripe;
use Stripe\Token;
use Stripe\Customer;

class StripeController extends Controller {

	public function createToken($data){
		Stripe::setApiKey(getenv('STRIPE_API_KEY_TEST'));
		// Create a token for customer credit card details
		$result = Token::create( array( "card" => array(
			"name" => $data['sname'], "number" => $data['number'], "exp_month" => $data['exp_month'],
			"exp_year" => $data['exp_year'], "cvc" => $data['cvc'])));
		return $result['id'];
	}

	public function createCustomer($email, $token){
		$customer = Customer::create(array("description" => "Gauss, Customer for $email email", "source" => $token));
  		return $customer['id'];
	}

	public function updateStripe($request, $response, $service, $app) {
		Stripe::setApiKey(getenv('STRIPE_API_KEY_TEST'));
		// $cu = \Stripe\Customer::retrieve($customer_id);
		// $cu->source = $_POST['stripeToken']; // obtained with Checkout
  //   	$cu->save();

		$createTestToken = \Stripe\Token::create(array("card" => array("number" => "4242424242424242", "exp_month" => 5, "exp_year" => 2017, "cvc" => "314")));

		return $createTestToken;
	}

	public function chargeCustomer($request, $response, $service, $app){
		Stripe::setApiKey(getenv('STRIPE_API_KEY_TEST'));
		// $myCard = array('number' => '4242424242424242', 'exp_month' => 8, 'exp_year' => 2018);
		// $charge = \Stripe\Charge::create(array('card' => $myCard, 'amount' => 2000, 'currency' => 'usd'));
		// return $charge;
	}
}

