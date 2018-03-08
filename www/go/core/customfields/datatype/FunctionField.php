<?php

namespace go\core\customfields\datatype;

class FunctionField extends Base {

	public function apiToDb($value, $values) {
		$f = $this->field->function;
		foreach ($values as $key => $value) {
			if(is_numeric($value)) {
				$f = str_replace('{' . $key . '}', $value, $f);
			}
		}
		$f = preg_replace('/\{[^}]*\}/', '0', $f);

		$result = null;

		set_error_handler(function() {			
		});
		
		eval("\$result = " . $f . ";");
		restore_error_handler();
		return $result;
	}
}
