<?php

namespace Allian\Helpers\Push;

use \Dotenv\Dotenv;
use Database\Connect;
use Allian\Helpers\Mail;
use Allian\Models\LangList;
use Allian\Models\CustLogin;
use Allian\Models\TranslationOrders;
use Allian\Models\ConferenceSchedule;
use Allian\Http\Controllers\Controller;
use Allian\Models\OrderOnsiteInterpreter;
use Allian\Http\Controllers\ConferenceController;

class PushNotification extends Controller {

	public static function push(){
		$production = false;
		if($production){
			$gateway = 'gateway.push.apple.com:2195';
		} else {
			$gateway = 'gateway.sandbox.push.apple.com:2195';
		}
		// Put your device token here (without spaces):
		$deviceToken = '8d0ed95e5eb05864d270f11c196e7871d0d685162597ffa721aba50745e';
		// 1095b49dcdbc6632049888d598ee6a301c2175ca238569fb4b6cb2255310a527 Marko
		// Put your private key's passphrase here:
		$passphrase = 'at123';
		// Put your alert message here:
		$message = 'Push notifikacija za Allian Translate, slat će se preko server cronjob-a, 15 minuta prije nego jedano od scheduled session od korisnika ap-a nepočne, kao upozorenje korisniku da se pripremi.';
		////////////////////////////////////////////////////////////////////////////////

		$ctx = stream_context_create();
		stream_context_set_option($ctx, 'ssl', 'local_cert', 'allianpushcertfikat.pem');
		stream_context_set_option($ctx, 'ssl', 'passphrase', $passphrase);

		// Open a connection to the APNS server
		// $fp = stream_socket_client(
		// 	'ssl://gateway.push.apple.com:2195', $err,
		// 	$errstr, 60, STREAM_CLIENT_CONNECT|STREAM_CLIENT_PERSISTENT, $ctx);

		$fp = stream_socket_client(
			$gateway, $err,
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
	}

}