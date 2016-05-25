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

class LangListController extends Controller {

	/**
     * @ApiDescription(section="LangNames", description="Retrieve json of list of langauges to translate.")
     * @ApiMethod(type="get")
     * @ApiRoute(name="/testgauss/langNames")
     * @ApiBody(sample="{ 'token': 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzUxMiJ9.eyJpYXQiOjE0NjQxODA5NjgsImp0aSI6Imt2bTFQUGNZV0pXanJFNldKVnJlWkZDXC9DT0NIXC9ScXZzRXczS1daRHVFaz0iLCJpc3MiOiJsb2NhbGhvc3QiLCJuYmYiOjE0NjQxODA5NjgsImV4cCI6MTQ2NTM5MDU2OH0.zUOaBjGImiccVvwPtQlxYhpi2kOD5a1hLEU7ySvDt3gUwRY-t2pC5UwHkRXrvsjmpx866p2ZQOzY5M2GCRoHkw'}")
     * @ApiParams(name="token", type="object", nullable=false, description="Token.")
     * @ApiReturnHeaders(sample="HTTP 200 OK")
     * @ApiReturn(type="object", sample="{'data': ''
		}")
     */
	public function langNames($request, $response, $service, $app) {
		if($request->token){

			// Validate token if not expired, or tampered with
			$this->validateToken($request->token);

			// Retrieve langugage names from database
			list($langNames) = LangList::langNames();

			// Format only
			$new = array();
			foreach($langNames as $l){
				$new[] = array( 'langId' => $l->getValueEncoded('LangId'),
								'phoneKey' => $l->getValueEncoded('PhoneKey'),
								'langName' => trim($l->getValueEncoded('LangName'), "\r\n"),
								'tierType' => $l->getValueEncoded('TierType'),
								'tierType_interpret' => $l->getValueEncoded('TierType_Interpret')
								);
			}

			return $response->json(array('data' => $new));
		} else {
			return $response->json("No token provided. TODO. Encrypt this");
		}
	}
}