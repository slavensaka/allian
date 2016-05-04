<?php

namespace Allian\Models;

class ConferenceLog extends DataObject {

	protected $data = array(
	    "log_id" => "",
	    "secret_code" => "",
	    "call_sid" => "",
	    "connect_datetime" => "",
	    "hangup_datetime" => "",
	    "is_disconnected" => "",
	);
}