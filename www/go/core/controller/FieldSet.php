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
	
	private function checkEnabledModule(\go\core\orm\Query $query) {
		return $query	->join('core_module', 'm', 'm.id = e.moduleId')
						->where(['m.enabled' => true]);
	}

	protected function getQueryQuery($params) {
		return $this->checkEnabledModule(parent::getQueryQuery($params)->orderBy(['sortOrder' => 'ASC', 'id' => 'ASC']));
	}
	protected function getGetQuery($params) {
		return $this->checkEnabledModule(parent::getGetQuery($params)->orderBy(['sortOrder' => 'ASC', 'id' => 'ASC']));
	}
	
	/**
	 * Handles the Foo entity's Foo/query command
	 * 
	 * @param array $params
	 * @see https://jmap.io/spec-core.html#/query
	 */
	public function query($params) {
		return $this->defaultQuery($params);
	}
	
	/**
	 * Handles the Foo entity's Foo/get command
	 * 
	 * @param array $params
	 * @see https://jmap.io/spec-core.html#/get
	 */
	public function get($params) {
		return $this->defaultGet($params);
	}
	
	/**
	 * Handles the Foo entity's Foo/set command
	 * 
	 * @see https://jmap.io/spec-core.html#/set
	 * @param array $params
	 */
	public function set($params) {
		return $this->defaultSet($params);
	}
	
	
	/**
	 * Handles the Foo entity's Foo/changes command
	 * 
	 * @param array $params
	 * @see https://jmap.io/spec-core.html#/changes
	 */
	public function changes($params) {
		return $this->defaultChanges($params);
	}
}
