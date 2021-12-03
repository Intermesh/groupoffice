<?php
namespace go\core\acl\model;

use go\core\db\Criteria;
use go\core\exception\Forbidden;
use go\core\jmap\Entity;
use go\core\orm\Filters;
use go\core\orm\Query;
use PDO;
use function GO;
use go\core\model\Acl;

abstract class AclEntity extends Entity {


	/**
	 * Fires when the ACL has changed.
	 *
	 * Not when changes were made to the acl but when the complete list has been replaced when for example
	 * a contact has been moved to another address book.	 *
	 */
	const EVENT_ACL_CHANGED = 'aclchanged';


	protected $permissionLevel;

	/**
	 * Get the current state of this entity
	 *
	 * @todo ACL state should be per entity and not global. eg. Notebook should return highest mod seq of acl's used by note books.
	 * @param null $entityState
	 * @return string
	 */
	public static function getState($entityState = null): string
	{
		return parent::getState($entityState) . ':' . Acl::entityType()->getHighestModSeq();
	}

	protected static function getEntityChangesQuery($sinceModSeq): Query
	{
		$query = parent::getEntityChangesQuery($sinceModSeq);

		Acl::applyToQuery($query, 'change.aclId');

		return $query;
	}

	public static function getChanges(string $sinceState, int $maxChanges): array
	{

		$result = parent::getChanges($sinceState, $maxChanges);

		//return is admin because ACL's don't apply to admins or when we're at the max of changes
		if(go()->getAuthState()->isAdmin() || $result['hasMoreChanges']) {
			return $result;
		}
		
		$maxChanges -= (count($result['changed']) + count($result['removed']));

		//state has entity modseq and acl modseq so we can detect permission changes
		$states = static::parseState($sinceState);
		
		//Detect permission changes for AclItemEntities. For example notes that depend on notebook permissions.		
		$acls = static::findAcls();	
		if(!$acls) {
			return $result;
		}
		$oldAclIds = Acl::wereGranted(go()->getUserId(), $states[2]['modSeq'], $acls)->all();
		$currentAclIds = Acl::areGranted(go()->getUserId(), $acls)->all();
		$changedAcls = array_merge(array_diff($oldAclIds, $currentAclIds), array_diff($currentAclIds, $oldAclIds));

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



	/**
	 * Return true when the ACL of the entity changes so the EVENT_ACL_CHANGED event will fire
	 *
	 * @return boolean
	 */
	abstract protected function isAclChanged();

	protected function internalSave() : bool
	{
		if (!$this->isNew() && $this->isAclChanged()) {
			static::fireEvent(self::EVENT_ACL_CHANGED, $this);
		}

		return parent::internalSave();
	}


	protected function removeAclOnDelete() {
		return false;
	}


	/**
	 * Get the table alias holding the aclId
	 */
	abstract public static function getAclEntityTableAlias();
	
}
