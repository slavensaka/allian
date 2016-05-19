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
		list($langPair) = LangPairTrans::retrieveLangPair();
		$listing = array();
		foreach($langPair as $p){
			list($langauge, $lang) = LangPairTrans::retrieveLangPairTrans($p->getValueEncoded( "LangId" ), $p->getValueEncoded("LangName"));

			$listing[] = array('"lang"' => $p->getValueEncoded("LangName"), '"translationTo"' =>  $lang);
		}
		return $response->json(array('"data"' => $listing));
	}
}