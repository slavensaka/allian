<?php

namespace Database;

define("DB_HOST", "localhost");
define("DB_USER", "root");
define("DB_PASS", "");
define("DB_NAME", "allian10_abs_linguist_portal");

class DatabaseConnection {

	function __construct(){
		$this->mysqli = new \mysqli(DB_HOST,DB_USER,DB_PASS,DB_NAME);
		if (mysqli_connect_errno()) {
		printf("Connect failed: %s\n", mysqli_connect_error());
		exit();
		}
	}

}