<?php

namespace Allian\Http\Controllers;

use \Dotenv\Dotenv;
use Allian\Models\CustLogin;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\DomainException;
use Firebase\JWT\BeforeValidException;
use RNCryptor\Encryptor;
use RNCryptor\Decryptor;
use Database\Connect;
use Allian\Helpers\Mail;
use Allian\Helpers\ArrayValues;
use Services_Twilio;
use Services_Twilio_TinyHttp;
use Services_Twilio_Twiml;
use Services_Twilio_Capability;
use Allian\Helpers\TwilioConference\ConferenceFunctions as ConfFunc;

class DeveloperController extends Controller {

	/**
	 *
	 * Block comment
	 *
	 */
	public function renderDocs($request, $response, $service, $app){
		if (!isset($_SERVER['PHP_AUTH_USER'] ) || !isset( $_SERVER['PHP_AUTH_PW']) ||
			$_SERVER['PHP_AUTH_USER'] != getenv('BASIC_AUTH_USER') || $_SERVER['PHP_AUTH_PW'] != getenv('BASIC_AUTH_PASS')) {
    		header('WWW-Authenticate: Basic realm="NO auth!"');
    		header('HTTP/1.0 401 Unauthorized');
    		exit;
    	} else {
       		$service->render('./docs/index.html');
    	}
	}

	/**
     * @ApiDescription(section="DevGenerateAuthToken", description="Generate a jwt token, Not used anymore.")
     * @ApiMethod(type="get")
     * @ApiRoute(name="/testgauss/devGenerateAuthToken")
     * @ApiBody(sample="{ 'data': ''}")
     * @ApiReturnHeaders(sample="HTTP 200 OK")
     * @ApiReturn(type="object", sample="{'jwtToken': '', 'tokenContent': '' }")
     */
	public function devGenerateAuthToken($request, $response, $service, $app){
		$secretKey = base64_decode(getenv('jwtKey'));
		$tokenId    = base64_encode(mcrypt_create_iv(32));
	    $issuedAt   = time();
	    $notBefore  = $issuedAt;
	    $expire     = $notBefore + 1209600;
	    $serverName = $_SERVER['SERVER_NAME'];
	    $data = array(
	        'iat'  => $issuedAt,         // Issued at: time when the token was generated
	        'jti'  => $tokenId,          // Json Token Id: an unique identifier for the token
	        'iss'  => $serverName,       // Issuer
	        'nbf'  => $notBefore,        // Not before
	        'exp'  => $expire,           // Expire
	        // 'data' => $dataArray
	    );
	    $jwt = JWT::encode($data, $secretKey, 'HS512');
	    return $response->json(array('jwtToken' => $jwt, 'tokenContent' => $data));
	}

	/**
     * @ApiDescription(section="DevEncryptJson", description="Developer used route for easy encrypting json request data. Input json value into field in sandbox are retrieve a encrypted API used data string for development purposes.Example {'CustomerID': 720}")
     * @ApiMethod(type="get")
     * @ApiRoute(name="/testgauss/devEncryptJson")
     * @ApiBody(sample="{
    	'CustomerID': '800'
  		}")
     * @ApiParams(name="data", type="object", nullable=false, description="")
     * @ApiReturnHeaders(sample="HTTP 200 OK")
     * @ApiReturn(type="object", sample="{
  		'json': {
    	'CustomerID': '800'
  		},
  		'encrypted': 'AwHiowfxnX8Hkr0two0lSmdI1epM4HfpGy3OBURIg4MuO1aqAVHfuQWoRUL0q4Eaio7BXrwsKmAAorWPF+JhSkcldsoiU4Xx8/BjrlRebbJKE2yz1yIFMSXdmloCH07ghLc='
		}")
     */
	public function devEncryptJson($request, $response, $service, $app){
		$json = $request->data;
		$password = getenv('CRYPTOR');
		$cryptor = new \RNCryptor\Encryptor();
		$base64Encrypted = $cryptor->encrypt($json, $password);
		$json = json_decode($json);
		return $response->json(array('json' => $json, 'encrypted' => $base64Encrypted));
	}

	/**
     * @ApiDescription(section="DevDecryptJson", description="Decrypt the encrypted data into plaintext. So if json was encrypted, retrieve the json in the encrypted data. Used for development purposes.")
     * @ApiMethod(type="get")
     * @ApiRoute(name="/testgauss/devDecryptJson")
     * @ApiBody(sample="{ 'data': 'AwHiowfxnX8Hkr0two0lSmdI1epM4HfpGy3OBURIg4MuO1aqAVHfuQWoRUL0q4Eaio7BXrwsKmAAorWPF+JhSkcldsoiU4Xx8/BjrlRebbJKE2yz1yIFMSXdmloCH07ghLc='}")
     * @ApiParams(name="data", type="object", nullable=false, description="")
     * @ApiReturnHeaders(sample="HTTP 200 OK")
     * @ApiReturn(type="object", sample="{
  		'data': 'AwHiowfxnX8Hkr0two0lSmdI1epM4HfpGy3OBURIg4MuO1aqAVHfuQWoRUL0q4Eaio7BXrwsKmAAorWPF+JhSkcldsoiU4Xx8/BjrlRebbJKE2yz1yIFMSXdmloCH07ghLc=',
  		'decrypted': {
    	'CustomerID': '800'
  		}
		}")
     */
	public function devDecryptJson($request, $response, $service, $app){
		$data = $request->data;
		$password = getenv('CRYPTOR');
		$cryptor = new \RNCryptor\Decryptor();
		$plaintext = $cryptor->decrypt($data, $password);
		$json = json_decode($plaintext);
		return $response->json(array('data' => $data, 'decrypted' => $json));
	}

	/**
	 *
	 * Block comment
	 *
	 */
	public function sendSms(){
		$http = new Services_Twilio_TinyHttp('https://api.twilio.com', array('curlopts' => array(CURLOPT_SSL_VERIFYPEER => false)));
		$version = '2010-04-01';
		$accountSid = getenv('S_TEST_TWILIO_SID');
		$authToken  = getenv('S_TEST_TWILIO_TOKEN');
		$appSid     = getenv('S_TEST_TWILIO_APP_SID');
		$client = new Services_Twilio($accountSid, $authToken, $version, $http);
		$client->account->messages->create(array(
		    'To' => '+385919249906',
		    'From' => '+12014642721',
		    'Body' => "Hey, it was a success send!",
		));
	}

	/**
	 *
	 * Block comment
	 *
	 */
	public function sendCall($request, $response, $service, $app){
		$http = new Services_Twilio_TinyHttp('https://api.twilio.com', array('curlopts' => array(CURLOPT_SSL_VERIFYPEER => false)));
		$version = '2010-04-01';
		$sid = getenv('S_TEST_TWILIO_SID');
		$token = getenv('S_TEST_TWILIO_TOKEN');
		$testPhone = getenv('S_TEST_TWILIO');
		$client = new Services_Twilio($sid, $token, $version, $http);
		$twiml_url = 'https://alliantranslate.com/testgauss/twilioConference';
		$call = $client->account->calls->create("+15005550006", "+14108675309", $twiml_url, array());
		return $call;
	}

	/**
	 *
	 * +385 91 924 9906 MOJ BROJ
	 * Twilio browser client for Twiml testing
	 *
	 */
	public function incoming($request, $response, $service, $app){
		$accountSid = getenv('S_TEST_TWILIO_SID');
		$authToken  = getenv('S_TEST_TWILIO_TOKEN');
		$appSid     = getenv('S_TEST_TWILIO_APP_SID');
		$capability = new Services_Twilio_Capability($accountSid, $authToken);
		$capability->allowClientOutgoing($appSid);
		$capability->allowClientIncoming('jenny');
		$token = ConfFunc::generateCapabilityToken('jenny');
		$service->token = $token;
		$service->render('./resources/views/twilio/test/incoming.php');
	}

	/**
	 *
	 * For testing
	 *
	 */
	function addtofile($file, $data){
		$server = trim($_SERVER['HTTP_HOST']);
		$server=trim($server);
		if($server == "localhost"){
			return file_put_contents("misc/testReqs/FirstRequestFile" . time(). ".txt", json_encode($data));
		}
	}

	/**
	 *
	 * For testing
	 *
	 */
	function addtofilePrepayment($file, $data){
		$server = trim($_SERVER['HTTP_HOST']);
		$server=trim($server);
		if($server == "localhost"){
			return file_put_contents("misc/testReqs/Prepayment" . time(). ".txt", json_encode($data));
		}
	}

	/**
	 *
	 * For testing
	 *
	 */
	function addtofilePairQueue($file, $data){
		$server = trim($_SERVER['HTTP_HOST']);
		$server=trim($server);
		if($server == "localhost"){
			return file_put_contents("misc/testReqs/PairIDQueue" . time(). ".txt", json_encode($data));
		}
	}

	/**
	 *
	 * POST tester
	 *
	 */
	public function postTester($request, $response, $service, $app){
		$twilioToken = $request->twilioToken;
		$from=$_REQUEST['To'];
		$from=$_REQUEST['From'];
		// $IPID = str_replace("client:","",$from);
		// $result = strpos($from,"client:");
		return $twilioToken;
	}

	/**
	 *
	 * GET tester
	 *
	 */
	public function getTester($request, $response, $service, $app){
		$to = array(getenv('SLAVEN_EMAIL'), 'system0@net.hr');
		Mail::simpleLocalMail("subject", "content", $to);
		$con = Connect::con();
		$customer = CustLogin::get_customer(800);
		return $response->json(gettype($customer['CustomerID']));
	}

	/**
	 *
	 * TEST
	 *
	 */
	public function test($request, $response, $service, $app){
		// $service->render('./resources/views/misc/telephones.html');
		$response = new Services_Twilio_Twiml;
			$response->say('The interpreting service is not available between the selected two language pairs at this time.');
			$response->hangup();
			return  $response;
	}

	public function phoneFormat($request, $response, $service, $app){
		$http = new Services_Twilio_TinyHttp('https://api.twilio.com', array('curlopts' => array(CURLOPT_SSL_VERIFYPEER => false)));
		$version = '2010-04-01';
		$sid = getenv('S_TEST_TWILIO_SID');
		$token = getenv('S_TEST_TWILIO_TOKEN');
		$testPhone = getenv('S_TEST_TWILIO');
		$client = new Services_Twilio($sid, $token, $version, $http);
		$twiml_url = 'https://alliantranslate.com/testgauss/twilioConference';
		$call = $client->account->calls->create("+15005550006", "+1410d8609", $twiml_url, array());
		return $call;
	}





}
