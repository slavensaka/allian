<?php
require __DIR__ . '/vendor/autoload.php';
// Metodde za testiranje
use Firebase\JWT\JWT;
use \Dotenv\Dotenv;

 /**
  *
  * Generate token for login route
  *
  */
function generateTokenForLogin(){
	$tokenId = base64_encode(mcrypt_create_iv(32));
    $issuedAt   = time();
    $notBefore  = $issuedAt;
    $expire     = $notBefore + 1209600; // 2 weeks
    $serverName = "http://localhost/";
    $email = "slavensakacic@gmail.com";
    $password ="12345";

	$data = array(
        'iat'  => $issuedAt,         // Issued at: time when the token was generated
        'jti'  => $tokenId,          // Json Token Id: an unique identifier for the token
        'iss'  => $serverName,       // Issuer
        'nbf'  => $notBefore,        // Not before
        'exp'  => $expire,           // Expire
        'data' => array(                  // Data related to the signer user
            'email'   => $email,
            'password' => $password,
        )
    );

	$jwt = JWT::encode(
	        $data,      //Data to be encoded in the JWT
	        "axqF3RBxut", // The signing key
	        'HS512'     // Algorithm used to sign the token, see https://tools.ietf.org/html/draft-ietf-jose-json-web-algorithms-40#section-3
	        );
	return $jwt;
}

/**
 *
 * Generate token for register route
 *
 */
function generateTokenForRegister(){
	$tokenId = base64_encode(mcrypt_create_iv(32));
    $issuedAt   = time();
    $notBefore  = $issuedAt;
    $expire     = $notBefore + 1209600;
    $serverName = "http://localhost/";
    $fname = "Slaven";
    $lname = "Sakacic";
    $email = "slavensakacic@gmail.com";
    $phone = "773-732-6534";
    $password ="12345";
    $phone_password = "45435";
    $services = array("telephonic_interpreting", "translation_services", "onsite_interpreting", "transcription_services");
    $stripe_token = "cus_6nNFDRVGjd1wUe";

	$data = array(
        'iat'  => $issuedAt,         // Issued at: time when the token was generated
        'jti'  => $tokenId,          // Json Token Id: an unique identifier for the token
        'iss'  => $serverName,       // Issuer
        'nbf'  => $notBefore,        // Not before
        'exp'  => $expire,           // Expire
        'data' => array(                  // Data related to the signer user
            'fname'   => $fname, // userid from the users table
            'lname'   => $lname,
            'email'   => $email,
            'phone'   => $phone,
            'password'   => $password,
            'phone_password'   => $phone_password,
            'services'   => $services,
            'stripe_token'   => $stripe_token
        )
    );

	return $jwt = JWT::encode(
	        $data,      //Data to be encoded in the JWT
	        "axqF3RBxut", // The signing key
	        'HS512'     // Algorithm used to sign the token, see https://tools.ietf.org/html/draft-ietf-jose-json-web-algorithms-40#section-3
	        );

}

/**
 *
 * Generate token for forget route
 *
 */
function generateTokenForForgot(){
	$tokenId = base64_encode(mcrypt_create_iv(32));
    $issuedAt   = time();
    $notBefore  = $issuedAt;
    $expire     = $notBefore + 1209600;
    $serverName = "http://localhost/";
    $email = "slavensakacic@gmail.com";
	$data = array(
        'iat'  => $issuedAt,         // Issued at: time when the token was generated
        'jti'  => $tokenId,          // Json Token Id: an unique identifier for the token
        'iss'  => $serverName,       // Issuer
        'nbf'  => $notBefore,        // Not before
        'exp'  => $expire,           // Expire
        'data' => array(                  // Data related to the signer user
            'email'   => $email,
        )
    );

	return $jwt = JWT::encode(
	        $data,      //Data to be encoded in the JWT
	        "axqF3RBxut", // The signing key
	        'HS512'     // Algorithm used to sign the token, see https://tools.ietf.org/html/draft-ietf-jose-json-web-algorithms-40#section-3
	        );
}

/**
 *
 * Generate token for forget route
 *
 */
function generateTokenForTelAccess(){
	$tokenId = base64_encode(mcrypt_create_iv(32));
    $issuedAt   = time();
    $notBefore  = $issuedAt;
    $expire     = $notBefore + 1209600;
    $serverName = "http://localhost/";
    $email = "slavensakacic@gmail.com";
	$data = array(
        'iat'  => $issuedAt,         // Issued at: time when the token was generated
        'jti'  => $tokenId,          // Json Token Id: an unique identifier for the token
        'iss'  => $serverName,       // Issuer
        'nbf'  => $notBefore,        // Not before
        'exp'  => $expire,           // Expire
        'data' => array(                  // Data related to the signer user
            'customerID'   => 752,
        )
    );

	return $jwt = JWT::encode(
	        $data,      //Data to be encoded in the JWT
	        "axqF3RBxut", // The signing key
	        'HS512'     // Algorithm used to sign the token, see https://tools.ietf.org/html/draft-ietf-jose-json-web-algorithms-40#section-3
	        );
}

function generateTokenForUpdateProfile(){
	$tokenId = base64_encode(mcrypt_create_iv(32));
    $issuedAt   = time();
    $notBefore  = $issuedAt;
    $expire     = $notBefore - 1209600;
    $serverName = "http://localhost/";
    $email = "slavensakacic@gmail.com";
    $password ="12345";

	$data = array(
        'iat'  => $issuedAt ,         // Issued at: time when the token was generated
        'jti'  => $tokenId,          // Json Token Id: an unique identifier for the token
        'iss'  => $serverName,       // Issuer
        'nbf'  => $notBefore,        // Not before
        'exp'  => $expire,           // Expire
        'data' => array(                  // Data related to the signer user
            'email'   => $email, // userid from the users table
            'password' => $password, // User name
        )
    );

	$jwt = JWT::encode(
	        $data,      //Data to be encoded in the JWT
	        "axqF3RBxut", // The signing key
	        'HS512'     // Algorithm used to sign the token, see https://tools.ietf.org/html/draft-ietf-jose-json-web-algorithms-40#section-3
	        );
	return $jwt;
}

function generateTokenForProfile(){
	$tokenId = base64_encode(mcrypt_create_iv(32));
    $issuedAt   = time();
    $notBefore  = $issuedAt;
    $expire     = $notBefore - 1209600;
    $serverName = "http://localhost/";
    $email = "slavensakacic@gmail.com";
    $password ="12345";

	$data = array(
        'iat'  => $issuedAt ,         // Issued at: time when the token was generated
        'jti'  => $tokenId,          // Json Token Id: an unique identifier for the token
        'iss'  => $serverName,       // Issuer
        'nbf'  => $notBefore,        // Not before
        'exp'  => $expire,           // Expire
        'data' => array(                  // Data related to the signer user
            'email'   => $email, // userid from the users table
            'password' => $password, // User name
        )
    );

	$jwt = JWT::encode(
	        $data,      //Data to be encoded in the JWT
	        "axqF3RBxut", // The signing key
	        'HS512'     // Algorithm used to sign the token, see https://tools.ietf.org/html/draft-ietf-jose-json-web-algorithms-40#section-3
	        );
	return $jwt;
}

/**
 *
 * Method to insert array into database
 *
 */
function ArrayOfServicesDatabase(){
	$fields=array_keys($services); // here you have to trust your field names!
	$values=array_values($services);
	$fieldlist=implode(',',$fields);
	$qs=str_repeat("?,",count($fields)-1);
	$sql="insert into user($fieldlist) values(${qs}?)";
	$q=$DBH->prepare($sql);
	$q->execute($values);
}

function encrypt($action, $string) {
    $output = false;
    $encrypt_method = "AES-256-CBC";
    $secret_key = 'This is my secret key';
    $key = hash('sha256', $secret_key);
    $output = openssl_encrypt($string, $encrypt_method, $key, 0);
    $output = base64_encode($output);
    return $output;
}

function decrypt($action, $string){
	$output = false;
	$encrypt_method = "AES-256-CBC";
	 $secret_key = 'This is my secret key';
	$key = hash('sha256', $secret_key);
	$output = openssl_decrypt(base64_decode($string), $encrypt_method, $key, 0);
	return $output;
}

function newencrypt($data, $key){
    $iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC);
    $iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);
    $salt = '12345678';
    $_key = $this->pbkdf2('SHA1', $key, $salt, 10000, 32, true);
    $ciphertext = mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $_key, $data, MCRYPT_MODE_CBC, $iv);
    $hmac = $this->pbkdf2('SHA1', $key, $salt, 10000, 32, true);
    $data = mb_convert_encoding(chr(1).chr(0).$salt.$salt.$iv.$ciphertext.$hmac, "BASE64", "UTF-8");
    return $data;
}

function encryptForLogin(){
	$password = "McdUgy2z9UR4vppZUg";
	$cryptor = new \RNCryptor\Encryptor();
	$data = '{
				"email": "slavensakacic@gmail.com",
				"password": "12345"
			}';
	return $base64Encrypted = $cryptor->encrypt($data, $password);
}

function encryptForRegister(){
	$password = "McdUgy2z9UR4vppZUg";
	$cryptor = new \RNCryptor\Encryptor();
	$data = '{
				"fname": "Slaven",
				"lname": "Sakacic",
				"email": "slavensakacic@gmail.com",
				"phone": "773-732-6534",
				"password": "12345",
				"phone_password": "45435",
				"services": ["Telephonic Interpreting", "Translation Services", "On-Site Interpreting", "Transcription Services"],
				"sname": "Pero Perić",
				"number": 4242424242424242,
				"exp_month": 5,
				"exp_year": 2017,
				"cvc":"314"
			}';
	return $base64Encrypted = $cryptor->encrypt($data, $password);
}

function encryptForForgot(){
	$password = "McdUgy2z9UR4vppZUg";
	$cryptor = new \RNCryptor\Encryptor();
	$data = '{ "email": "slavensakacic@gmail.com" }';
	return $base64Encrypted = $cryptor->encrypt($data, $password);
}

function encryptForUpdate(){
	$password = "McdUgy2z9UR4vppZUg";
	$cryptor = new \RNCryptor\Encryptor();
	$CustomerID = 721;
	$data = '{
				"CustomerID": 720,
				"fname": "Novi",
				"lname": "Novinko",
				"email": "slaven@example.com",
				"phone": "111-111-1111",
				"password": "54321",
				"phone_password": "11111",
				"services": ["Telephonic Interpreting", "Translation Services", "On-Site Interpreting", "Transcription Services"]
			}';
	return $base64Encrypted = $cryptor->encrypt($data, $password);
}

function encryptForLangPairTrans(){
	$password = "McdUgy2z9UR4vppZUg";
	$cryptor = new \RNCryptor\Encryptor();
	$CustomerID = 721;
	$data = '{
				"lang": 721,
				"fname": "Novi",
				"lname": "Novinko",
				"email": "slaven@example.com",
				"phone": "111-111-1111",
				"password": "54321",
				"phone_password": "11111",
				"services": ["Telephonic Interpreting", "Translation Services", "On-Site Interpreting", "Transcription Services"]
			}';
	return $base64Encrypted = $cryptor->encrypt($data, $password);
}

function encryptForViewProfile(){
	$password = "McdUgy2z9UR4vppZUg";
	$cryptor = new \RNCryptor\Encryptor();
	$data = '{
				"CustomerID": 718
			}';
	return $base64Encrypted = $cryptor->encrypt($data, $password);
}

function decryptRN(){
	$base64Encrypted = "AwFRLF20iFlT/sHjP09GcnnSRj5U3UXt+DMpqabHmrnFo0UTs8FTHV0Llz+cFloNLYr1Pj5XWDFzac6oFFDu9WJld3kK2FGSf3oRiMbeN1Y6ScgwnQ5/848yysp+XwWImV9tiXYeZQQISp+nrFbujSL3ttarEGHGdVwB+BJ6CNEIOK7IsAb3uBzprUfvxGKgCUI=";

	$password = "McdUgy2z9UR4vppZUg";
	$cryptor = new \RNCryptor\Decryptor();
	$plaintext = $cryptor->decrypt($base64Encrypted, $password);
	echo $plaintext;
}

echo decryptRN();