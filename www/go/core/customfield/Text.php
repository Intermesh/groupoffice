<?php

namespace go\core\customfield;

use Exception;

class Text extends Base {

	/**
	 * Get column definition for SQL.
	 *
	 * When false is returned no databaseName is required and no field will be created.
	 *
	 * @return string
	 * @throws Exception
	 */
	protected function getFieldSQL()
	{
		$maxLength = $this->field->getOption('maxLength') ?? 190;

		if($maxLength > 255) {
			return "TEXT NULL";
		}

		$def = $this->field->getDefault();
		if(!empty($def)) {
			$def = go()->getDbConnection()->getPDO()->quote($def);
		} else{
			$def = "''";
		}

		return "VARCHAR(" . $maxLength . ") NOT NULL DEFAULT " . $def;
	}
	
}
