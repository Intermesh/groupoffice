<?php
namespace go\core\jmap\exception;

use Exception;

class UnsupportedFilter extends Exception {
	public function __construct(string $entity, string $name) {
		parent::__construct("The filter '$name' is not supported for entity '$entity' by the server.");
	}
}