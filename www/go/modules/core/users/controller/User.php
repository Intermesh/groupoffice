<?php

namespace go\modules\core\users\controller;

use go\core\auth\model;
use go\core\jmap\EntityController;



class User extends EntityController {	
	
	protected function canUpdate(\go\core\orm\Entity $entity) {
		
		if(!GO()->getUser()->isAdmin()) {
			if($entity->isModified('groups')) {
				return false;
			}
		}
		
		return parent::canUpdate($entity);
	}
	
	protected function canCreate() {
		return GO()->getUser()->isAdmin();
	}
	
	/**
	 * The class name of the entity this controller is for.
	 * 
	 * @return string
	 */
	protected function entityClass() {
		return model\User::class;
	}
}
