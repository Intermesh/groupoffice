<?php
namespace go\core\acl\model;

use go\core\db\Criteria;
use go\core\jmap\Entity;
use go\core\orm\Query;
use PDO;
use function GO;
use go\core\model\Acl;

abstract class AclEntity extends Entity {



	protected $permissionLevel;
	
	/**
	 * Get the current state of this entity
	 * 
	 * @todo ACL state should be per entity and not global. eg. Notebook should return highest mod seq of acl's used by note books.
	 * @return string
	 */
	public static function getState($entityState = null) {
		return parent::getState($entityState) . ':' . Acl::entityType()->getHighestModSeq();
	}
	
	
	protected static function getChangesQuery($sinceModSeq) {
		$changes = parent::getChangesQuery($sinceModSeq);
		
		//apply permissions to changes query
		Acl::applyToQuery($changes, "change.aclId");
		
		return $changes;
	}
	
	public static function getChanges($sinceState, $maxChanges) {
		//state has entity modseq and acl modseq so we can detect permission changes
		$states = static::parseState($sinceState);
		
		
		$result = parent::getChanges($sinceState, $maxChanges);	
		$result['oldState'] = $sinceState;
		
		if($result['hasMoreChanges']) {			
			//allready at max
			return $result;
		}
		
		$maxChanges -= (count($result['changed']) + count($result['removed']));		
		
		//Detect permission changes for AclItemEntities. For example notes that depend on notebook permissions.		
		$acls = static::findAcls();	
		if($acls) {
			$oldAclIds = Acl::wereGranted(go()->getUserId(), $states[2]['modSeq'], $acls)->all();
			$currentAclIds = Acl::areGranted(go()->getUserId(), $acls)->all();
			$changedAcls = array_merge(array_diff($oldAclIds, $currentAclIds), array_diff($currentAclIds, $oldAclIds));	
		}
		
		
		//add AclItemEntity changes based on permissions		
		if(empty($changedAcls)) {

			return $result;
		}

		$isAclItem = is_a(static::class, AclItemEntity::class, true);			

		$aclTableAlias = static::getAclEntityTableAlias();

		$query = static::find()
						->calcFoundRows()
						->fetchMode(PDO::FETCH_ASSOC)
						->select($aclTableAlias . '.aclId')
						->select(static::getPrimaryKey(true), true)
						->where($aclTableAlias . '.aclId', 'in', $changedAcls)
						->offset($states[2]['offset'])
						->limit($maxChanges + 1);


		if($isAclItem) {
			static::joinAclEntity($query);
		}
		$stmt = $query->execute();

		$result['totalChanges'] += $query->foundRows();

		//we don't need entities here. Just a list of id's.
		$i = 0;
		foreach($stmt as $entity) {
			$aclId = $entity['aclId'];
			unset($entity['aclId']);
			$id = count($entity) > 1 ? implode("-", $entity) : array_shift($entity);
						
			//check if already changed
			if(in_array($id, $result['changed']) || in_array($id, $result['removed'])) {
				continue;
			}
			
			if(in_array($aclId, $currentAclIds)) {
				$result['changed'][] = $id;
			} else
			{
				$result['removed'][] = $id;
			}

			$i++;

			if($i == $maxChanges) {				
				break;
			}
			
			
		}
		
		if($stmt->rowCount() > $maxChanges) {
			$states[2]['offset'] += $maxChanges;
			$result['hasMoreChanges'] = true;
			$result['newState'] = static::intermediateState($states);
		}
		
		return $result;
	}
	
	protected static function defineFilters() {
		return parent::defineFilters()
						->add("permissionLevelUserId", function() {
							//dummy used in permissionLevel filter.
						})
						->add("permissionLevelGroups", function() {
							//dummy used in permissionLevel filter.
						})
						->add("permissionLevel", function(Criteria $criteria, $value, Query $query, $filter) {
							//Permission level is always added to the main query so that it's always applied with AND
							static::applyAclToQuery($query, $value, $filter['permissionLevelUserId'] ?? null, $filter['permissionLevelGroups'] ?? null);
						}, Acl::LEVEL_READ);
	}

	/**
	 * Get the table alias holding the aclId
	 */
	abstract public static function getAclEntityTableAlias();
	
}
