<?php
namespace go\core\jmap;

use go\core\http\Exception;
use go\core\http;

/**
 * JMAP Request object
 * 
 * Does some checks if the request is valid
 */
class Request extends http\Request {
	public function __construct() {
//		if($this->getMethod() != 'POST') {
//			throw new Exception(405, 'Only POST requests are supported');
//		}
		
//		Disabled for backwards compatibiltiy with old framework
//		
//		if(!$this->isJson()) {
//			throw new Exception(400, "Content-Type must be 'application/json'.");
//		}
		
//		if(!is_array($this->getBody())) {
//			throw new Exception(400, "JSON body must be an array.");
//		}
	}
}
