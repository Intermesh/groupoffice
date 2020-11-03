<?php

namespace go\core\customfield;

use go\core\orm\Entity;
use go\core\model\Field;
use go\core\customfield\Base;
use go\core\validate\ErrorCode;
use go\core\util\StringUtil;

class Html extends Base {
	/**
	 * Get column definition for SQL
	 * 
	 * @return string
	 */
	protected function getFieldSQL() {
		return "TEXT NULL";
	}

	public function validate($value, Field $field, $model)
	{
		if(!empty($value) && StringUtil::detectXSS($value)) {
			$model->setValidationError("customFields." . $field->databaseName, ErrorCode::INVALID_INPUT, "You're not allowed to put scripts in customFields." . $field->databaseName);				
			return false;
		}
		return true;
	}
	
}
