<?php

namespace go\core\controller;

use go\core\jmap\EntityController;
use go\core\model;

class FieldSet extends EntityController {

	/**
	 * The class name of the entity this controller is for.
	 * 
	 * @return string
	 */
	protected function entityClass() {
		return model\FieldSet::class;
	}

	protected function getQueryQuery($params) {
		return parent::getQueryQuery($params)->orderBy(['sortOrder' => 'ASC']);
	}
	protected function getGetQuery($params) {
		return parent::getGetQuery($params)->orderBy(['sortOrder' => 'ASC']);
	}
}
