<?php

namespace Allian\Http\Controllers;

use Firebase\JWT\JWT;
use \Dotenv\Dotenv;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\DomainException;
use Firebase\JWT\BeforeValidException;
use RNCryptor\Encryptor;
use RNCryptor\Decryptor;
use Database\Connect;

class DeveloperController extends Controller {

	/**
	 *
	 * Block comment
	 *
	 */
	public function renderDocs($request, $response, $service, $app){
    		if ( !isset($_SERVER['PHP_AUTH_USER'] ) || !isset( $_SERVER['PHP_AUTH_PW'] ) ||
    			$_SERVER['PHP_AUTH_USER'] != getenv('BASIC_AUTH_USER') || $_SERVER['PHP_AUTH_PW'] != getenv('BASIC_AUTH_PASS') ) {
        		header( 'WWW-Authenticate: Basic realm="NO auth!"' );
        		header( 'HTTP/1.0 401 Unauthorized' );
        		exit;
    	} else {
       		$service->render('./docs/index.html');
    	}
	}

	/**
     * @ApiDescription(section="DevEncryptJson", description="Developer used route for easy encrypting json request data. Input json value into field in sandbox are retrieve a encrypted API used data string for development purposes.Example {'CustomerID': 720}")
     * @ApiMethod(type="get")
     * @ApiRoute(name="/testgauss/devEncryptJson")
     * @ApiBody(sample="{ 'data': 'Some json string'}")
     * @ApiParams(name="data", type="object", nullable=false, description="")
     * @ApiReturnHeaders(sample="HTTP 200 OK")
     * @ApiReturn(type="object", sample="{'json': '', 'encrypted': '' }")
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
     * @ApiBody(sample="{ 'data': ''}")
     * @ApiParams(name="data", type="object", nullable=false, description="")
     * @ApiReturnHeaders(sample="HTTP 200 OK")
     * @ApiReturn(type="object", sample="{'data': '', 'decrypted': '' }")
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
     * @ApiDescription(section="DevGenerateAuthToken", description="Generate a jwt token, that can be used for any user as his, used for development purposes for now. Put into any sandbox with data and call succefully the route.")
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
	 *
	 * Block comment
	 *
	 */
	public function tester($request, $response, $service, $app){
		// $all_headers = $request->headers()->get('Tester');
		// return $response->json($all_headers);

		$con = Connect::con();

		$order_time = date("Y-m-d H:i");
		$order_insert_query = "INSERT INTO `translation_orders` (`order_type`, `user_id`, `invoice_id`, `name`,  `business_name`, `email`, `phone`, `file_ids`,`total_units`, `total_price`,`status`, `order_time`,`project_reference`)VALUES('" . $order_type . "','" . $user . "','" . $invoice_id . "','" . $name . "','" . $bsn_name . "','" . $email . "','" . $phone . "','" . $file_ids . "','" . $total_units . "','" . $total_price . "','0','$order_time','$project_reference_number')";
		$order_inserted = mysqli_query($con, $order_insert_query);
		if ($order_inserted) {
	        $order_id = mysqli_insert_id($con); // PRAVI ORDER ID $$$
	        return $order_id;
	    }
		// $order_inserted = mysqli_query($con, "INSERT INTO `translation_orders` (`user_id`)". "VALUES('" . 111111 . "')");
		// $order_id = mysqli_insert_id($con); // Vrati od order_id
  //       return $response->json($order_id);

		// $data = $request->data;
		// $dec = json_decode($data);
		// $ar = (array) $dec;
		// if($ar['services']['telephonic_interpreting']){
		// 	return $ar['services']['telephonic_interpreting'];
		// 	$ar['services']['telephonic_interpreting'] = 'Telephonic Interpreting';
		// }
		// if($ar['services'][1]){
		// 	$ar['services'][1] = 'Translation Services';
		// }
		// if($ar['services'][2]){
		// 	$ar['services'][2] = 'On-Site Interpreting';
		// }
		// if($ar['services'][3]){
		// 	$ar['services'][3] = 'Transcription Services';
		// }
		// return $response->json($ar['services']);
		// $services = implode(":", $ar['services']);
		// return $services;

	}
}