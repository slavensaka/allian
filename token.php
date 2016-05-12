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

// echo generateTokenForLogin();
// echo generateTokenForRegister();
echo generateTokenForForgot();
//eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzUxMiJ9.eyJpYXQiOjE0NjI5Njc0NzUsImp0aSI6IkZLamZXeUNsZDdKS1ZHTXdBSElhMnl6a09SQVc0YWhlRnFOanJJSkExc0U9IiwiaXNzIjoiaHR0cDpcL1wvbG9jYWxob3N0XC8iLCJuYmYiOjE0NjI5Njc0NzUsImV4cCI6MTQ2NDE3NzA3NSwiZGF0YSI6eyJmbmFtZSI6IlNsYXZlbiIsImxuYW1lIjoiU2FrYWNpYyIsImVtYWlsIjoic2xhdmVuc2FrYWNpY0BnbWFpbC5jb20iLCJwaG9uZSI6Ijc3My03MzItNjUzNCIsInBhc3N3b3JkIjoiMTIzNDUiLCJwaG9uZV9wYXNzd29yZCI6IjQ1NDM1Iiwic2VydmljZXMiOlsidGVsZXBob25pY19pbnRlcnByZXRpbmciLCJ0cmFuc2xhdGlvbl9zZXJ2aWNlcyIsIm9uc2l0ZV9pbnRlcnByZXRpbmciLCJ0cmFuc2NyaXB0aW9uX3NlcnZpY2VzIl0sInN0cmlwZV90b2tlbiI6ImN1c182bk5GRFJWR2pkMXdVZSJ9fQ.-YnmvmsEEDVb6BiMO21ayjSEF0nTfa4zOiC430fZkeOQp42XSl5SEhMnYQ4dBN50iL4bCScVZlvgOLvmsP8kxw