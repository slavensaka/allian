<?php

namespace Allian\Http\Controllers;

use Allian\Models\CustLogin;
use Firebase\JWT\JWT;
use \Dotenv\Dotenv;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\DomainException;
use Firebase\JWT\BeforeValidException;
use RNCryptor\Encryptor;
use RNCryptor\Decryptor;
use Stripe\Stripe;


class StripeController extends Controller {

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

