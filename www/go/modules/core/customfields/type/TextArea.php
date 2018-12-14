<?php

namespace go\modules\core\customfields\type;

class TextArea extends Base {
	/**
	 * Get column definition for SQL
	 * 
	 * @return string
	 */
	protected function getFieldSQL() {
		return "TEXT NULL";
	}
	
}
