<?php

namespace go\core\customfield;

use go\core\ErrorHandler;
use go\core\TemplateParser;

class TemplateField extends TextArea {

	public function beforeSave($value, &$record, \go\core\orm\Entity $entity)
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


}

