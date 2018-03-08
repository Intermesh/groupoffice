<?php

namespace go\core\util;

/**
 * Ftp connection class
 * 
 * Example Usage
 * 
 * $ftp = new Ftp('ftp.example.com');
 * $ftp->login('username','password');
 * var_dump($ftp->nlist());
 * 
 * See php.net/manual/en/book.ftp.php for more information
 * 
 * @author Wesley Smits <wsmits@intermesh.nl>
 * @copyright Copyright Intermesh BV.
 */
class Ftp {

	private $conn;

	public function __construct($host, $port = 21, $timeout = 90) {
		$this->conn = ftp_connect($host, $port, $timeout);
	}

	public function __call($func, $a) {
		$func = 'ftp_' . $func;
		if (function_exists($func)) {
			
			array_unshift($a, $this->conn);
			return call_user_func_array($func, $a);
		} else {
			// replace with your own error handler.
			trigger_error("$func is not a valid FTP function", E_USER_ERROR);
		}
	}

}
