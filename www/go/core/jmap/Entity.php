<?php

namespace go\core\jmap;

use go\core\orm\Query;
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
	 * Disabled during install and upgrade.
	 * 
	 * @var boolean 
	 */
	public static $trackChanges = true;
	
	
	/**
	 * Get the current state of this entity
	 * 
	 * This is the modSeq of the main entity joined with a ":" char with user 
	 * table states {@see Mapping::addUserTable()}
	 * 
	 * eg."1:2"
	 * 
	 * @todo ACL state should be per entity and not global. eg. Notebook should return highest mod seq of acl's used by note books.
	 * @return string
	 */
	public static function getState($entityState = null) {
		$state = ($entityState ?? static::entityType()->getHighestModSeq()) . ':';
		
		$state .= static::getMapping()->hasUserTable  ? static::entityType()->getHighestUserModSeq() : "0";		

		return $state;
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
			$this->entityType()->checkChange($this);
		} else
		{
			GO()->warn('Track changes was disabled during save of '. static::class);
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
		
		if(self::$trackChanges) {
			$this->entityType()->checkChange($this);
		} else
		{
			GO()->warn('Track changes was disabled during delete of '. static::class);
		}
		
		return true;
	}	
	
	/**
	 * A state contains:
	 * 
	 * <Entity modSeq>|<offset>:<User modSeq>|<offset>
	 * 
	 * This functon will return:
	 * 
	 * [
	 *	['modSeq' => (int), 'offset' => (int)]
	 * ]
	 * 
	 * The offset is use for intermediate state when paging is needed. This happens
	 * when there are more changes than the maximum allowed.
	 * 
	 * @param string $state
	 * @return array
	 */
	protected static function parseState($state) {
		return array_map(function($s) {
			
			$modSeqAndOffset = explode("|", $s);
			
			return ['modSeq' => (int) $modSeqAndOffset[0], 'offset' => (int) ($modSeqAndOffset[1] ?? 0)];
			
		}, explode(':', $state));
		
	}
	
	/**
	 * The opposite of parseState()
	 * 
	 * @param array $stateArray
	 * @return string
	 */
	protected static function intermediateState($stateArray) {
		return implode(":", array_map(function($s) {	
			return $s['modSeq'] . '|' . $s['offset'];			
		},$stateArray));
	}
	
	
	/**
	 * 
	 * $entityModSeq:$userModSeq-$offset
	 * 
	 * @todo Paging with intermediateState() might not be necessary here. It's 
	 *  required for ACL changes but we could just return the current modseq?
	 *  Changes should be sent in reversed order. Newest first but this complicates paging.
	 * 
	 * @param string $sinceState
	 * @param int $maxChanges
	 * @return array ['entityId' => 'destroyed' => boolean, modSeq => int]
	 * @throws CannotCalculateChanges
	 */
	public static function getChanges($sinceState, $maxChanges) {
		
		$entityType = static::entityType();
		
		
		//states are the main entity state combined with user table states. {@see Mapping::addUserTable()}
		$states = static::parseState($sinceState);

		//find the old state changelog entry
		if($states[0]['modSeq']) { //If state == 0 then we don't need to check this
			
			$change = (new Query())
							->select("modSeq")
							->from("core_change")
							->where(["entityTypeId" => $entityType->getId()])
							->andWhere('modSeq', '=', $states[0]['modSeq'])
							->single();

			if(!$change) {			
				throw new CannotCalculateChanges("Can't calculate changes for state: ". $sinceState);
			}
		}	
		
		$result = [				
			'oldState' => $sinceState,
			'newState' => null,
			'hasMoreChanges' => false,
			'changed' => [],
			'removed' => []
		];		
			
		$userChanges = static::getUserChangesQuery($states[1]['modSeq']);
			
		$changes = static::getEntityChangesQuery($states[0]['modSeq'])
						->union($userChanges)
						->offset($states[1]['offset'])
						->limit($maxChanges + 1)
						->execute();
		
		$count = 0;
		foreach ($changes as $change) {
			$count++;
			if ($change['destroyed']) {
				$result['removed'][] = $change['entityId'];
			} else {					
				$result['changed'][] = $change['entityId'];
			}
			
			if($count == $maxChanges) {
				break;
			}
		}
		
		if($changes->rowCount() > $maxChanges){
			
			$states[1]['offset'] += $maxChanges;
			
			$result['hasMoreChanges'] = true;
			$result['newState'] = static::intermediateState($states);
		} else
		{
			$result['newState'] = static::getState();
		}
		
		$result['hasMoreChanges'] = $result['newState'] != static::getState();
		
		return $result;		
	}
	
	/**
	 * Check if this entities has user properties
	 * 
	 * User properties can vary between users. For example "starred" of a contact
	 * can be different between users.
	 * 
	 * @return boolean
	 */
	public static function hasUserProperties() {
		foreach(static::getMapping()->getTables() as $table) {
			if($table->isUserTable) {
				return true;
			}
		}
		return false;
	}
	
	/**
	 * Get all user property names.
	 * 
	 * User properties can vary between users. For example "starred" of a contact
	 * can be different between users.
	 * 
	 * @return string[]
	 */
	public static function getUserProperties() {
		$p = [];
		foreach(static::getMapping()->getTables() as $table) {
			if($table->isUserTable) {
				$p = array_merge($p, $table->getColumnNames());
			}
		}
		
		return $p;
	}
	
	protected static function getUserChangesQuery($sinceModSeq) {
		return  (new Query())
						->select('entityId, "0" AS destroyed')
						->from("core_change_user", "change_user")
						->where([
								"userId" => GO()->getUserId(),
								"entityTypeId" => static::entityType()->getId()
						])
						->andWhere('modSeq', '>', $sinceModSeq);
	}
	
	
	protected static function getEntityChangesQuery($sinceModSeq) {
		$changes = (new Query)
						->select('entityId,max(destroyed) AS destroyed')
						->from('core_change', 'change')
						->fetchMode(PDO::FETCH_ASSOC)						
						->groupBy(['entityId'])
						->where(["entityTypeId" => static::entityType()->getId()])
						->andWhere('modSeq', '>', $sinceModSeq);
		
	
		return $changes;
	}

}
