<?php

namespace go\core\controller;

use GO\Base\Db\ActiveRecord;
use go\core\db\Query;
use go\core\http\Exception;
use go\core\model;
use go\core\orm\EntityType;
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

	public function overview($params) {

		//$level = "CASE ag.level WHEN 10 THEN 'Read' WHEN 20 THEN 'Read/Add' WHEN 30 THEN 'Write' WHEN 40 THEN 'Write/Delete' WHEN 50 THEN 'Manage' ELSE 'Other' END as level";
		$statement = \go()->getDbConnection()
			->select('ag.level, a.entityTypeId as typeId, e.name as type, a.entityId, m.name as module, IFNULL(g.isUserGroupFor, 0) as userGroup, g.name as username')
			->from('core_acl_group', 'ag')
			->join('core_acl', 'a', 'ag.aclId = a.id')
			->join('core_group', 'g', 'g.id = ag.groupId')
			->join('core_user', 'u', 'g.isUserGroupFor = u.id', 'LEFT')
			->join('core_entity', 'e', 'a.entityTypeId = e.id')
			->join('core_module', 'm', 'e.moduleId = m.id')
			->where('(u.enabled = 1 OR u.enabled IS NULL)') // NULL for group
			//->andWhere('e.name', '!=', "LogEntry") // Should not be needed because the ACL is copied from other item. a.entityTypeId is not the LogEntry
			->andWhere('a.entityTypeId IS NOT NULL') // default ACLS for type do not have ids
			->andWhere('a.entityId IS NOT NULL')->limit(2000)
			->fetchMode(\PDO::FETCH_OBJ);
		if(isset($params['filter'])) {
			$filters = isset($params['filter']['conditions']) ? $params['filter']['conditions'] : [$params['filter']];
			foreach($filters as $filter) {
				if(isset($filter['groupId'])) {
					$statement->andWhere('ag.groupId', '=', $filter['groupId']);
				}
				if(isset($filter['moduleId'])) {
					$statement->andWhere('e.moduleId', '=', $filter['moduleId']);
				}
				if(isset($filter['type']) && $filter['type'] !== 'both') {
					$statement->andWhere('g.isUserGroupFor', $filter['type']=='users'? 'IS NOT' :'IS', null );
				}
			}
		}

		$acgs = $statement->all();

		if(count($acgs) < 2000) {
			// only find name of entity if not to many data is loaded.
			$types = [];
			foreach($acgs as $record) {
				if(!isset($types[$record->typeId])) {
					$types[$record->typeId] = [];
				}
				$types[$record->typeId][] = $record->entityId;
			}
			$names = [];
			foreach($types as $typeId => $ids) {
				$et = EntityType::findById($typeId);
				if(!$et) {
					throw new \Exception($typeId);
				}
				$cls = $et->getClassName();
				if(is_a($cls, ActiveRecord::class, true)) {
					$items = $cls::model()->findByAttribute('id', $ids);
				} else {
					$items = $cls::find()->where('id', 'IN', $ids);
				}
				foreach($items as $item) {
					$names[$item->id] = $item->title();
				}
			}
			foreach($acgs as $record) {
				$record->name = isset($names[$record->entityId]) ? $names[$record->entityId] : '';
			}
		}

		return ['list'=>$acgs];
	}
	
}
