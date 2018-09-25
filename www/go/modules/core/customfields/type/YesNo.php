<?php

namespace go\modules\core\customfields\type;

class YesNo extends Base {

	/**
	 * Get column definition for SQL
	 * 
	 * @return string
	 */
	protected function getFieldSQL() {		
		return "tinyint DEFAULT NULL";
	}
}
