<?php

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

$plain_txt = "This is my plain text";
echo "Plain Text = $plain_txt\n";
echo "<br>";
$encrypted_txt = encrypt('encrypt', $plain_txt);
echo "Encrypted Text = $encrypted_txt";
echo "<br>";
$decrypted_txt = decrypt('decrypt', $encrypted_txt);
echo "Decrypted Text = $decrypted_txt\n";
echo "<br>";
if( $plain_txt === $decrypted_txt ) echo "SUCCESS";
else echo "FAILED";
echo "<br>";