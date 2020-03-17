<?php
namespace go\core\jmap\exception;

class UnsupportedSort extends \Exception {
	public function __construct($message = "unsupportedSort") {
		parent::__construct($message);
	}
}