<?php
require __DIR__ . '/vendor/autoload.php';

use Allian\Http\Controllers\Controller;
use Allian\Http\Controllers\CustLoginController;
use Allian\Http\Controllers\TranslationOrdersController;
use Allian\Http\Controllers\LangPairController;
use Allian\Http\Controllers\StripeController;
use Allian\Http\Controllers\DeveloperController;
use Allian\Http\Controllers\ConferenceScheduleController;
use Allian\Http\Controllers\TwilioController;
use Allian\Http\Controllers\LangListController;
use Allian\Http\Controllers\OrderOnsiteInterpreterController;
use Allian\Models\CustLogin;

$dotenv = new Dotenv\Dotenv(__DIR__);
$dotenv->load();

$klein = new \Klein\Klein();

$klein->onHttpError(function ($code, $klein, $matched, $methods, $exception) {
	$type = $klein->request()->method();

	if($type == 'GET'){
		return $klein->response()->json(Controller::errorJson("Type of HTTP status code error: $code"));
	} elseif($type == 'POST'){
		return $klein->response()->json(array("data" => Controller::encryptValues(json_encode(Controller::errorJson("Type of HTTP status code error: $code")))));
	} else {
	    return $klein->response()->json(Controller::errorJson("Type of HTTP status code error: $code"));
	}
});

$klein->respond(function ($request, $response, $service, $app) use ($klein) {
    $klein->onError(function ($klein, $err_msg) {
	    $type = $klein->request()->method();
		if($type == 'GET'){
			return $klein->response()->json(Controller::errorJson($err_msg));
		} elseif($type == 'POST'){
			// Retrieve only the request real uri
    		$p = explode("?", $klein->request()->uri());
    		// Catch register route & then handle the deletion of user from database.
			if($p[0] == '/testgauss/register'){
				// Get data from request
				$encrypted = $klein->request()->param('data');
				// Decrypt it
				$password = getenv("CRYPTOR");
				$decryptor = new \RNCryptor\Decryptor();
				$plaintext = $decryptor->decrypt($encrypted, $password);
				$data = json_decode($plaintext, true);
				// Find user in database with email and password
				$customer = CustLogin::authenticate($data['email'], $data['password']);
				// Find him again and delete him, BECAUSE his stripe is not valid
				if($customer){
					$deleteCustomer = CustLogin::deleteCustomerByEmail($data['email'], $customer->getValueEncoded('LoginPassword'));
				}
			}
			$base64Encrypted = Controller::encryptValues(json_encode(Controller::errorJson($err_msg)));
	    	return $klein->response()->json(array('data' => $base64Encrypted));
		}
    });
});

$klein->with('/testgauss', function() use ($klein){
	// if(is_callable(array($custLogin, 'testing'))) echo "JE"; else echo "Nije";
	$custLogin = new CustLoginController();
	$klein->respond('POST', '/login', array($custLogin, 'postLogin'));
	$klein->respond('POST', '/register', array($custLogin, 'postRegister'));
	$klein->respond('POST', '/forgot', array($custLogin, 'postForgot'));
	$klein->respond('POST', '/viewProfile', array($custLogin, 'viewProfile'));
	$klein->respond('POST', '/updateProfile', array($custLogin, 'updateProfile'));
	$klein->respond('POST', '/telephonicAccess', array($custLogin, 'telephonicAccess'));
	$klein->respond('POST', '/telephonicAccessEmail', array($custLogin, 'telephonicAccessEmail'));
	$klein->respond('GET', '/terms', array($custLogin, 'getTerms'));
	$klein->respond('POST', '/logout', array($custLogin, 'logout'));
	$klein->respond('POST', '/keepLoggedIn', array($custLogin, 'keepLoggedIn'));
	$klein->respond('GET', '/support', array($custLogin, 'support'));

	$langPair = new LangPairController();
	$klein->respond('GET', '/langPairTrans', array($langPair, 'langPairTrans'));

	$conferenceSchedule = new ConferenceScheduleController();
	$klein->respond('GET', '/getTimezones', array($conferenceSchedule, 'getTimezones'));
	$klein->respond('POST', '/schedulePartOne', array($conferenceSchedule, 'schedulePartOne'));
	$klein->respond('POST', '/schedulePartTwo', array($conferenceSchedule, 'schedulePartTwo'));
	$klein->respond('POST', '/scheduleFinal', array($conferenceSchedule, 'scheduleFinal'));

	$stripe = new StripeController();
	$klein->respond('POST', '/updateStripe', array($stripe, 'updateStripe'));
	$klein->respond('POST', '/viewStripe', array($stripe, 'viewStripe'));

	$langList = new LangListController();
	$klein->respond('GET', '/langNames', array($langList, 'langNames'));

	$twilio = new TwilioController();
	$klein->respond('GET', '/twilioConference', array($twilio, 'twilioConference'));
	$klein->respond('GET', '/addNewMember', array($twilio, 'addNewMember'));
	$klein->respond('GET', '/incoming', array($twilio, 'incoming')); // TESTING
	$klein->respond('POST', '/incomingInbound', array($twilio, 'incomingInbound')); // TESTING
	$klein->respond('GET', '/twilioCall', array($twilio, 'twilioCall')); // TESTING

	$translationOrders = new TranslationOrdersController();
	$klein->respond('POST', '/orderSummary', array($translationOrders, 'orderSummary'));
	$klein->respond('POST', '/orderSummaryDetails', array($translationOrders, 'orderSummaryDetails'));

	$orderOnSiteInterpreter = new OrderOnsiteInterpreterController();
	$klein->respond('POST', '/scheduledSessions', array($orderOnSiteInterpreter, 'scheduledSessions'));
	$klein->respond('POST', '/scheduledSessionsDetails', array($orderOnSiteInterpreter, 'scheduledSessionsDetails'));

	$developer = new DeveloperController();
	$klein->respond('GET', '/renderdocs', array($developer, 'renderDocs'));
	$klein->respond('GET', '/devEncryptJson', array($developer, 'devEncryptJson'));
	$klein->respond('GET', '/devDecryptJson', array($developer, 'devDecryptJson'));
	$klein->respond('GET', '/devGenerateAuthToken', array($developer, 'devGenerateAuthToken'));
	$klein->respond('POST', '/postTester', array($developer, 'postTester'));
	$klein->respond('GET', '/getTester', array($developer, 'getTester'));
	$klein->respond('GET', '/test', array($developer, 'test'));

});

$klein->dispatch();