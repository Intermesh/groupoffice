<?php

namespace go\core\customfield;

class FunctionField extends Number {

	private static $loopIds = [];
	
	//no db field for functions
	public function onFieldSave() {
		return true;
	}
	
	public function onFieldDelete() {
		return true;
	}

	public function hasColumn()
	{
		return false;
	}
	
	/**
	 * Get column definition for SQL
	 * 
	 * @return string
	 */
	protected function getFieldSQL() {
		$d = $this->field->getDefault();
		$d = isset($d) && $d != "" ? number_format($d, 4) : "NULL";
		
		$decimals = $this->field->getOption('numberDecimals') + 2;
		
		return "decimal(19,$decimals) DEFAULT " . $d;
	}

	public function dbToApi($value, \go\core\orm\CustomFieldsModel $values, $entity) {

		$f = $this->field->getOption("function");

		$f = preg_replace_callback('/\{([^}]*)\}/', function($matches) use($entity){
			return $entity->getCustomFields(true)->getValue(trim($matches[1])) ?? 0;
		}, $f);

		if(empty($f)) {
			return null;
		}

		//check for infinity @see CustomFieldsModel
		if(strpos($f, "∞") !== false) {
			return "∞";
		}

		$result = null;
		try {
			eval("\$result = " . $f . ";");
		} catch (\Error $e) {
			$result = null;
		} catch(\Exception $e) {
			$result = null;
		}

		return $result;
	}

	public function beforeSave($value, \go\core\orm\CustomFieldsModel $model, $entity, &$record)
	{
		//remove data because it's not saved to the database
		unset($record[$this->field->databaseName]);

		return true;
	}

}
