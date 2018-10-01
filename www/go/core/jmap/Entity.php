<?php

namespace go\core\jmap;

use go\core\db\Query;
use go\core\jmap\exception\CannotCalculateChanges;
use go\core\orm\Entity as OrmEntity;
use PDO;

/**
 * Entity model
 * 
 * An entity is a model that is saved to the database. An entity can have 
 * multiple database tables. It can be extended with has one related tables and
 * it can also have properties in other tables.
 */
abstract class Entity  extends OrmEntity {	
	
	/**
	 * Track changes in the core_change log for the JMAP protocol.
	 * Disabled during install.
	 * 
	 * @var boolean 
	 */
	public static $trackChanges = true;
	
	
	/**
	 * Get the current state of this entity
	 * 
	 * @todo ACL state should be per entity and not global. eg. Notebook should return highest mod seq of acl's used by note books.
	 * @return string
	 */
	public static function getState() {
		return static::getType()->highestModSeq;
	}
	
	
	/**
	 * Saves the model and property relations to the database
	 * 
	 * Important: When you override this make sure you call this parent function first so
	 * that validation takes place!
	 * 
	 * @return boolean
	 */
	protected function internalSave() {
		
		if(!parent::internalSave()) {
			return false;
		}
		
		if(self::$trackChanges) {
			$this->getType()->change($this);		
		}
		
		return true;
	}
	
	/**
	 * Implements soft delete
	 * 
	 * @return boolean
	 */
	protected function internalDelete() {
		
		if(!parent::internalDelete()) {
			return false;
		}
		
		$this->getType()->change($this);
		
		return true;
	}	
	
	
	/**
	 * 
	 * @param string $sinceState
	 * @param int $maxChanges
	 * @return array ['entityId' => 'destroyed' => boolean, modSeq => int]
	 * @throws CannotCalculateChanges
	 */
	public static function getChanges($sinceState, $maxChanges) {
		
		$entityType = static::getType();
		
		//find the old state changelog entry
		if($sinceState) { //If state == 0 then we don't need to check this
			$sinceChange = (new Query())
							->select("*")
							->from("core_change")
							->where(["entityTypeId" => $entityType->getId()])
							->andWhere('modSeq', '=', $sinceState)
							->single();

			if(!$sinceChange) {			
				throw new CannotCalculateChanges();
			}
		}	
		
		$result = [				
			'oldState' => $sinceState,
			'newState' => null,
			'hasMoreUpdates' => false,
			'changed' => [],
			'removed' => []
		];
		
		$changes = static::getChangesQuery($sinceState, $maxChanges);
		
		foreach ($changes as $change) {
			if ($change['destroyed']) {
				$result['removed'][] = $change['entityId'];
			} else {					
				$result['changed'][] = $change['entityId'];
			}
		}
		
		if(isset($change)){
			$result['newState'] = $change['modSeq'];
		} else
		{
			$result['newState'] = static::getState();
		}
		
		$result['hasMoreUpdates'] = $result['newState'] != static::getState();
		
		return $result;		
	}
	
	protected static function getChangesQuery($sinceState, $maxChanges) {
		return (new Query)->select('entityId,max(destroyed) AS destroyed, max(modSeq) AS modSeq')
						->from('core_change')
						->fetchMode(PDO::FETCH_ASSOC)
						->limit($maxChanges)
						->orderBy(['modSeq' => 'ASC'])						
						->groupBy(['entityId'])
						->where(["entityTypeId" => static::getType()->getId()])
						->andWhere('modSeq', '>', $sinceState);
	}
	
}
