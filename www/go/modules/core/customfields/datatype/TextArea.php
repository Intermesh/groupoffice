<?php

namespace go\modules\core\customfields\datatype;

class TextArea extends Base {
	/**
	 * Get column definition for SQL
	 * 
	 * @return string
	 */
	public function getFieldSQL() {
		return "TEXT NULL";
	}
	
}
