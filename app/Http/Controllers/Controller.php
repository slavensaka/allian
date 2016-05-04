<?php

namespace Allian\Http\Controllers;

class Controller {
	public function renderDocs($request, $response, $service, $app){
		$service->render('./docs/index.html');
	}

	public function validateEmail($email){

	}

}