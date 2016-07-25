<?php

namespace Database;

class DatabaseConnection {

	function __construct(){
		$host = getenv('DB_HOST');
		$db_username = getenv('DB_USERNAME');
		$db_password = getenv('DB_PASSWORD');
		$db_name = getenv('DB_NAME');
		$this->mysqli = new \mysqli("$host", "$db_username", "$db_password", "$db_name");
		if (mysqli_connect_errno()) {
			throw new \Exception("Connect failed: %s\n", mysqli_connect_error());
			exit();
		}
	}

}