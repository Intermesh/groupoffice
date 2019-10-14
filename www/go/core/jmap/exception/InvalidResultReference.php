<?php
namespace go\core\jmap\exception;

class InvalidResultReference extends \Exception {
	public function __construct($message = "InvalidResultReferenceError") {
		parent::__construct($message);
	}
}
