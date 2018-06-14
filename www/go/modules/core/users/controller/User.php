<?php

namespace go\modules\core\users\controller;

use go\modules\core\users\model;
use go\core\jmap\EntityController;



class User extends EntityController {	
	
	protected function canUpdate(\go\core\orm\Entity $entity) {
		
		if(!GO()->getAuthState()->getUser()->isAdmin()) {
			if($entity->isModified('groups')) {
				return false;
			}
		}
		
		return parent::canUpdate($entity);
	}
	
	protected function canCreate() {
		return GO()->getAuthState()->getUser()->isAdmin();
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
