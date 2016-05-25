<?php

namespace Database;

use Dotenv\Dotenv;

abstract class DataObject {

	protected $data = array();

	public function __construct( $data ) {
	    foreach ( $data as $key => $value ) {
	      if ( array_key_exists( $key, $this->data ) ) $this->data[$key] = $value;
    	}
  	}

	public function lorem(){
   		$novi = getenv('APP_MODE');
   		echo $novi;
	}

	public function getValue($field) {
		if(array_key_exists($field, $this->data)) {
			return $this->data[$field];
		} else {
			die( "Field not found" );
		}
	}

	public function getValueEncoded($field) {
		return htmlspecialchars($this->getValue($field));
	}

	protected function connect() {
		try {
			$conn = new \PDO( getenv('DB_DSN'), getenv('DB_USERNAME'), getenv('DB_PASSWORD') );
			$conn->setAttribute( \PDO::ATTR_PERSISTENT, true );
			$conn->setAttribute( \PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION );
		} catch (\PDOException $e) {
			die( "Connection failed: " . $e->getMessage());
		}
		return $conn;
	}
	protected function disconnect($conn) {
		$conn = "";
	}
}
