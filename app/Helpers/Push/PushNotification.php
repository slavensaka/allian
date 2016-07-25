<?php

namespace Allian\Helpers\Push;

use \Dotenv\Dotenv;
use Allian\Http\Controllers\Controller;

class PushNotification extends Controller {

	/**
	 *
	 * Used to send push notifications
	 *
	 */
	public static function push($deviceToken = null, $message, $production = false){
		if($production){
			$gateway = 'gateway.push.apple.com:2195';
		} else {
			$gateway = 'gateway.sandbox.push.apple.com:2195';
		}
		if($deviceToken == null){
			exit();
		}
		// The private key's passphrase
		$passphrase = getenv('PUSH_PASS_PHRASE');
		// Put your alert message here:
		$ctx = stream_context_create();
		if($production){
			stream_context_set_option($ctx, 'ssl', 'local_cert', 'allianpushcertifikatprod.pem');
		} else {
			stream_context_set_option($ctx, 'ssl', 'local_cert', 'allianpushcertfikat.pem');
		}
		stream_context_set_option($ctx, 'ssl', 'passphrase', $passphrase);
		// Open a connection to the APNS server
		$fp = stream_socket_client($gateway, $err, $errstr, 60, STREAM_CLIENT_CONNECT|STREAM_CLIENT_PERSISTENT, $ctx);
		if(!$fp){
			exit();
			// exit("Failed to connect: $err $errstr" . PHP_EOL);
		}
		// Create the payload body
		$body['aps'] = array('alert' => $message, 'sound' => 'default', 'badge' => 1);
		// Encode the payload as JSON
		$payload = json_encode($body);
		// Build the binary notification
		$msg = chr(0) . pack('n', 32) . pack('H*', $deviceToken) . pack('n', strlen($payload)) . $payload;
		// Send it to the server
		$result = fwrite($fp, $msg, strlen($msg));
		// Close the connection to the server
		fclose($fp);
		if (!$result){
			return false;
		}else{
			return true;
		}
	}

	/**
	 *
	 * Test the push notification to mobile
	 *
	 */
	public static function testPush($deviceToken, $production = false){
		if($production){
			$gateway = 'gateway.push.apple.com:2195';
		} else {
			$gateway = 'gateway.sandbox.push.apple.com:2195';
		}
		// Put your private key's passphrase here:
		$passphrase = getenv('PUSH_PASS_PHRASE');
		// Put your alert message here:
		$message = 'Push notifikacija za Allian Translate, slat će se preko server cronjob-a, 10 minuta prije nego je dano od scheduled session od korisnika ap-a nepočne, kao upozorenje korisniku da se pripremi.';
		////////////////////////////////////////////////////////////////////////////////
		$ctx = stream_context_create();
		stream_context_set_option($ctx, 'ssl', 'local_cert', 'allianpushcertfikat.pem');
		stream_context_set_option($ctx, 'ssl', 'passphrase', $passphrase);
		$fp = stream_socket_client($gateway, $err,$errstr, 60, STREAM_CLIENT_CONNECT|STREAM_CLIENT_PERSISTENT, $ctx);
		if(!$fp){
			exit("Failed to connect: $err $errstr" . PHP_EOL);
		}
		// Create the payload body
		$body['aps'] = array('alert' => $message, 'sound' => 'default', 'badge' => 1);
		// Encode the payload as JSON
		$payload = json_encode($body);
		// Build the binary notification
		$msg = chr(0) . pack('n', 32) . pack('H*', $deviceToken) . pack('n', strlen($payload)) . $payload;
		// Send it to the server
		$result = fwrite($fp, $msg, strlen($msg));
		// Close the connection to the server
		fclose($fp);
		if (!$result) {
			return false;
		}
		else{
			return true;
		}
	}

}