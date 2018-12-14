<?php

namespace go\modules\core\customfields\type;

class DateTime extends Base {

	/**
	 * Get column definition for SQL
	 * 
	 * @return string
	 */
	protected function getFieldSQL() {
		$d = $this->field->getDefault();
		$d = isset($d) && $d != "" ? GO()->getDbConnection()->getPDO()->quote((new \go\core\util\DateTime($d))->format('Y-m-d H:i')) : "NULL";
		return "DATETIME DEFAULT " . $d;
	}
}
