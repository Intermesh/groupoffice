<?php

namespace go\core\jmap;

use go\core\orm\Query;
use go\core\jmap\exception\CannotCalculateChanges;
use go\core\orm\Entity as OrmEntity;
use PDO;
use go\core\orm\EntityType;
use go\core\acl\model\AclOwnerEntity;
use go\core\acl\model\AclItemEntity;
use go\core\orm\Relation as GoRelation;
use go\core\util\ClassFinder;

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

			$this->checkChangeForRelations();
		} 
		
		return true;
	}

	private function checkChangeForRelations() {
		foreach($this->getMapping()->getRelations() as $r) {

			if($r->type != GoRelation::TYPE_SCALAR) {
				continue;
			}
			$modified = $this->getModified([$r->name]);
			if(empty($modified)) {
				continue;
			}

			$ids = array_merge(array_diff($modified[$r->name][0], $modified[$r->name][1]), array_diff($modified[$r->name][1], $modified[$r->name][0]));

			if(empty($ids)) {
				//Just the order of id's has changed.
				continue;
			}

			$entities = $this->findEntitiesByTable($r->tableName);
			foreach($entities as $e) {
				$cls = $e['cls'];

				$isAclOwnerEntity = is_a($cls, AclOwnerEntity::class, true);
				$isAclItemEntity = is_a($cls, AclItemEntity::class, true);

				foreach($e['paths'] as $path) {
					$query = $cls::find();

					$query->where('id', 'IN', $ids);
					

					$query->select($query->getTableAlias() . '.id AS entityId');

					if($isAclItemEntity) {
						$aclAlias = $cls::joinAclEntity($query);
						$query->select($aclAlias .'.aclId', true);
					} else if($isAclOwnerEntity) {
						$query->select('aclId', true);
					} else{
						$query->select('NULL AS aclId', true);
					}

					$query->select('"0" AS destroyed', true);

					$type = $cls::entityType();

					//go()->warn($query);

					/** @var EntityType $type */
					$type->changes($query);
				}
			}			
		}
	}
	
	/**
	 * Delete's the entitiy. Implements change logging for sync.
	 * 
	 * @param Query $query  The query to select entities in the delete statement
	 * @return boolean
	 */
	protected static function internalDelete(Query $query) {
		
		if(self::$trackChanges) {
			static::changeReferencedEntities($query);
			static::logDeleteChanges($query);
		}

		if(!parent::internalDelete($query)) {
			return false;
		}		
		return true;
	}	

	/**
	 * Log's deleted entities for JMAP sync
	 * 
	 * @param Query $query The query to select entities in the delete statement
	 * @return boolean
	 */
	protected static function logDeleteChanges(Query $query) {
		$ids = clone $query;
		$ids->select($query->getTableAlias() . '.id as entityId, null as aclId, "1" as destroyed');
		return static::entityType()->changes($ids);
	}

	// public static function markChangesForDelete(array $ids, $aclId = null) {
	// 	static::changeReferencedEntities($ids);
	// 	static::entityType()->changes(array_map(function($id) { return ['entityId' => $id, 'aclId' => $aclId, 'destroyed' => true];}, $ids));
	// }

	/**
	 * This function finds all entities that might change because of this delete. 
	 * This happens when they have a foreign key constraint with SET NULL
	 */
	private static function changeReferencedEntities($ids) {
		foreach(static::getEntityReferences() as $r) {
			$cls = $r['cls'];			

			$isAclOwnerEntity = is_a($cls, AclOwnerEntity::class, true);
			$isAclItemEntity = is_a($cls, AclItemEntity::class, true);

			foreach($r['paths'] as $path) {
				$query = $cls::find();

				if(!empty($path)) {
					//TODO joinProperites only joins the first table.
					$query->joinProperties($path);
					$query->where(array_pop($path) . '.' .$r['column'], 'IN', $ids);
				} else{
					$query->where($r['column'], 'IN', $ids);					
				}

				$query->select($query->getTableAlias() . '.id AS entityId');

				if($isAclItemEntity) {
					$aclAlias = $cls::joinAclEntity($query);
					$query->select($aclAlias .'.aclId', true);
				} else if($isAclOwnerEntity) {
					$query->select('aclId', true);
				} else{
					$query->select('NULL AS aclId', true);
				}

				$query->select('"0" AS destroyed', true);

				$type = $cls::entityType();

				//go()->warn($query);

				/** @var EntityType $type */
				$type->changes($query);
			}		
		}
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
								"userId" => go()->getUserId(),
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


		/**
	 * Get all table columns referencing the id column of the entity's main table.
	 * 
	 * It uses the 'information_schema' to read all foreign key relations.
	 * 
	 * @return array [['cls'=>'Contact', 'column' => 'id', 'paths' => []]]
	 */
	protected static function getEntityReferences() {
		$cacheKey = "refs-entity-" . static::class;
		$entityClasses = go()->getCache()->get($cacheKey);
		if($entityClasses === null) {

			$refs = static::getTableReferences();

			$entityClasses = [];
			foreach($refs as $r) {
				$entities = static::findEntitiesByTable($r['table']);
				$eWithCol = array_map(function($i) use($r) {
					$i['column'] = $r['column'];
					return $i;
				}, $entities);

				$entityClasses = array_merge($entityClasses, $eWithCol);
			}	
			
			go()->getCache()->set($cacheKey, $entityClasses);			
		}		
		
		return $entityClasses;
	}

	/**
	 * Find's entities that have the given table name mapped
	 * 
	 * @return Array[] [['cls'=>'', 'paths' => 'contactId']]
	 */
	protected static function findEntitiesByTable($tableName) {
		$cf = new ClassFinder();
		$allEntitites = $cf->findByParent(self::class);

		//don't find the entity itself
		$allEntitites = array_filter($allEntitites, function($e) {
			return $e != static::class;
		});

		$mapped = array_map(function($e) use ($tableName) {
			$paths = $e::getMapping()->hasTable($tableName);
			return [
				'cls' => $e,
				'paths' => $paths
			];

		}, $allEntitites);

		return array_filter($mapped, function($m) {
			return !empty($m['paths']);
		});
	}


	public function jsonSerialize()
	{
		$arr = $this->toArray();
		$arr['id'] = $this->id();

		return $arr;
	}

}
