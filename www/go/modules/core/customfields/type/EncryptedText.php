<?php
namespace go\modules\core\customfields\type;

use go\core\util\Crypt;
use go\modules\core\customfields\type\Base;

class EncryptedText extends Base {
	/**
	 * Get column definition for SQL
	 * 
	 * @return string
	 */
	protected function getFieldSQL() {
		return "TEXT NULL";
	}
	
	public function dbToApi($value, &$values) {		
		return Crypt::decrypt($value);
	}
	
	public function apiToDb($value, &$values) {
		return Crypt::encrypt($value);
	}
	
}

