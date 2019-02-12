<?php

namespace go\core\controller;

use go\core\acl\model;
use go\core\jmap\EntityController;
use go\core\orm\Entity;
use function GO;

class Acl extends EntityController {
	
	protected function canUpdate(Entity $entity) {
		
		$level = model\Acl::getUserPermissionLevel($entity->id, GO()->getUserId());
		if($level != model\Acl::LEVEL_MANAGE) {
			return false;
		}
		
		return parent::canUpdate($entity);
	}
	
	protected function canCreate() {
		
		//acl's are not created with the API
		return false;
	}
	
	/**
	 * The class name of the entity this controller is for.
	 * 
	 * @return string
	 */
	protected function entityClass() {
		return model\Acl::class;
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
