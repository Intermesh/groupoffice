<?php

namespace go\core\customfield;

use go\core\orm\Entity;
use go\core\customfield\Base;
use go\core\orm\CustomFieldsModel;
use go\core\util\StringUtil;

class Html extends Base {

	/**
	 * @deprecated Values are no longer validated. Active content is removed on save, see apiToDb().
	 */
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
	 * Only a value with active content is changed. Parsing and writing the HTML again would also change valid HTML,
	 * like Word markup.
	 *
	 * @inheritDoc
	 */
	public function apiToDb($value, CustomFieldsModel $values, $entity)
	{
		if (!is_string($value) || !StringUtil::containsActiveContent($value)) {
			return $value;
		}
		return StringUtil::removeActiveContent($value);
	}
}
