<?php

namespace go\core\controller;

use go\core\model;
use function GO;
use go\core\jmap\exception\InvalidArguments;
use go\core\Controller;

class Acl extends Controller {
	
	// protected function canUpdate(Entity $entity) {
		
	// 	$level = model\Acl::getUserPermissionLevel($entity->id, GO()->getUserId());
	// 	if($level != model\Acl::LEVEL_MANAGE) {
	// 		return false;
	// 	}
		
	// 	return parent::canUpdate($entity);
	// }
	
	// protected function canCreate() {
		
	// 	//acl's are not created with the API
	// 	return false;
	// }
	
	// /**
	//  * The class name of the entity this controller is for.
	//  * 
	//  * @return string
	//  */
	// protected function entityClass() {
	// 	return model\Acl::class;
	// }
	
	// /**
	//  * Handles the Foo entity's Foo/query command
	//  * 
	//  * @param array $params
	//  * @see https://jmap.io/spec-core.html#/query
	//  */
	// public function query($params) {
	// 	return $this->defaultQuery($params);
	// }
	
	// /**
	//  * Handles the Foo entity's Foo/get command
	//  * 
	//  * @param array $params
	//  * @see https://jmap.io/spec-core.html#/get
	//  */
	// public function get($params) {
	// 	return $this->defaultGet($params);
	// }
	
	// /**
	//  * Handles the Foo entity's Foo/set command
	//  * 
	//  * @see https://jmap.io/spec-core.html#/set
	//  * @param array $params
	//  */
	// public function set($params) {
	// 	return $this->defaultSet($params);
	// }
	
	
	// /**
	//  * Handles the Foo entity's Foo/changes command
	//  * 
	//  * @param array $params
	//  * @see https://jmap.io/spec-core.html#/changes
	//  */
	// public function changes($params) {
	// 	return $this->defaultChanges($params);
	// }
	
	/**
	 * Reset ACL permissions to the defaults
	 */
	public function reset($params) {
		$params['add'] = $params['add'] ?? false;
		if(!isset($params['entity'])) {
			throw new InvalidArguments("The 'entity' param is required");
		}
		
		$entityType = \go\core\orm\EntityType::findByName($params['entity']);
		
		if(!$entityType) {
			throw new NotFound("Could not find entity '" . $params['entity'] . "'");
		}
		
		$defaultAcl = $entityType->getDefaultAcl();
		$cls = $entityType->getClassName();

		$entities = $cls::find();

		foreach($entities as $entity) {
			if(!$params['add']) {
				$entity->findAcl()->groups = [];
			}
			$entity->setAcl($defaultAcl);
			if(!$entity->save()) {
				throw new \Exception("Could not save default ACL for entity");
			}
		}

		return [];


		// $table = array_values($cls::getMapping()->getTables())[0];
		// $defaultAcl = model\Acl::findById($entityType->getDefaultAclId());
		
		// $aclIds = GO()->getDbConnection()->select('aclId')->from($table->getName());
		// $acls = model\Acl::find()->where('id', 'IN', $aclIds);
		
		// foreach($acls as $acl) {
		// 	if(!$params['add']) {
		// 		$acl->groups = [];
				
		// 		if($entityType->getName() == "Group") {
		// 			// Groups have a special situtation. They must be shared with the group itself so they can see it.
		// 			$group = \go\core\model\Group::find()->where(['aclId' => $acl->id])->single();
		// 			$acl->addGroup($group->id, model\Acl::LEVEL_READ);					
		// 		}
		// 	}
			
		// 	foreach($defaultAcl->groups as $group) {
		// 		$aclGroup = $params['add'] || $entityType->getName() == "Group" ? $acl->findGroup($group->groupId) : false;
		// 		if($aclGroup) {
		// 			$aclGroup->level = $group->level;					
		// 		} else
		// 		{
		// 			$acl->addGroup($group->groupId, $group->level);
		// 		}
		// 	}
			
		// 	if(!$acl->save()) {
		// 		throw new \Exception("Could not save ACL");
		// 	}
		// }
		
		// return [];		
	}
	
}
