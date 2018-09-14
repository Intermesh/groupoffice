<?php

namespace go\modules\core\customfields\datatype;

class Number extends Base {

	/**
	 * Get column definition for SQL
	 * 
	 * @return string
	 */
	public function getFieldSQL() {
		$d = $this->field->getDefault();
		$d = isset($d) && $d != "" ? number_format($d, 4) : "NULL";
		return "decimal(19,4) DEFAULT " . $d;
	}
}
