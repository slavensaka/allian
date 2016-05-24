<?php

namespace Allian\Http\Controllers;

use Firebase\JWT\JWT;
use \Dotenv\Dotenv;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\DomainException;
use Firebase\JWT\BeforeValidException;
use RNCryptor\Encryptor;
use RNCryptor\Decryptor;
use Twillio;

class TwilioController extends Controller {

	public function twilio(){
		return "Lorem";
	}
}