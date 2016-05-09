<?php
require __DIR__ . '/vendor/autoload.php';


use Allian\Http\Controllers\CustLoginController;
use Allian\Http\Controllers\CallIdentifyController;

$dotenv = new Dotenv\Dotenv(__DIR__);
$dotenv->load();

//Routing
$klein = new \Klein\Klein();

$klein->onHttpError(function ($code, $router,$service) {
    switch ($code) {
        case 404:
            $router->response()->body('Page not found! ' . $code . '!');
            break;
        case 405:
            $router->response()->body('Method not allowed! ' . $code . '!');
            break;
        default:
            $router->response()->body('General error ' . $code);
    }
});
// $klein->onError(function ($klein, $message) {
//     echo $message;
// });


$klein->respond(function ($request, $response, $service, $app) use ($klein) {
    // Handle exceptions => flash the message and redirect to the referrer
    $klein->onError(function ($klein, $err_msg) {
    	$jsonArray = array();
		$jsonArray['status'] = 0;
		$jsonArray['userMessage'] = $err_msg;
       	return  $klein->response()->json($jsonArray);
    });
});

$klein->with('/testgauss', function() use ($klein){

	$custLogin = new CustLoginController();
	// // if(is_callable(array($custLogin, 'testing'))) echo "JE"; else echo "Nije";
	$klein->respond('GET', '/terms', array($custLogin, 'getTerms'));
	$klein->respond('GET', '/renderdocs', array($custLogin, 'renderDocs')); // FOR TESTING

	$klein->respond('POST', '/login', array($custLogin, 'postLogin'));
	$klein->respond('POST', '/register', array($custLogin, 'postRegister'));
	$klein->respond('POST', '/forgotpassword', array($custLogin, 'postForgotPassword'));

});

$klein->dispatch();

// include('connection.php'); // FOR TESTING