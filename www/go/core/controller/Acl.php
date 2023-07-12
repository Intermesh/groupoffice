<?php

namespace go\core\controller;

use Exception;
use GO\Base\Db\ActiveRecord;
use go\core\acl\model\AclOwnerEntity;
use go\core\db\Query;
use go\core\exception\NotFound;
use go\core\model;
use go\core\orm\EntityType;
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

			$table= $cls::model()->tableName();
			$col = 'acl_id';

		} else {
			/** @var AclOwnerEntity $cls */

			$table = $cls::getMapping()->getPrimaryTable()->getName();
			$col = $cls::$aclColumnName;
		}


		$fullAclCol = "$table.$col";

		go()->getDbConnection()->debug = true;


		if(!$params['add']) {
			$stmt = go()->getDbConnection()->delete('core_acl_group',(new Query())
			->where('aclId', 'IN',
				go()->getDbConnection()
					->selectSingleValue($col)->from($table, "t2")
					->join("core_acl", "acl", "acl.id = t2.$col")
					->where("acl.usedIn = '$fullAclCol'")
			));

			$stmt->execute();

			// Add ACL owners
			$stmt = go()->getDbConnection()->insert('core_acl_group',
				go()->getDbConnection()
					->select('t.'.$col.', g.id, "' . model\Acl::LEVEL_MANAGE .'"')
					->distinct()
					->from($table, 't')
					->join('core_acl', 'a', 'a.id=t.'.$col)
					->join('core_group', 'g', 'g.isUserGroupFor=a.ownedBy')
					->where('a.ownedBy != '.model\User::ID_SUPER_ADMIN)
					->where("a.usedIn = '$fullAclCol'")
			);

			$stmt->execute();
		}

		foreach ($defaultAcl as $groupId => $level) {

			$stmt = go()->getDbConnection()
				->insertIgnore(
					'core_acl_group',
					go()->getDbConnection()
						->select($col . ', "' . $groupId . '", "' . $level . '"')
						->from($table, 't')
						->join("core_acl", "acl", "acl.id = t.$col")
						->where("acl.usedIn = '$fullAclCol'"),
					['aclId', 'groupId', 'level']
				);

			$stmt->execute();
		}

		if($cls::entityType()->getName() == "Group") {
			//share groups with themselves
			$stmt = go()->getDbConnection()
				->insertIgnore(
					'core_acl_group',
					go()->getDbConnection()->select($col.', id, "' . model\Acl::LEVEL_READ .'"')->from($table),
					['aclId', 'groupId', 'level']
				);

			$stmt->execute();
		}

		EntityType::resetAllSyncState();
		go()->getSettings()->cacheClearedAt = time();
		/** @noinspection PhpUnhandledExceptionInspection */
		go()->getSettings()->save();

		return [];
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
			->where('m.enabled = 1')
			->where('(u.enabled = 1 OR u.enabled IS NULL)') // NULL for group
			//->andWhere('e.name', '!=', "LogEntry") // Should not be needed because the ACL is copied from other item. a.entityTypeId is not the LogEntry
				->andwhere("e.name != 'Search'")
			->andwhere("e.name != 'LogEntry'")
			->andWhere('a.entityTypeId IS NOT NULL') // default ACLS for type do not have ids
			->andWhere('a.entityId IS NOT NULL')
			->andWhere('a.entityId != 0') //??
			->limit(2000)
			->fetchMode(\PDO::FETCH_OBJ)
			->orderBy(['g.name' => 'ASC']);

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
					continue;
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
				$record->name = isset($names[$record->entityId]) ? $names[$record->entityId] : go()->t("Unknown");
			}
		}

		return ['list'=>$acgs];
	}

}
