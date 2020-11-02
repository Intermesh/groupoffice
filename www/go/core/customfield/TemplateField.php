<?php

namespace go\core\customfield;

use go\core\ErrorHandler;
use go\core\TemplateParser;

class TemplateField extends TextArea {



	public function beforeSave($value, \go\core\orm\CustomFieldsModel $model, $entity, &$record)
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

	public function dbToApi($value, \go\core\orm\CustomFieldsModel $values, $entity)
	{
		if($value === null) {
			//field just added and value not saved yet.
			$this->beforeSave($value, $values, $entity, $record);
			if(!$entity->isNew()) {
				$entity->saveCustomFields();
			}
			$value = $record[$this->field->databaseName];
		}
		return parent::dbToApi($value, $values, $entity);
	}


}

