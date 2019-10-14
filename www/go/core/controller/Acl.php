<?php

namespace go\core\controller;

use GO\Base\Db\ActiveRecord;
use go\core\model;
use function GO;
use go\core\jmap\exception\InvalidArguments;
use go\core\Controller;

class Acl extends Controller { 
	
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

		if(is_a($cls, ActiveRecord::class, true) ) {
			$entities = $cls::model()->find();

			foreach($entities as $entity) {
				
				/** @var ActiveRecord $entity */
				$acl = $entity->getAcl();
				if(!$params['add']) {
					$acl->clear();
				}

				foreach($defaultAcl as $groupId => $level) {
					$acl->addGroup($groupId, $level);
				}
			}
		} else {			
			$entities = $cls::find();

			foreach($entities as $entity) {
				if(!$params['add']) {
					$entity->findAcl()->groups = [];

					if($entityType->getName() == "Group") {
						// Groups have a special situtation. They must be shared with the group itself so they can see it.
						$entity->findAcl()->addGroup($entity->id, model\Acl::LEVEL_READ);					
					}			
				}
				
				$entity->setAcl($defaultAcl);
				if(!$entity->save()) {
					throw new \Exception("Could not save default ACL for entity");
				}
			}
		}

		return [];


		// $table = array_values($cls::getMapping()->getTables())[0];
		// $defaultAcl = model\Acl::findById($entityType->getDefaultAclId());
		
		// $aclIds = go()->getDbConnection()->select('aclId')->from($table->getName());
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
