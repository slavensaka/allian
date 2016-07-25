<?php

namespace Database;

use Dotenv\Dotenv;

abstract class DataObject {

	protected $data = array();

	public function __construct($data) {
	    foreach($data as $key => $value) {
	      if(array_key_exists($key, $this->data)) $this->data[$key] = $value;
    	}
  	}

	/**
	 *
	 * Get a column value as is
	 *
	 */
	public function getValue($field) {
		if(array_key_exists($field, $this->data)) {
			return $this->data[$field];
		} else {
			throw new \Exception("Field not found");
		}
	}

	/**
	 *
	 * Get a database column value where
	 * Convert special characters to HTML entities
	 *
	 */
	public function getValueEncoded($field) {
		return htmlspecialchars($this->getValue($field));
	}

	/**
	 *
	 * Used for connecting to the database
	 *
	 */
	protected function connect(){
		try {
			$conn = new \PDO(getenv('DB_DSN'), getenv('DB_USERNAME'), getenv('DB_PASSWORD'));
			$conn->setAttribute(\PDO::ATTR_PERSISTENT, true);
			$conn->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
		} catch (\PDOException $e) {
			throw new \Exception("Connection failed: " . $e->getMessage());
		}
		return $conn;
	}

	/**
	 *
	 * Used for disconnecting from the database
	 *
	 */
	protected function disconnect($conn){
		$conn = "";
	}
}
