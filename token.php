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
    $expire     = $notBefore + 1209600;
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
    $expire     = $notBefore + 1209600;
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

function newencrypt($data, $key)
{
    $iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC);
    $iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);

    $salt = '12345678';

    $_key = $this->pbkdf2('SHA1', $key, $salt, 10000, 32, true);

    $ciphertext = mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $_key, $data, MCRYPT_MODE_CBC, $iv);

    $hmac = $this->pbkdf2('SHA1', $key, $salt, 10000, 32, true);

    $data = mb_convert_encoding(chr(1).chr(0).$salt.$salt.$iv.$ciphertext.$hmac, "BASE64", "UTF-8");

    return $data;
}

/*
 * PHP mcrypt - Complete encryption and decryption of data
 */
// $input = "This is my important data I need to encrypt";

// /* Open the cipher */
// $td = mcrypt_module_open('rijndael-256', '', 'ofb', '');

// /* Create the IV and determine the keysize length, use MCRYPT_RAND
//  * on Windows instead */
// $iv = mcrypt_create_iv(mcrypt_enc_get_iv_size($td), MCRYPT_DEV_RANDOM);
// $ks = mcrypt_enc_get_key_size($td);

// /* Create key */
// $key = substr(md5('very secret key'), 0, $ks);

// /* Intialize encryption */
// mcrypt_generic_init($td, $key, $iv);

//  Encrypt data
// $encrypted = mcrypt_generic($td, $input);

// /* Terminate encryption handler */
// mcrypt_generic_deinit($td);

// /* Initialize encryption module for decryption */
// mcrypt_generic_init($td, $key, $iv);

// /* Decrypt encrypted string */
// $decrypted = mdecrypt_generic($td, $encrypted);

// /* Terminate decryption handle and close module */
// mcrypt_generic_deinit($td);
// mcrypt_module_close($td);

// /* Show string */
// echo "Encrypted string : ".trim($encrypted) . "<br />\n";
// echo "Decrypted string : ".trim($decrypted) . "<br />\n";


// echo encrypt('My Data', 'mykey');
// $plain_txt = "This is my plain text";
// echo "Plain Text = $plain_txt\n";
// echo "<br>";
// $encrypted_txt = encrypt('encrypt', $plain_txt);
// echo "Encrypted Text = $encrypted_txt";
// echo "<br>";
// $decrypted_txt = decrypt('decrypt', $encrypted_txt);
// echo "Decrypted Text = $decrypted_txt\n";
// echo "<br>";
// if( $plain_txt === $decrypted_txt ) echo "SUCCESS";
// else echo "FAILED";
// echo "<br>";

// echo generateTokenForLogin();
// echo generateTokenForRegister();
// echo generateTokenForForgot();
// echo generateTokenForTelAccess();
// echo getcwd();
// $date1 = strtotime('2015-02-02 14:00:00');
// echo $date1;
// echo "<br>";
// $date2 = strtotime('2015-02-04 15:00:00');
// echo $date2;
// $diff=date_diff($date1,$date2);
// echo $diff->format("%R%a days");



// $date1 = "2015-02-02 14:00:00";
// $date2 = "2015-02-04 15:00:00";

// $diff = abs(strtotime($date2) - strtotime($date1));

// $years = floor($diff / (365*60*60*24));
// $months = floor(($diff - $years * 365*60*60*24) / (30*60*60*24));
// $days = floor(($diff - $years * 365*60*60*24 - $months*30*60*60*24)/ (60*60*24));

// printf("%d years, %d months, %d days\n", $years, $months, $days);