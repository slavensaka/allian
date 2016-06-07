<?php
// ALTER TABLE CustLogin ADD COLUMN jwt_token VARCHAR(250) NULL AFTER token;
// TODO PhPassword also hash and old passwords
require __DIR__ . '/vendor/autoload.php';

use Allian\Http\Controllers\Controller;
use Allian\Http\Controllers\CustLoginController;
use Allian\Http\Controllers\LangPairController;
use Allian\Http\Controllers\StripeController;
use Allian\Http\Controllers\DeveloperController;
use Allian\Http\Controllers\ConferenceScheduleController;
use Allian\Http\Controllers\TwilioController;
use Allian\Http\Controllers\LangListController;

use Allian\Models\CustLogin;

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
    	// Catch register route & then handle the deletion of user from database.
    	if($klein->request()->uri() == '/testgauss/register'){
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

       	return $klein->response()->json(array('data' => $base64Encrypted));
    });
});

$klein->with('/testgauss', function() use ($klein){
	// if(is_callable(array($custLogin, 'testing'))) echo "JE"; else echo "Nije";
	$custLogin = new CustLoginController();
	$klein->respond('POST', '/login', array($custLogin, 'postLogin')); // NE DIRAT
	$klein->respond('POST', '/register', array($custLogin, 'postRegister')); // NE DIRAT
	$klein->respond('POST', '/forgot', array($custLogin, 'postForgot')); // NE DIRAT
	$klein->respond('POST', '/viewProfile', array($custLogin, 'viewProfile')); // NE DIRAT
	$klein->respond('POST', '/updateProfile', array($custLogin, 'updateProfile')); // NE DIRAT
	$klein->respond('POST', '/telephonicAccess', array($custLogin, 'telephonicAccess')); // NE DIRAT
	$klein->respond('GET', '/terms', array($custLogin, 'getTerms')); // NE DIRAT
	$klein->respond('POST', '/logout', array($custLogin, 'logout'));
	$klein->respond('POST', '/keepLoggedIn', array($custLogin, 'keepLoggedIn'));
	$klein->respond('GET', '/support', array($custLogin, 'support'));

	$langPair = new LangPairController();
	$klein->respond('GET', '/langPairTrans', array($langPair, 'langPairTrans')); // NE DIRAT

	$conferenceSchedule = new ConferenceScheduleController();
	$klein->respond('GET', '/getTimezones', array($conferenceSchedule, 'getTimezones')); // NE DIRAT
	$klein->respond('POST', '/schedulePartOne', array($conferenceSchedule, 'schedulePartOne'));
	$klein->respond('POST', '/schedulePartTwo', array($conferenceSchedule, 'schedulePartTwo'));
	$klein->respond('POST', '/scheduleFinal', array($conferenceSchedule, 'scheduleFinal'));


	$stripe = new StripeController();
	$klein->respond('POST', '/updateStripe', array($stripe, 'updateStripe')); // NE DIRAT
	$klein->respond('POST', '/viewStripe', array($stripe, 'viewStripe')); // NE DIRAT

	$langList = new LangListController();
	$klein->respond('POST', '/langNames', array($langList, 'langNames'));

	$twilio = new TwilioController();
	$klein->respond('GET', '/twilio', array($twilio, 'twilio'));

	$developer = new DeveloperController();
	$klein->respond('GET', '/renderdocs', array($developer, 'renderDocs')); // FOR TESTING
	$klein->respond('GET', '/devEncryptJson', array($developer, 'devEncryptJson'));
	$klein->respond('GET', '/devDecryptJson', array($developer, 'devDecryptJson'));
	$klein->respond('GET', '/devGenerateAuthToken', array($developer, 'devGenerateAuthToken'));
	$klein->respond('GET', '/tester', array($developer, 'tester'));


	$klein->respond('GET', '/tester1', function ($request, $response, $service, $app) {

		// $all_headers = $request->headers()->get('token');//DIT it
		// $all_headers = $request->headers()->all();//DIT it
		// $all_headers = $request->param('novi');
		// return $response->json($all_headers);






	});


});

$klein->dispatch();