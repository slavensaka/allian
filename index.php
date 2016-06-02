<?php // ALTER TABLE CustLogin ADD COLUMN jwt_token VARCHAR(250) NULL AFTER token;
//Stavi env na server
// PhPassword also hash and old passwords
// Potrebno je storat u app jwt_token i CustomerID.
require __DIR__ . '/vendor/autoload.php';

use Allian\Http\Controllers\Controller;
use Allian\Http\Controllers\CustLoginController;
use Allian\Http\Controllers\LangPairController;
use Allian\Http\Controllers\StripeController;
use Allian\Http\Controllers\DeveloperController;
use Allian\Http\Controllers\ConferenceScheduleController;
use Allian\Http\Controllers\TwilioController;
use Allian\Http\Controllers\LangListController;

$dotenv = new Dotenv\Dotenv(__DIR__);
$dotenv->load();

$klein = new \Klein\Klein();
// ==========================================================================
// TODO Style render views 404, 405, error
// ==========================================================================
$klein->onHttpError(function ($code, $klein, $matched, $methods, $exception) {
    switch ($code) {
        case 404:
        	$klein->service()->render("/resources/views/errors/404.php");
            break;
        case 405:
        	$klein->service()->render("/resources/views/errors/405.php");
            break;
        default:
        	$klein->service()->render("/resources/views/errors/error.php");
    }
});

$klein->respond(function ($request, $response, $service, $app) use ($klein) {
    $klein->onError(function ($klein, $err_msg) {
    	$base64Encrypted = Controller::encryptValues(json_encode(Controller::errorJson($err_msg)));
    	// return  $klein->response()->json($klein->request()->params());
       	return $klein->response()->json(array('data' => $base64Encrypted));
    });
});

$klein->with('/testgauss', function() use ($klein){
	// // if(is_callable(array($custLogin, 'testing'))) echo "JE"; else echo "Nije";
	$custLogin = new CustLoginController();
	$klein->respond('POST', '/login', array($custLogin, 'postLogin'));
	$klein->respond('POST', '/register', array($custLogin, 'postRegister'));
	$klein->respond('POST', '/forgot', array($custLogin, 'postForgot'));
	$klein->respond('POST', '/viewProfile', array($custLogin, 'viewProfile'));
	$klein->respond('POST', '/updateProfile', array($custLogin, 'updateProfile'));
	$klein->respond('POST', '/telephonicAccess', array($custLogin, 'telephonicAccess'));
	$klein->respond('GET', '/terms', array($custLogin, 'getTerms'));
	$klein->respond('POST', '/logout', array($custLogin, 'logout'));
	$klein->respond('POST', '/keepLoggedIn', array($custLogin, 'keepLoggedIn'));
	$klein->respond('GET', '/support', array($custLogin, 'support'));

	$langPair = new LangPairController();
	$klein->respond('GET', '/langPairTrans', array($langPair, 'langPairTrans'));

	$conferenceSchedule = new ConferenceScheduleController();
	$klein->respond('POST', '/getTimezones', array($conferenceSchedule, 'getTimezones'));

	$stripe = new StripeController();
	$klein->respond('POST', '/updateStripe', array($stripe, 'updateStripe'));

	$langList = new LangListController();
	$klein->respond('GET', '/langNames', array($langList, 'langNames'));

	$twilio = new TwilioController();
	$klein->respond('GET', '/twilio', array($twilio, 'twilio'));

	$developer = new DeveloperController();
	$klein->respond('GET', '/renderdocs', array($developer, 'renderDocs')); // FOR TESTING
	$klein->respond('GET', '/devEncryptJson', array($developer, 'devEncryptJson'));
	$klein->respond('GET', '/devDecryptJson', array($developer, 'devDecryptJson'));
	$klein->respond('GET', '/devGenerateAuthToken', array($developer, 'devGenerateAuthToken'));
	$klein->respond('POST', '/tester', array($developer, 'tester'));
});

$klein->dispatch();