<?php

namespace go\modules\core\customfields\datatype;

class FunctionField extends Base {
	
	//no db field for functions
	public function onFieldSave() {
		return true;
	}
	
	public function onFieldDelete() {
		return true;
	}
	
	/**
	 * Get column definition for SQL
	 * 
	 * @return string
	 */
	protected function getFieldSQL() {
		$d = $this->field->getDefault();
		$d = isset($d) && $d != "" ? number_format($d, 4) : "NULL";
		
		$decimals = $this->field->getOption('numberDecimals') + 2;
		
		return "decimal(19,$decimals) DEFAULT " . $d;
	}

	public function dbToApi($dummy, $values) {
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
