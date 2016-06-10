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
     * @ApiBody(sample="{ 'data': {
    'CustomerID': '800'
  }}")
     * @ApiBody(sample="{ 'token': 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzUxMiJ9.eyJpYXQiOjE0NjQ2MDE1MTUsImp0aSI6InAwaFpucWxqaUpqWStDdmdrb3c0MjJITTQ1TkYweFVobCtHU2lWZFwvUlN3PSIsImlzcyI6ImxvY2FsaG9zdCIsIm5iZiI6MTQ2NDYwMTUxNSwiZXhwIjoxNDY1ODExMTE1LCJkYXRhIjp7IlN1Y2Nlc3MiOiJTdWNjZXNzIn19.wwxlnjSCmInwNYinJ-LIyHMOys3oYTeoQem2MJTfgNREFZ8rcDB9uZ61Hw6vHIVMh_8BKzJUKS-_0nwhfrJVxQ'}")
     * @ApiParams(name="data", type="string", nullable=false, description="Data.")
     * @ApiParams(name="token", type="string", nullable=false, description="Token.")
     * @ApiReturnHeaders(sample="HTTP 200 OK")
     * @ApiReturn(type="string", sample="{'data': {
        'languages': [
            {
                'langId': '10',
                'phoneKey': '237',
                'langName': 'Afrikaans',
                'tierType': '1',
                'tierType_interpret': '3'
            },
            { '...': '...' }
        ]
    }
 }")
     */
	public function langNames($request, $response, $service, $app) {
		if($request->token){
			// Validate token if not expired, or tampered with
			$this->validateToken($request->token);
			// Decrypt data
			$data = $this->decryptValues($request->data);
			// Validate input data
			$service->validate($data['CustomerID'], 'Error: No customer id is present.')->notNull()->isInt();
			//Validate the jwt token in the database
			$validated = $this->validateTokenInDatabase($request->token, $data['CustomerID']);
			// If error validating token in database
			if(!$validated){
	     		return $response->json(array('data' => $this->errorJson("Authentication problems present")));
			}
			// Retrieve langugage names from database
			list($langNames) = LangList::langNames();
			// Format only
			$new = array();
			foreach($langNames as $l){
				$new[] = array(
					'langId' => $l->getValueEncoded('LangId'),
					'phoneKey' => $l->getValueEncoded('PhoneKey'),
					'langName' => trim($l->getValueEncoded('LangName'), "\r\n"),
					'tierType' => $l->getValueEncoded('TierType'),
					'tierType_interpret' => $l->getValueEncoded('TierType_Interpret')
				);
			}
			return $response->json(array('data' => array('languages' =>  $new)));
		} else {
			$base64Encrypted = $this->encryptValues(json_encode($this->errorJson("No token provided in request")));
     		return $response->json(array('data' => $base64Encrypted));
		}
	}
}