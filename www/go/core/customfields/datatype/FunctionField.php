<?php

namespace go\core\customfields\datatype;

class FunctionField extends Base {

	public function apiToDb($value, $values) {
		$f = $this->field->getOption("function");
		
		foreach ($values as $key => $value) {
			if(is_numeric($value)) {
				$f = str_replace('{' . $key . '}', $value, $f);
			}
		}
		$f = preg_replace('/\{[^}]*\}/', '0', $f);
		
		GO()->debug("Function field formula: \$result = " .  $f. ";");
		
		if(empty($f)) {
			return null;
		}

		$result = null;

		
		
		
		eval("\$result = " . $f . ";");
		
		
		return $result;
	}
}
