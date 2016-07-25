<?php

namespace Allian\Helpers\Allian;

use \Dotenv\Dotenv;

class ConnectNowFunctions {

	/**
	 * Used to put CallSid.txt /customertype so the interpreter can
	 * process the order at the end of the call. Interpreter script
	 * collects the User's information and then payment to user and send email
	 *
	 * @param string $sid Customer's CallSid
	 * @param array $data
	 *		CustomerID
	 *		type
	 * @return boolean isSuccess
	 */
	function addCustomerIdType($sid, $data){
		$server = trim($_SERVER['HTTP_HOST']);
		$server=trim($server);
		if($server == "localhost"){
			return file_put_contents("misc/customertype/". $sid . ".txt", json_encode($data));
		} else if($server == "alliantranslate.com"){
			// TODO PROD
			// return file_put_contents("../linguist/phoneapp/customertype/". $sid . ".txt", json_encode($data));
		} else {
			return file_put_contents("misc/customertype/". $sid . ".txt", json_encode($data));
		}
	}

	/**
	 * Customer's script removes CustomerID by CallSid.txt from the
	 * /customertype that's needed for interpreter script.
	 * Unlink the call because the call is done and we don't need it anymore
	 *
	 *	@param string $sid Customer's CallSid
	 *	@return null
	 */
	function removeCustomerIdType($sid){
		$server = trim($_SERVER['HTTP_HOST']);
		$server=trim($server);
		// Check if localhost, unlink form a folder
		if($server == "localhost"){
			$sidfile="misc/customertype/" . $sid . ".txt";
			if(file_exists($sidfile)){
				unlink($sidfile);
			}
		// Unlink the file in the right live folder, if the CallSid.txt exists
		} else if($server == "alliantranslate.com"){
			$sidfile = "../linguist/phoneapp/customertype/" . $sid . ".txt";
			if(file_exists($sidfile)){
				unlink($sidfile);
			}
		// Used for ngrok.exe, when the server host is diffrent name
		} else {
			$sidfile="misc/customertype/" . $sid . ".txt";
			if(file_exists($sidfile)){
				unlink($sidfile);
			}
		}
	}

}