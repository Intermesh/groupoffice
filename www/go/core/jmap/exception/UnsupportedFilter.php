<?php
namespace go\core\jmap\exception;

class UnsupportedFilter extends \Exception {
	public function __construct($message = "unsupportedFilter") {
		parent::__construct($message);
	}
}