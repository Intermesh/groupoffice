<?php
namespace go\core\acl\model;

use go\core\jmap\Entity;
use go\core\jmap\exception\CannotCalculateChanges;
use PDO;
use function GO;

abstract class AclEntity extends Entity {
	/**
	 * Get the current state of this entity
	 * 
	 * @todo ACL state should be per entity and not global. eg. Notebook should return highest mod seq of acl's used by note books.
	 * @return string
	 */
	public static function getState($entityState = null) {
		return parent::getState($entityState) . ':' . Acl::getType()->highestModSeq;
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
		
		if($result['hasMoreUpdates']) {			
			//allready at max
			return $result;
		}
		
		$maxChanges -= (count($result['changed']) + count($result['removed']));
		
		
		//Detect permission changes for AclItemEntities. For example notes that depend on notebook permissions.		
		$acls = static::findAcls();	
		if($acls) {
			$oldAclIds = Acl::wereGranted(GO()->getUserId(), $states[2]['modSeq'], $acls)->all();
			$currentAclIds = Acl::areGranted(GO()->getUserId(), $acls)->all();
			$changedAcls = array_merge(array_diff($oldAclIds, $currentAclIds), array_diff($currentAclIds, $oldAclIds));	
		}
		
		
		//add AclItemEntity changes based on permissions		
		if(empty($changedAcls)) {

			return $result;
		}
			
		$entityType = static::getType();		

		$isAclItem = is_a(static::class, AclItemEntity::class, true);

		$aclTableAlias = $isAclItem ? 'aclEntity' : $query->getTableAlias();

		$query = static::find()
						->fetchMode(PDO::FETCH_ASSOC)
						->select($aclTableAlias . '.aclId')
						->select(static::getPrimaryKey(true), true)
						->where($aclTableAlias . '.aclId', 'in', $changedAcls)
						->offset($states[2]['offset'])
						->limit($maxChanges + 1);


		if($isAclItem) {
			static::joinAclEntity($query);
		}
		
		$query = $query->execute();


		//we don't need entities here. Just a list of id's.
		$i = 0;
		foreach($query as $entity) {				
			$aclId = $entity['aclId'];
			unset($entity['aclId']);
			$id = implode("-", $entity);
			
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
		
		if($query->rowCount() > $maxChanges) {
			$states[2]['offset'] += $maxChanges;
			$result['hasMoreUpdates'] = true;
			$result['newState'] = static::intermediateState($states);
		}
		
		return $result;
	}
}
