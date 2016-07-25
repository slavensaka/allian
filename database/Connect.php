<?php

namespace Database;

use \Dotenv\Dotenv;

class Connect {

	/**
	 *
	 * Use for mysqli_connection to database
	 *
	 */
	public static function con(){
		$host = getenv('DB_HOST');
		$db_username = getenv('DB_USERNAME');
		$db_password = getenv('DB_PASSWORD');
		$db_name = getenv('DB_NAME');
		return mysqli_connect("$host", "$db_username", "$db_password", "$db_name");
	}
}