<?php
namespace go\core\exception;

class Unavailable extends \Exception implements UserSafeException {
	
	public function __construct($message = "Service unavailable", $code = 0, $previous = null) {
		parent::__construct(go()->t($message), $code, $previous);
	}
}
