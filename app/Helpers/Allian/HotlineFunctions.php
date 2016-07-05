<?php

namespace Allian\Helpers\Allian;

use Database\Connect;
use Allian\Helpers\Mail;
use Allian\Models\LangList;
use Allian\Models\CustLogin;
use Allian\Models\TranslationOrders;
use Allian\Models\ConferenceSchedule;
use Allian\Models\OrderOnsiteInterpreter;
use Allian\Http\Controllers\TwilioController;

class HotlineFunction {

	/**
	 *
	 * Block comment
	 *
	 */
	function addtofile($file,$data){
		return file_put_contents("userdata/".$file.".txt", json_encode($data));
	}

	/**
	 *
	 * Block comment
	 *
	 */
	function getfromfile($file){
		$abc=file_get_contents("userdata/".$file.".txt");
		$abc=json_decode($abc,TRUE);
		return $abc;
	}

}