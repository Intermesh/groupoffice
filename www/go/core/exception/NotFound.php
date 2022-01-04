<?php
namespace go\core\exception;

class NotFound extends \Exception {
	
	public function __construct($message = "The item was not found", $code = 0, $previous = null) {
		parent::__construct(go()->t($message), $code, $previous);
	}
}
