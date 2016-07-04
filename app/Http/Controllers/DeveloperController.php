<?php

namespace Allian\Http\Controllers;

use \Dotenv\Dotenv;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\DomainException;
use Firebase\JWT\BeforeValidException;
use RNCryptor\Encryptor;
use RNCryptor\Decryptor;
use Database\Connect;
use Allian\Helpers\ArrayValues;
use Services_Twilio;
use Services_Twilio_TinyHttp;
use Services_Twilio_Twiml;

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
	 * POST tester
	 *
	 */
	public function postTester($request, $response, $service, $app){
		return "POSTTESTER";
	}

	/**
	 *
	 * GET tester
	 *
	 */
	public function getTester($request, $response, $service, $app){
		return "GetTester";

	}

	/**
	 *
	 * TEST
	 *
	 */
	public function test($request, $response, $service, $app){
		$service->render('./resources/views/misc/telephones.html');
	}

}






