<?php

namespace go\core\data;

/**
 * Stores any property in the magicProperties array if it is not declared.
 */
trait MagicPropertiesTrait {

	protected $magicProperties = [];

	public function __set($name, $value) {
		$this->magicProperties[$name] = $value;
	}

	public function __get($name) {
		if (array_key_exists($name, $this->magicProperties)) {
			return $this->magicProperties[$name];
		}

		$trace = debug_backtrace();
		trigger_error(
						'Undefined property via __get(): ' . $name .
						' in ' . $trace[0]['file'] .
						' on line ' . $trace[0]['line'], E_USER_NOTICE);
		return null;
	}

	public function __isset($name) {
		return isset($this->magicProperties[$name]);
	}

	public function __unset($name) {
		unset($this->magicProperties[$name]);
	}

}
