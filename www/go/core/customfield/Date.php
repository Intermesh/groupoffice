<?php

namespace go\core\customfield;

class Date extends Base {

	/**
	 * Get column definition for SQL
	 * 
	 * @return string
	 */
	protected function getFieldSQL() {
		$d = $this->field->getDefault();
		$d = isset($d) && $d != "" ? GO()->getDbConnection()->getPDO()->quote($d) : "NULL";
		return "DATE DEFAULT " . $d;
	}
}
