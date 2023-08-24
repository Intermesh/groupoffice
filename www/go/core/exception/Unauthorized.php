<?php
namespace go\core\exception;

class Unauthorized extends \Exception {
	
	public function __construct($message = "Unauthorized", $code = 0, $previous = null) {
		parent::__construct(go()->t($message), $code, $previous);
	}
}
