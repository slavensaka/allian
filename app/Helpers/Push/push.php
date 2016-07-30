<?php

// Put your device token here (without spaces):
$deviceToken = 'e7eb3a21d06a275337a45d367cd367b519fca6483c2168a5a8b201a5db91345e';

// Put your private key's passphrase here:
$passphrase = 'at123';

// Put your alert message here:
// $message = 'Push notifikacija za Allian Translate, slat će se preko server cronjob-a, 10 minuta prije nego je dano od scheduled session od korisnika ap-a nepočne, kao upozorenje korisniku da se pripremi.';
$message = "Your scheduled conference call is about to start in 5 minutes. Translation: Portuguese <> Italian. On date: 27.07.2016. Cost: 213.25$.";
$body = array('aps' => array('alert' => $message, 'sound' => 'default', 'badge' => 1 ), 'orderID' => "5153");
////////////////////////////////////////////////////////////////////////////////

$ctx = stream_context_create();
stream_context_set_option($ctx, 'ssl', 'local_cert', 'allianpushcertfikat.pem');
stream_context_set_option($ctx, 'ssl', 'passphrase', $passphrase);

// Open a connection to the APNS server
// $fp = stream_socket_client(
// 	'ssl://gateway.push.apple.com:2195', $err,
// 	$errstr, 60, STREAM_CLIENT_CONNECT|STREAM_CLIENT_PERSISTENT, $ctx);
$fp = stream_socket_client(
	'ssl://gateway.sandbox.push.apple.com:2195', $err,
	$errstr, 60, STREAM_CLIENT_CONNECT|STREAM_CLIENT_PERSISTENT, $ctx);

if (!$fp)
	exit("Failed to connect: $err $errstr" . PHP_EOL);

echo 'Connected to APNS' . PHP_EOL;

// Create the payload body
$body['aps'] = array(
	'alert' => $message,
	'sound' => 'default',
     'badge' => 1
	);

// Encode the payload as JSON
$payload = json_encode($body);

// Build the binary notification
$msg = chr(0) . pack('n', 32) . pack('H*', $deviceToken) . pack('n', strlen($payload)) . $payload;

// Send it to the server
$result = fwrite($fp, $msg, strlen($msg));

if (!$result)
	echo 'Message not delivered' . PHP_EOL;
else
	echo 'Message successfully delivered' . PHP_EOL;

// Close the connection to the server
fclose($fp);
