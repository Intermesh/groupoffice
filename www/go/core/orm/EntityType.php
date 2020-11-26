<?php

namespace go\core\orm;

use DateTime;
use Exception;
use GO\Base\Db\ActiveRecord;
use go\core\App;
use go\core\db\Query;
use go\core\model\Module;
use go\core\jmap;
use go\core\model\Acl;
use InvalidArgumentException;

/**
 * The EntityType class
 * 
 * This holds information about the entity.
 * 
 * id: The ID in the database used for foreign keys
 * className: The PHP class name used in the PHP API
 * name: The name of the entity for the JMAP client API
 * moduleId: The module ID this entity belongs to
 * 
 * It's also used for routing short routes like "Note/get" instead of "community/notes/Note/get"
 * 
 */
class EntityType implements \go\core\data\ArrayableInterface {

	private $className;	
	private $id;
	private $name;
	private $moduleId;	
  private $clientName;
	private $defaultAclId;

	private static $cache;
	
	/**
	 * The highest mod sequence used for JMAP data sync
	 * 
	 * @var int
	 */
	protected $highestModSeq;
	
	private $highestUserModSeq;
	
	private $modSeqIncremented = false;
	
	private $userModSeqIncremented = false;
	
	/**
	 * The name of the entity for the JMAP client API
	 * 
	 * eg. "note"
	 * @return string
	 */
	public function getName() {
		return $this->clientName;
	}
	
	/**
	 * The PHP class name used in the PHP API
	 * 
	 * @return Entity
	 */
	public function getClassName() {
		return $this->className;
	}
	
	/**
	 * The ID in the database used for foreign keys
	 * 
	 * @return int
	 */
	public function getId() {
		return $this->id;
	}
	
	/**
	 * The module ID this entity belongs to
	 * 
	 * @return int
	 */
	public function getModuleId() {
		return $this->moduleId;
	}


  /**
   * Get the module this type belongs to.
   *
   * @return Module
   * @throws Exception
   */
	public function getModule() {
		return Module::findById($this->moduleId);
	}

  /**
   * Find by PHP API class name
   *
   * @param string  $className
   * @return static
   * @throws Exception
   */
	public static function findByClassName($className) {

		$clientName = $className::getClientName();
		$c = self::getCache();	
		
		if(!isset($c['name'][$clientName])) {
			$module = Module::findByClass($className);
		
			if(!$module) {
				throw new Exception("No module found for ". $className);
			}

			$record = [];
			$record['moduleId'] = isset($module) ? $module->id : null;
			$record['name'] = self::classNameToShortName($className);
      $record['clientName'] = $clientName;
			App::get()->getDbConnection()->insert('core_entity', $record)->execute();
			$record['id'] = App::get()->getDbConnection()->getPDO()->lastInsertId();

			go()->getCache()->delete('entity-types');

			$e = new static;
			$e->className = $className;
			$e->id = $record['id'];
			$e->moduleId = $record['moduleId'];
			$e->clientName = $record['clientName'];
			$e->name = $record['name'];

			return $e;
		}
		return $c['models'][$c['name'][$clientName]] ?? false;
	}

  /**
   * The highest mod sequence used for JMAP data sync
   *
   * @return int
   * @throws Exception
   */
	public function getHighestModSeq() {
		if(isset($this->highestModSeq)) {
			return $this->highestModSeq;
		}

		$this->highestModSeq = (new Query())
			->selectSingleValue("highestModSeq")
			->from("core_entity")
			->where(["id" => $this->id])			
			->single();

		return $this->highestModSeq;
	}

	/**
	 * Clear cached modseqs.
	 * 
	 * Calling this function is needed when the request is running for a long time and multiple increments are possible.
	 * For example when sending newsletters on a CLI script.
	 * 
	 * @return $this
	 */
	public function clearCache() {

		$this->modSeqIncremented = false;
		$this->userModSeqIncremented = false;
		$this->highestModSeq = null;
		$this->highestUserModSeq = null;

		return $this;
	}


	
	/**
	 * Creates a short name based on the class name.
	 * 
	 * This is used to generate response name. 
	 * 
	 * eg. class go\modules\community\notes\model\Note becomes just "note"
	 * 
	 * @return string
	 */
	private static function classNameToShortName($cls) {
		return substr($cls, strrpos($cls, '\\') + 1);
	}
	public function __wakeup()
	{
		$this->highestModSeq = null;
	}

  /**
   * Find all registered.
   *
   * @return static[]
   * @throws Exception
   */
	public static function findAll(Query $query = null) {
		
		if(!isset($query)) {
			return array_values(static::getCache()['models']);
		}
		
		$records = $query
						->select('e.*, m.name AS moduleName, m.package AS modulePackage')
						->from('core_entity', 'e')
						->join('core_module', 'm', 'm.id = e.moduleId')
						->where(['m.enabled' => true])
						->all();
		
		$i = [];
		foreach($records as $record) {
			$type = static::fromRecord($record);
			$cls = $type->getClassName();
			if(!class_exists($cls) || (!is_a($cls, Entity::class, true) && !is_a($cls, ActiveRecord::class, true))) {
				go()->warn($cls .' not found!');
				continue;
			}
			$i[] = $type;
		}
		
		return $i;
	}

  /**
   * @return array|mixed
   * @throws Exception
   */
	private static function getCache() {
		$cache = go()->getCache()->get('entity-types');

		if($cache === null) {
			$cache= [
				'id' => [],
				'name' => [],
				'models' => self::findAll(new Query)
			];

			for($i = 0, $c = count($cache['models']); $i < $c; $i++) {
			  /** @var self $t */
				$t = $cache['models'][$i];
				$cache['id'][$t->getId()] = $i;
				$cache['name'][$t->getName()] = $i;
			}
			if(!go()->getInstaller()->isInProgress()) {
				go()->getCache()->set('entity-types', $cache);
			}
		}

		return $cache;
	}


  /**
   * Find by db id
   *
   * @param int $id
   * @return static|bool
   * @throws Exception
   */
	public static function findById($id) {

		$c = self::getCache();
		if(!isset($c['id'][$id])) {
			return false;
		}
		return $c['models'][$c['id'][$id]] ?? false;
	}

  /**
   * Find by client API name
   *
   * @param string $name
   * @return static|bool
   * @throws Exception
   */
	public static function findByName($name) {

		$c = self::getCache();
		if(!isset($c['name'][$name])) {
			return false;
		}
		return $c['models'][$c['name'][$name]] ?? false;
	}
	
	/**
	 * Convert array of entity names to ids
	 * 
	 * @param string[] $names eg ['Contact', 'Note']
	 * @return int[] eg. [1,2]
	 */
	public static function namesToIds($names) {
		return array_map(function($name) {
			$e = static::findByName($name);
			if(!$e) {
				throw new Exception("Entity '$name'  not found");
			}
			return $e->getId();
		}, $names);	
	}
  

	private static function fromRecord($record) {
		$e = new static;
		$e->id = $record['id'];
		$e->name = $record['name'];
    $e->clientName = $record['clientName'];
		$e->moduleId = $record['moduleId'];
		$e->highestModSeq = (int) $record['highestModSeq'];
		$e->defaultAclId = $record['defaultAclId'] ?? null; // in the upgrade situation this column is not there yet.

		if (isset($record['modulePackage'])) {
			if($record['modulePackage'] == 'core') {
				$e->className = 'go\\core\\model\\' . ucfirst($e->name);	
				if(!class_exists($e->className)) {
					$e->className = 'GO\\Base\\Model\\' . ucfirst($e->name);	
				}
			} else
			{
				$e->className = 'go\\modules\\' . $record['modulePackage'] . '\\' . $record['moduleName'] . '\\model\\' . ucfirst($e->name);
			}
		} else {			
			$e->className = 'GO\\' . ucfirst($record['moduleName']) . '\\Model\\' . ucfirst($e->name);			
		}
		
		return $e;
	}

  /**
   * Register multiple changes for JMAP
   *
   * This function increments the entity type's modSeq so the JMAP sync API
   * can detect this change for clients.
   *
   * It writes the changes into the 'core_change' table.
   *
   * @param Query|array $changedEntities A query object or an array that provides "entityId", "aclId" and "destroyed"
   * in this order. When using an array you may also provide a list of entity ID's. In that case it's assumed that these
   * entites have no ACL and are not destroyed but modified.
   * @return bool
   * @throws Exception
   */
	public function changes($changedEntities) {

		if(!jmap\Entity::$trackChanges) {
			return true;
		}
		
		go()->getDbConnection()->beginTransaction();
		
		$this->highestModSeq = $this->nextModSeq();		
		
		if(!is_array($changedEntities)) {
			$changedEntities->select('"' . $this->getId() . '", "'. $this->highestModSeq .'", NOW()', true);		
		} else {

			if(empty($changedEntities)) {
				return true;
			}

			if(!is_array($changedEntities[0])) {
				$changedEntities = array_map(function($entityId) {
					return [$entityId, null, 0, $this->getId(), $this->highestModSeq, new DateTime()];
				}, $changedEntities);
			} else{
				if(count($changedEntities[0]) != 3) {
					throw new InvalidArgumentException("Invalid array given");
				}
				$changedEntities = array_map(function($r) {
					return array_merge(array_values($r), [$this->getId(), $this->highestModSeq, new DateTime()]);
				}, $changedEntities);
			}
		}
		
		try {
			$stmt = go()->getDbConnection()->insert('core_change', $changedEntities, ['entityId', 'aclId', 'destroyed', 'entityTypeId', 'modSeq', 'createdAt']);
			$stmt->execute();
		} catch(Exception $e) {
			go()->getDbConnection()->rollBack();
			throw $e;
		}
		
		// Will not work without savepoints
		// if(!$stmt->rowCount()) {
		// 	//if no changes were written then rollback the modSeq increment.
		// 	go()->getDbConnection()->rollBack();
		// } else
		// {
			return go()->getDbConnection()->commit();
		// }				

	}

  /**
   * Register a change for JMAP
   *
   * This function increments the entity type's modSeq so the JMAP sync API
   * can detect this change for clients.
   *
   * It writes the changes into the 'core_change' table.
   *
   * It also writes user specific changes 'core_user_change' table ({@see \go\core\orm\Mapping::addUserTable()).
   *
   * @param jmap\Entity $entity
   * @throws Exception
   */
	public function change(jmap\Entity $entity, $isDeleted = false) {
		if(!jmap\Entity::$trackChanges) {
			return true;
		}
		$this->highestModSeq = $this->nextModSeq();

		$record = [
				'modSeq' => $this->highestModSeq,
				'entityTypeId' => $this->id,
				'entityId' => $entity->id(),
				'aclId' => $entity->findAclId(),
				'destroyed' => $isDeleted,
				'createdAt' => new DateTime()
						];

		go()->getDbConnection()->insert('core_change', $record)->execute();
	}
		
	/**
	 * Checks if a saved entity needs changes for the JMAP API with change() and userChange()
	 * 
	 * @param Entity $entity
	 * @throws Exception
	 */
	public function checkChange(Entity $entity, $force = false) {

		$modifiedPropnames = array_keys($entity->getModified());
		$userPropNames = $entity->getUserProperties();

		$entityModified = !empty(array_diff($modifiedPropnames, $userPropNames));
		$userPropsModified = !empty(array_intersect($userPropNames, $modifiedPropnames));

		if($force || $entityModified) {
			$this->change($entity);
		}
		
		if($userPropsModified) {
			$this->userChange($entity);
		}
	}
	
	private function userChange(Entity $entity) {
		$data = [
				'modSeq' => $this->nextUserModSeq(),						
				'entityTypeId' => $this->id,
				'entityId' => $entity->id(),
				'userId' => go()->getUserId()
						];

		$stmt = go()->getDbConnection()->replace('core_change_user', $data);
		if(!$stmt->execute()) {
			throw new Exception("Could not save user change");
		}
	}

  /**
   * Get the modSeq for the user specific properties.
   *
   * @return string
   * @throws Exception
   */
	public function getHighestUserModSeq() {
		if(!isset($this->highestUserModSeq)) {
			$this->highestUserModSeq = (int) (new Query())
						->selectSingleValue("highestModSeq")
						->from("core_change_user_modseq")
						->where(["entityTypeId" => $this->id, "userId" => go()->getUserId()])
						->single();					
		}
		return $this->highestUserModSeq;
	}


  /**
   * Get the modification sequence
   *
   * @param string $entityClass
   * @return int
   * @throws Exception
   */
	public function nextModSeq() {
		
//		if($this->modSeqIncremented) {
//			return $this->highestModSeq;
//		}
		/*
		 * START TRANSACTION
		 * SELECT counter_field FROM child_codes FOR UPDATE;
		  UPDATE child_codes SET counter_field = counter_field + 1;
		 * COMMIT
		 */
		$modSeq = (new Query())
						->selectSingleValue("highestModSeq")
						->from("core_entity", 'entity')
						->where(["id" => $this->id])
						->forUpdate()
						->single();
		$modSeq++;

		App::get()->getDbConnection()
						->update(
										"core_entity", 
										['highestModSeq' => $modSeq],
										\go\core\orm\Query::normalize(["id" => $this->id])->tableAlias('entity')
						)->execute(); //mod seq is a global integer that is incremented on any entity update
	
		//$this->modSeqIncremented = true;
		
		$this->highestModSeq = $modSeq;
		
		return $modSeq;
	}

  /**
   * Get the modification sequence
   *
   * @param string $entityClass
   * @return int
   * @throws Exception
   */
	public function nextUserModSeq() {
		
		if($this->userModSeqIncremented) {
			return $this->getHighestUserModSeq();
		}
		
		$modSeq = (new Query())
			->selectSingleValue("highestModSeq")
			->from("core_change_user_modseq")
			->where(["entityTypeId" => $this->id, "userId" => go()->getUserId()])
			->forUpdate()
			->single();

		$modSeq++;

		App::get()->getDbConnection()
						->replace(
										"core_change_user_modseq", 
										[
												'highestModSeq' => $modSeq,
												"entityTypeId" => $this->id,
												"userId" => go()->getUserId()
										]
						)->execute(); //mod seq is a global integer that is incremented on any entity update
	
		$this->userModSeqIncremented = true;
		
		$this->highestUserModSeq = $modSeq;
		
		return $modSeq;
	}

  /**
   * @return Acl
   * @throws Exception
   */
	private function createAcl() {
		$acl = new Acl();
		$acl->usedIn = 'core_entity.defaultAclId';
		$acl->ownedBy = 1;
		if(!$acl->save()) {
			throw new Exception('Could not save default ACL');
		}
		
		return $acl;
	}

  /**
   * Get ACL id of ACL that holds default permissions
   *
   * @return int|null
   * @throws Exception
   */
	public function getDefaultAclId() {
		if(!$this->isAclOwner()) {
			return null;
		}
		
		if(!isset($this->defaultAclId)) {
			
			go()->getDbConnection()->beginTransaction();
			
			$acl = $this->createAcl();
			
			if(!go()->getDbConnection()->update('core_entity', ['defaultAclId' => $acl->id], ['id' => $this->getId()])->execute()) {
				go()->getDbConnection()->rollBack();
				throw new Exception("Could not save defaultAclId");
			}
			
			go()->getDbConnection()->commit();
			
			$this->defaultAclId = $acl->id;
		}
		
		return $this->defaultAclId;
	}
	
	/**
	 * Returns true when this entity type holds an ACL id for permissions.
	 * 
	 * @return bool
	 */
	public function isAclOwner() {
		$cls = $this->getClassName();
		return $cls != \go\core\model\Search::class && 
						(
							is_subclass_of($cls, \go\core\acl\model\AclOwnerEntity::class) || 
							(is_subclass_of($cls, \GO\Base\Db\ActiveRecord::class) && $cls::model()->aclField() && !$cls::model()->isJoinedAclField)
						);
	}
	
	/**
	 * Returns true if this entity supports custom fields
	 * 
	 * @return bool
	 */
	public function supportsCustomFields() {
		return method_exists($this->getClassName(), "getCustomFields");
	}
	
	/**
	 * Returns true if the entity supports a files folder.
	 * 
	 * @return bool
	 */
	public function supportsFiles() {
		$cls = $this->getClassName();
		return property_exists($cls, 'filesFolderId') || (is_a($cls, ActiveRecord::class, true) && $cls::model()->hasFiles());
	}

  /**
   * Returns an array with group ID as key and permission level as value.
   *
   * @return array eg. ["2" => 50, "3" => 10]
   * @throws Exception
   */
	public function getDefaultAcl() {

		$defaultAclId = $this->getDefaultAclId();
		if(!$defaultAclId) {
			return null;
		}
		$a = Acl::findById($defaultAclId);
		$acl = [];
		foreach($a->groups as $group) {
			$acl[$group->groupId] = $group->level;
		}

		return $acl;
	}

	/**
	 *
	 * @example
	 *
	 * You can manually set the default for a group like this:
	 *
	 * ```
	 * Calendar::entityType()->setDefaultAcl([Group::ID_INTERNAL => Acl::LEVEL_WRITE]);
	 * ```
	 * @param $acl
	 * @return bool
	 * @throws Exception
	 */
	public function setDefaultAcl($acl) {
		$defaultAclId = $this->getDefaultAclId();
		if(!$defaultAclId) {
			throw new Exception("Entity '".$this->name."' does not support a default ACL");
		}
		$a = Acl::findById($defaultAclId);
		foreach($acl as $groupId => $level) {
			$a->addGroup($groupId, $level);
		}
		return $a->save();
	}

	public function toArray($properties = null) {
		return [
				"name" => $this->getName(),
				"isAclOwner" => $this->isAclOwner(),
				"defaultAcl" => $this->getDefaultAcl(),
				"supportsCustomFields" => $this->supportsCustomFields(),
				"supportsFiles" => $this->supportsFiles()
		];
	}
}
