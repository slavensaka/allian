<?php
require __DIR__ . '/vendor/autoload.php';

use Allian\Http\Controllers\Controller;
use Allian\Http\Controllers\CustLoginController;
use Allian\Http\Controllers\CallIdentifyController;

$dotenv = new Dotenv\Dotenv(__DIR__);
$dotenv->load();

//Routing
$klein = new \Klein\Klein();

$klein->onHttpError(function ($code, $router,$service) {
    switch ($code) {
        case 404:
            $router->response()->json(Controller::errorJson('Page not found! ' . $code));
            break;
        case 405:
            $router->response()->json(Controller::errorJson('Method not allowed! ' . $code . '!'));
            break;
        default:
            $router->response()->json(Controller::errorJson('General error ' . $code));
    }
});

$klein->respond(function ($request, $response, $service, $app) use ($klein) {
    // Handle exceptions => flash the message and redirect to the referrer
    $klein->onError(function ($klein, $err_msg) {
       	return  $klein->response()->json(Controller::errorJson($err_msg));
    });
});

	$klein->with('/testgauss', function() use ($klein){
		$custLogin = new CustLoginController();
		// // if(is_callable(array($custLogin, 'testing'))) echo "JE"; else echo "Nije";

		$klein->respond('POST', '/login', array($custLogin, 'postLogin'));
		$klein->respond('POST', '/register', array($custLogin, 'postRegister'));

		$klein->respond('POST', '/forgot', array($custLogin, 'postForgot'));

		$klein->respond('GET', '/terms', array($custLogin, 'getTerms'));
		$klein->respond('GET', '/renderdocs', array($custLogin, 'renderDocs')); // FOR TESTING
	});

$klein->dispatch();

// echo base64_encode("axqF3RBxut"); //YXhxRjNSQnh1dA==
// include('gmail.php');
// include('connection.php'); // FOR TESTING

//Works
// \Stripe\Stripe::setApiKey('sk_test_AM1QoITYp8ZBFgSkRY4Fruby');
// $myCard = array('number' => '4242424242424242', 'exp_month' => 8, 'exp_year' => 2018);
// $charge = \Stripe\Charge::create(array('card' => $myCard, 'amount' => 2000, 'currency' => 'usd'));
// echo $charge;