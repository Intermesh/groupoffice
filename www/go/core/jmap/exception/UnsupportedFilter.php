<?php
namespace go\core\jmap\exception;

class UnsupportedFilter extends \Exception {
	public function __construct($name) {
		parent::__construct("Unsupported filter: '" . $name . "'");
	}
}