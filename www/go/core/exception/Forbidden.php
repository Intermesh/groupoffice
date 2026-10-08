<?php
namespace go\core\exception;

class Forbidden extends \Exception implements UserSafeException {
	
	public function __construct($message = "You don't have permissions to do this", $code = 0, $previous = null) {
		parent::__construct(go()->t($message), $code, $previous);
	}
}
