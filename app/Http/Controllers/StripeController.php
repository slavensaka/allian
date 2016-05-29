<?php

namespace Allian\Http\Controllers;

use Stripe\Stripe;
use Stripe\Token;
use \Dotenv\Dotenv;
use Stripe\Customer;

class StripeController extends Controller {

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
	 *
	 * Block comment
	 *
	 */
	public function updateStripe($request, $response, $service, $app) {
		Stripe::setApiKey(getenv('STRIPE_KEY'));
		// $cu = \Stripe\Customer::retrieve($customer_id);
		// $cu->source = $_POST['stripeToken']; // obtained with Checkout
  //   	$cu->save();

		$createTestToken = \Stripe\Token::create(array("card" => array("number" => "4242424242424242", "exp_month" => 5, "exp_year" => 2017, "cvc" => "314")));

		return $createTestToken;
	}

	/**
	 *
	 * Block comment
	 *
	 */
	public function chargeCustomer($request, $response, $service, $app){
		Stripe::setApiKey(getenv('STRIPE_KEY'));
		// $myCard = array('number' => '4242424242424242', 'exp_month' => 8, 'exp_year' => 2018);
		// $charge = \Stripe\Charge::create(array('card' => $myCard, 'amount' => 2000, 'currency' => 'usd'));
		// return $charge;
	}
}

