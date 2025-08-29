<?php

namespace go\core\customfield;

use Exception;

class FunctionField extends Number
{

	private static $loopIds = [];
	
	//no db field for functions
	public function onFieldSave(): bool
	{
		return true;
	}
	
	public function onFieldDelete(): bool
	{
		return true;
	}

	public function hasColumn(): bool
	{
		return false;
	}

	/**
	 * Get column definition for SQL
	 *
	 * @return string
	 * @throws Exception
	 */
	protected function getFieldSQL(): string
	{
		$d = $this->field->getDefault();
		$d = isset($d) && $d != "" ? number_format($d, 4) : "NULL";
		
		$decimals = $this->field->getOption('numberDecimals') + 2;
		
		return "decimal(19,$decimals) DEFAULT " . $d;
	}

	public function dbToApi($value, \go\core\orm\CustomFieldsModel $values, $entity): ?string
	{

		$f = $this->field->getOption("function");

		$f = preg_replace_callback('/\{([^}]*)\}/', function($matches) use($entity){
			$cfs = $entity->getCustomFields(true);
			return isset($cfs->{trim($matches[1])}) ? $cfs->{trim($matches[1])} : 0;
		}, $f);

		if(empty($f)) {
			return null;
		}

		//check for infinity @see CustomFieldsModel
		if(strpos($f, "∞") !== false) {
			return "∞";
		}

		return $this->runMath($f);
	}

	private function runMath(string $math) {
		$tokens = token_get_all("<?php {$math}");
		$expr = '';

		foreach($tokens as $token){

			if(is_string($token)){

				if(in_array($token, array('(', ')', '+', '-', '/', '*'), true))
					$expr .= $token;

				continue;
			}

			list($id, $text) = $token;

			if(in_array($id, array(T_DNUMBER, T_LNUMBER)))
				$expr .= $text;
		}

		try {
			eval("\$result = {$expr};");
		} catch (\Error $e) {
			$result = null;
		} catch(Exception $e) {
			$result = null;
		}

		return $result;

	}

	public function beforeSave($value, \go\core\orm\CustomFieldsModel $model, $entity, &$record): bool	{
		//remove data because it's not saved to the database
		unset($record[$this->field->databaseName]);

		return true;
	}

}
