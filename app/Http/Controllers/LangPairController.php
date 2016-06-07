<?php

namespace Allian\Http\Controllers;

use Allian\Models\LangList;
use Allian\Models\LangPair;
use Allian\Models\LangPairTrans;
use Firebase\JWT\JWT;
use \Dotenv\Dotenv;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\DomainException;
use Firebase\JWT\BeforeValidException;
use RNCryptor\Encryptor;
use RNCryptor\Decryptor;

class LangPairController extends Controller {

	/**
     * @ApiDescription(section="LangPairTrans", description="Retrieve the language pair translations possible depending on linguists that can handle the language pair and are approved.")
     * @ApiMethod(type="get")
     * @ApiRoute(name="/testgauss/langPairTrans")
     * @ApiBody(sample="{'data': 'AwGIQopJaEotZ+/gaCg4Vgyr+AFOenMxVKfVNhVOprjY1vTeqNY3GnhjC1ANCZ+yDnpOUHVt9Xieo/Z4jSqhEYSjvYH0baLYVJuQ3j4TTTUFTKa+PTfbCr4Sk7d2TKK4v04=', 'token': 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzUxMiJ9.eyJpYXQiOjE0NjQ1OTMzNjcsImp0aSI6IlJoOGpiMVhUZHFvUDVDVUVSQ29VY3pWR0dnSVFsQWJ1bFwvRFp1U2pcL050OD0iLCJpc3MiOiJsb2NhbGhvc3QiLCJuYmYiOjE0NjQ1OTMzNjcsImV4cCI6MTQ2NTgwMjk2NywiZGF0YSI6eyJTdWNjZXNzIjoiU3VjY2VzcyJ9fQ.JDwNdycstmqNC0dyrNgNuik_zXCYbx3PwbIkdTX7is3oDrQr6CKQ6mREUt-9tbOys361mcH1kyXaahn9Y2tTRg'}")
     @ApiParams(name="token", type="object", nullable=false, description="Autentication token for users autentication.")
     @ApiParams(name="data", type="object", nullable=false, description="Customer ID.")
     * @ApiReturnHeaders(sample="HTTP 200 OK")
     * @ApiReturn(type="object", sample="{ 'data': 'json of lang-translationTo' }")
     */
	public function langPairTrans($request, $response, $service, $app) {
		if($request->headers()->get('token')){
			// Validate token if not expired, or tampered with
			$this->validateToken($request->headers()->get('token'));
			// Decrypt data
			$data = $this->decryptValues($request->headers()->get('data'));
			// Validate customerId
			$service->validate($data['CustomerID'], 'Error: No customer id is present.')->notNull()->isInt();
			// Validate token in database for customer stored
			$validated = $this->validateTokenInDatabase($request->headers()->get('token'), $data['CustomerID']);
			// If error validating token in database
			if(!$validated){
				$base64Encrypted = $this->encryptValues(json_encode($this->errorJson("Authentication problems present")));
	     		return $response->json(array('data' => $base64Encrypted));
			}
			// Retrieve all languages ASC order
			list($listLanguages) = LangPairTrans::getLanguages();
			// For every listed langauge retrieve there translationTo possible langauges.
			$listing = array();
			foreach($listLanguages as $p){
				// Return a list of translationTo languages by the SELECT SQL value
				list($translationTo) = LangPairTrans::retrieveLangPairTrans($p->getValueEncoded("LangId"));
				// For every translationTo object, store the LangName gotten from SELECT SQL
				// into a new array for creating a valid json response array.
				$novi = array();
				foreach($translationTo as $l){
					// Retrieve the LangName's of all gotten languages translationTo, that lang supports
					$novi[] = $l->getValueEncoded("LangName");
				}
				//Create a valid, and dev requested type of json response
				$listing[] = array("lang" => $p->getValueEncoded("LangName"), "translationTo" =>$novi );
			}
			return $response->json(array("data" => $listing));
			// $base64Encrypted = $this->encryptValues(json_encode($listing));
	  //    	return $response->json(array('data' => $base64Encrypted));
		} else {
			return $response->json("No token provided. TODO. Encrypt this");
		}
	}
}