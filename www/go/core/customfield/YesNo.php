<?php

namespace go\core\customfield;

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
