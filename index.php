<?php
require __DIR__ . '/vendor/autoload.php';

use Allian\Http\Controllers\Controller;
use Allian\Http\Controllers\CustLoginController;
use Allian\Http\Controllers\LangPairController;
use Allian\Http\Controllers\StripeController;
use Allian\Http\Controllers\DeveloperController;

$dotenv = new Dotenv\Dotenv(__DIR__);
$dotenv->load();

//Routing
$klein = new \Klein\Klein();

// TODO Return HTTP Status code error rendered views.
//The likes of 404 Not found
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
    // If validation and expcetion errors are present on any route, catch them
    // end return a encrypted value, with status=0, and userMessage depending on
    // err_msg given with klein routing package
    $klein->onError(function ($klein, $err_msg) {
    	$base64Encrypted = Controller::encryptValues(json_encode(Controller::errorJson($err_msg)));
       	return  $klein->response()->json(array('data' => $base64Encrypted));
    });
});

$klein->with('/testgauss', function() use ($klein){
	// // if(is_callable(array($custLogin, 'testing'))) echo "JE"; else echo "Nije";
	$custLogin = new CustLoginController();
	$klein->respond('POST', '/login', array($custLogin, 'postLogin'));
	$klein->respond('POST', '/register', array($custLogin, 'postRegister'));
	$klein->respond('POST', '/forgot', array($custLogin, 'postForgot'));
	$klein->respond('GET', '/terms', array($custLogin, 'getTerms'));

	$klein->respond('POST', '/telephonicAccess', array($custLogin, 'postTelephonicAccess'));
	$klein->respond('POST', '/updateProfile', array($custLogin, 'updateProfile'));
	$klein->respond('POST', '/viewProfile', array($custLogin, 'viewProfile'));

	$langPair = new LangPairController();
	$klein->respond('GET', '/langPairTrans', array($langPair, 'langPairTrans'));

	$stripe = new StripeController();
	$klein->respond('POST', '/updateStripe', array($stripe, 'updateStripe'));
	// TODO MORAM SPREMIT TOKEN_ID IZ JWT U BAZU i UVIJEK PROVJERIT
	$developer = new DeveloperController();
	$klein->respond('GET', '/renderdocs', array($developer, 'renderDocs')); // FOR TESTING
	$klein->respond('GET', '/devEncryptJson', array($developer, 'devEncryptJson'));
	$klein->respond('GET', '/devDecryptJson', array($developer, 'devDecryptJson'));
	$klein->respond('GET', '/devGenerateAuthToken', array($developer, 'devGenerateAuthToken'));
	$klein->respond('POST', '/tester', array($developer, 'tester'));
});

$klein->dispatch();

// include('gmail.php');
// include('connection.php'); // FOR TESTING

