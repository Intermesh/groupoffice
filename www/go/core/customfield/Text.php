<?php

namespace go\core\customfield;

class Text extends Base {
	/**
	 * Get column definition for SQL.
	 *
	 * When false is returned no databaseName is required and no field will be created.
	 *
	 * @return string|boolean
	 * @throws Exception
	 */
	protected function getFieldSQL() {
		$def = $this->field->getDefault();
		if(!empty($def)) {
			$def = go()->getDbConnection()->getPDO()->quote($def);
		} else{
			$def = "''";
		}
		return "VARCHAR(".($this->field->getOption('maxLength') ?? 190).") NOT NULL DEFAULT " . $def;
	}
	
}
