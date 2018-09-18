<?php

namespace go\modules\core\customfields\datatype;

class Number extends Base {

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
}
