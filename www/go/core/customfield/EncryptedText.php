<?php
namespace go\core\customfield;

use go\core\util\Crypt;
use go\core\customfield\Base;

class EncryptedText extends Base {
	/**
	 * Get column definition for SQL
	 * 
	 * @return string
	 */
	protected function getFieldSQL() {
		return "TEXT NULL";
	}
	
	public function dbToApi($value, &$values, $entity) {
		return isset($value) ? Crypt::decrypt($value) : null;
	}
	
	public function apiToDb($value, &$values, $entity) {
		return isset($value) ? Crypt::encrypt($value) : null;
	}
	
}

