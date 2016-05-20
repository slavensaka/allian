<?php

namespace Allian\Http\Controllers;

use Allian\Models\LangList;
use Allian\Models\LangPair;
use Allian\Models\LangPairTrans;
use Allian\Models\CustLogin;
use Firebase\JWT\JWT;
use \Dotenv\Dotenv;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\DomainException;
use Firebase\JWT\BeforeValidException;
use RNCryptor\Encryptor;
use RNCryptor\Decryptor;

class LangPairController extends Controller {

	public function langPairTrans($request, $response, $service, $app) {

		// Retrieve all languages ASC order
		list($listLanguages) = LangPairTrans::getLanguages();

		// For every listed langauge retrieve there translationTo possible langauges.
		// Array of 0 to many langauges to translate $p langauge.
		$listing = array();
		foreach($listLanguages as $p){

			// Return a list of translationTo languages by the SELECT SQL value
			list($translationTo) = LangPairTrans::retrieveLangPairTrans($p->getValueEncoded( "LangId" ));

			// For every translationTo object, store the LangName gotten from SELECT SQL
			// into a new array for creating a valid json response array.
			$novi = array();
			foreach($translationTo as $l){
				// Retrieve the LangName's of all gotten languages translationTo, that lang supports
				$novi[] = $l->getValueEncoded("LangName");
			}

			//Create a valid, and dev requested type of json response
			$listing[] = array('"lang"' => $p->getValueEncoded("LangName"), '"translationTo"' =>$novi );
		}
		return $response->json(array('"data"' => $listing));
	}
}