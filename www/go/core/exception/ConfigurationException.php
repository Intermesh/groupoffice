<?php
namespace go\core\exception;

class ConfigurationException extends \Exception {
	
	public function __construct($message = "Invalid config.php contents. No \$config array defined.", $code = 0, $previous = null) {
		parent::__construct(go()->t($message), $code, $previous);
	}
}

