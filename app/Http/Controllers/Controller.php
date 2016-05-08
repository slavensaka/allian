<?php

namespace Allian\Http\Controllers;

class Controller {

	public function renderDocs($request, $response, $service, $app){
		$service->render('./docs/index.html');
	}


	public function getTerms($request, $response, $service, $app){
		$response->json("OKOK");
		$service->render('./resources/views/terms.html');

	}

	//TODO general function
	public function validateEmail($email){

	}



}