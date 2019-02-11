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
}
