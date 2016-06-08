<?php

namespace Database;

class Connect {

	public static function con(){
		return mysqli_connect("localhost","root","","allian10_abs_linguist_portal"); // TODO
	}

}