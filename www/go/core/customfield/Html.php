<?php

namespace go\core\customfield;

use go\core\orm\Entity;
use go\core\model\Field;
use go\core\customfield\Base;
use go\core\validate\ErrorCode;
use go\core\orm\CustomFieldsModel;
use go\core\util\StringUtil;

class Html extends Base {

	public static bool $validationEnabled = true;
	/**
	 * Get column definition for SQL
	 * 
	 * @return string
	 */
	protected function getFieldSQL(): string
	{
		return "TEXT NULL";
	}

	/**
	 * Remove scripts, event handlers and other active content before the value is stored. The value is shown as HTML
	 * in the detail view.
	 *
	 * @inheritDoc
	 */
	public function apiToDb($value, CustomFieldsModel $values, $entity)
	{
		if (!is_string($value) || $value === '') {
			return $value;
		}
		return StringUtil::removeActiveContent($value);
	}

	/**
	 * @param $value
	 * @param Field $field
	 * @param $model
	 * @return bool
	 * @throws \Exception
	 */
	public function validate($value, Field $field, $model): bool
	{
		if(self::$validationEnabled && !empty($value) && StringUtil::detectXSS($value)) {
			$model->setValidationError("customFields." . $field->databaseName, ErrorCode::INVALID_INPUT, "You're not allowed to put scripts in customFields." . $field->databaseName);				
			return false;
		}
		return true;
	}
	
}
