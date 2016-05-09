<?php

namespace Allian\Http\Controllers;

class Controller {
	/**
	 *
	 * Block comment
	 *
	 */
	public function renderDocs($request, $response, $service, $app){
    		if ( !isset($_SERVER['PHP_AUTH_USER'] ) || !isset( $_SERVER['PHP_AUTH_PW'] ) ||
    			$_SERVER['PHP_AUTH_USER'] != getenv('BASIC_AUTH_USER') || $_SERVER['PHP_AUTH_PW'] != getenv('BASIC_AUTH_PASS') ) {
        		header( 'WWW-Authenticate: Basic realm="NO auth!"' );
        		header( 'HTTP/1.0 401 Unauthorized' );
        		exit;
    	} else {
       		$service->render('./docs/index.html');
    	}
	}

	/**
     * @ApiDescription(section="Terms", description="Render view for terms & conditions")
     * @ApiMethod(type="get")
     * @ApiRoute(name="/testgauss/terms")
     * @ApiReturnHeaders(sample="HTTP 200 OK")
     */
	public function getTerms($request, $response, $service, $app){
		$service->render('./resources/views/terms.html');

	}


	/**
	 *
	 * Block comment
	 *
	 */
	public function validateEmail($email){

	}

	/**
	 *
	 * Block comment
	 *
	 */
	public function errorJson($userMessage){
		$jsonArray = array();
		$jsonArray['status'] = 0;
		$jsonArray['userMessage'] = $userMessage;
		return $jsonArray;
	}



}