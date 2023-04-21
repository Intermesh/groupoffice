<?php

namespace go\core\customfield;

class YesNo extends Base {

	/**
	 * Get column definition for SQL
	 * 
	 * @return string
	 */
	protected function getFieldSQL(): string
	{
		$def = $this->field->getDefault();
		if(!empty($def)) {
			$def = intval($def);
		} else{
			$def = "NULL";
		}

		return "tinyint DEFAULT " . $def;
	}

	public function dbToText($value, \go\core\orm\CustomFieldsModel $values, $entity)
	{
		switch($value) {
			case -1:
				return go()->t("No");
			case 1:
				return go()->t("Yes");

			default:
				return "";
		}
	}
}
