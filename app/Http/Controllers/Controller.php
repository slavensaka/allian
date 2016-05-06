<?php

namespace Allian\Http\Controllers;

class Controller {
	/**
     * @ApiDescription(section="Documentation", description="Get the website API information")
     * @ApiMethod(type="get")
     * @ApiRoute(name="/testgauss/renderdocs")
     */
	public function renderDocs($request, $response, $service, $app){
		$service->render('./docs/index.html');
	}

	/**
     * @ApiDescription(section="Customer", description="Autenticate customer")
     * @ApiMethod(type="post")
     * @ApiRoute(name="/user/get/{id}")
     * @ApiParams(name="id", type="integer", nullable=false, description="User id")
     * @ApiParams(name="data", type="object",sample="{'user_id':'int','user_name':'string','profile':{'email':'string','age':'integer'}}")
     * @ApiHeaders(name="Test", type="test", description="Testing the header")
     * @ApiReturnHeaders(sample="HTTP 200 OK")
     * @ApiReturn(type="object", sample="{
     *  'transaction_id':'int',
     *  'transaction_status':'string'
     * }")
     */
	public function getRenderTerms($request, $response, $service, $app){
		$service->render('./resources/views/terms.html');
	}

	//TODO general function
	public function validateEmail($email){

	}

}