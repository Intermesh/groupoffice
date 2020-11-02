<?php

namespace go\core\customfield;

use go\core\ErrorHandler;
use go\core\TemplateParser;

class TemplateField extends TextArea {

	private static $loopIds = [];

	public function beforeSave($value, &$record, $entity)
	{
		$tpl = $this->field->getOption('template');

		$tplParser = new TemplateParser();
		$tplParser->addModel('entity', $entity);

		try {
			$parsed = $tplParser->parse($tpl);
		}
		catch(\Exception $e) {
			ErrorHandler::logException($e);
			$parsed = $e->getMessage();
		}

		$record[$this->field->databaseName] = $parsed;

		return true;
	}

	public function dbToApi($value, &$values, $entity)
	{
		if($value == null) {
			//prevent infinite loop because this function is used in beforeSave too
			if(in_array($this->field->id, self::$loopIds)) {
				return null;
			}

			self::$loopIds[] = $this->field->id;

			//field just added and value not saved yet.
			$this->beforeSave($value, $values, $entity);
			$entity->saveCustomFields();
			$value = $values[$this->field->databaseName];

			self::$loopIds = array_filter(self::$loopIds, function($id) {
				return $id != $this->field->id;
			});
		}
		return parent::dbToApi($value, $values, $entity);
	}

	public function hasColumn() {
		return false;
	}

}

