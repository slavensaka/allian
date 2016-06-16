<?php

namespace Allian\Models;

use Database\DataObject;
use Database\Connect;

class Login extends DataObject {

	/**
	 *
	 * Block comment
	 *
	 */
	function get_linguist($ipid) {
		$con = Connect::con();
	    $get_ip_info = mysqli_query($con, "SELECT * FROM Login WHERE IPID =  '$ipid'");
	    $ip = mysqli_fetch_array($get_ip_info);
	    return $ip;
	}
}