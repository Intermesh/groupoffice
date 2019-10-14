<?php
namespace go\core\jmap\exception;

class CannotCalculateChanges extends \Exception {
	public function __construct($message = "cannotCalculateChanges") {
		parent::__construct($message);
	}
}
