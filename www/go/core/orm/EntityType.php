<?php

namespace go\core\orm;

use DateTime;
use Exception;
use GO\Base\Db\ActiveRecord;
use go\core\acl\model\AclOwnerEntity;
use go\core\App;
use go\core\auth\Method;
use go\core\data\ArrayableInterface;
use go\core\db\DbException;
use go\core\db\Query;
use go\core\ErrorHandler;
use go\core\fs\Blob;
use go\core\model\Client;
use go\core\model\Module;
use go\core\jmap;
use go\core\model\Acl;
use go\core\model\Search;
use go\core\orm\exception\SaveException;
use go\core\util\Lock;
use GO\Files\Model\Folder;
use InvalidArgumentException;
use PDO;
use PDOException;

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
class EntityType implements ArrayableInterface {

	private $className;
	private $id;
	private $name;
	private $moduleId;
	private $clientName;
	private $defaultAclId;

	/**
	 * The highest mod sequence used for JMAP data sync
	 *
	 * @var int
	 */
	protected $highestModSeq;

	private $highestUserModSeq;

	private $modSeqIncremented = false;
	/**
	 * @var bool
	 */
	private $userModSeqIncremented = false;
	private $enabled;


	/**
	 * The name of the entity for the JMAP client API
	 *
	 * eg. "note"
	 * @return string
	 */
	public function getName(): string
	{
		return $this->clientName;
	}

	/**
	 * The PHP class name used in the PHP API
	 *
	 * @return class-string<Entity>
	 */
	public function getClassName(): string
	{
		return $this->className;
	}

	/**
	 * If the module is disabled this will return false
	 * @return bool
	 */
	public function isEnabled() : bool {
		return $this->enabled;
	}

	/**
	 * The ID in the database used for foreign keys
	 *
	 * @return int
	 */
	public function getId(): int
	{
		return $this->id;
	}

	/**
	 * The module ID this entity belongs to
	 *
	 * @return int
	 */
	public function getModuleId(): int
	{
		return $this->moduleId;
	}

	/**
	 * Get the module this type belongs to.
	 *
	 * @param array $props
	 * @return Module
	 */
	public function getModule(array $props = []): Module
	{
		return Module::findById($this->moduleId, $props);
	}

	/**
	 * Find by PHP API class name
	 *
	 * @param class-string<Entity> $className
	 * @return ?EntityType
	 * @throws Exception
	 */
	public static function findByClassName(string $className) : ?EntityType {
		$clientName = $className::getClientName();
		$c = self::getCache();


		if(!isset($c['name'][$clientName])) {
			$module = Module::findByClass($className, ['id']);

			$record = [];
			$record['moduleId'] = $module->id;
			$record['name'] = self::classNameToShortName($className);
			$record['clientName'] = $clientName;
			try {
				go()->getDbConnection()->insert('core_entity', $record)->execute();
			} catch(DbException $e) {
				// maybe cache is out of date. Delete it for next attempt.
				go()->getCache()->delete('entity-types');
				ErrorHandler::logException($e, "Failed to register new entity type for class '$className'.");
				throw $e;
			}
			$record['id'] = go()->getDbConnection()->getPDO()->lastInsertId();

			go()->getCache()->delete('entity-types');

			$e = new static;
			$e->className = $className;
			$e->id = $record['id'];
			$e->moduleId = $record['moduleId'];
			$e->clientName = $record['clientName'];
			$e->name = $record['name'];

			return $e;
		}

		if(go()->getDebugger()->enabled && !in_array($className, self::$checkedClasses)) {
			//do extra check if entity type belongs to the module
			$module = Module::findByClass($className, ['id'], true);
			if($c['models'][$c['name'][$clientName]]->moduleId != $module->id) {
				throw new Exception("Entity $className conflicts with : " .$c['models'][$c['name'][$clientName]]->getClassName() .". Please return unique client name with getClientName()");
			}

			self::$checkedClasses[] = $className;
		}
		return $c['models'][$c['name'][$clientName]] ?? null;
	}


	private static $checkedClasses = [];

	/**
	 * The highest mod sequence used for JMAP data sync
	 *
	 * @return int
	 * @throws PDOException
	 */
	public function getHighestModSeq(): int{

		if(isset($this->highestModSeq)) {
			return $this->highestModSeq;
		}

		$stmt = go()->getDbConnection()->getCachedStatment("highestModSeq");

		// create reusable statement
		if(!$stmt) {
			$stmt = (new Query())
				->selectSingleValue("highestModSeq")
				->from("core_entity")
				->where('id = :id')
				->createStatement();

			go()->getDbConnection()->cacheStatement("highestModSeq", $stmt);
		}

		$stmt->bindValue(':id' , $this->id);
		$stmt->execute();
		$this->highestModSeq = $stmt->fetch();

		$stmt->closeCursor();

		return $this->highestModSeq ?? 0;
	}

	/**
	 * Clear cached modseqs.
	 *
	 * Calling this function is needed when the request is running for a long time and multiple increments are possible.
	 * For example when sending newsletters on a CLI script.
	 *
	 * @return $this
	 */
	public function clearCache(): EntityType
	{
		$this->highestModSeq = null;
		$this->highestUserModSeq = null;
		$this->modSeqIncremented = false;
		$this->userModSeqIncremented = false;

		return $this;
	}


	/**
	 * Creates a short name based on the class name.
	 *
	 * This is used to generate response name.
	 *
	 * eg. class go\modules\community\notes\model\Note becomes just "note"
	 *
	 * @param $cls
	 * @return string
	 */
	private static function classNameToShortName($cls): string
	{
		return substr($cls, strrpos($cls, '\\') + 1);
	}
	public function __wakeup()
	{
		$this->clearCache();
	}

	/**
	 * Find all registered.
	 *
	 * @return static[]
	 * @throws PDOException
	 */
	public static function findAll(Query|null $query = null): array
	{
		if(!isset($query)) {
			return array_values(static::getCache()['models']);
		}

		$records = $query
			->select('e.id')
			->from('core_entity', 'e')
			->join('core_module', 'm', 'm.id = e.moduleId')
//						->where(['m.enabled' => true])
			->all();

		$i = [];
		foreach($records as $record) {
			$et = self::findById($record['id']);
			if($et) {
				$i[] = $et;
			}
		}

		return $i;
	}

	private static function findFromDb(): array
	{
		$records = go()->getDbConnection()
			->select('e.*, m.name AS moduleName, m.package AS modulePackage, m.enabled')
			->from('core_entity', 'e')
			->join('core_module', 'm', 'm.id = e.moduleId')
//						->where(['m.enabled' => true])
			->all();

		$i = [];
		foreach($records as $record) {
			$type = static::fromRecord($record);
			$cls = $type->getClassName();
			try {
				if (!class_exists($cls) || (!is_a($cls, Entity::class, true) && !is_a($cls, ActiveRecord::class, true))) {
					go()->warn('Entity class "' . $cls . '" in database but it is not found on disk!');
					continue;
				}
			} catch(Exception $e) {
				go()->warn('Entity class "' . $cls . '" in database but there was an error loading it: ' . $e->getMessage());
				continue;
			}
			$i[] = $type;
		}

		return $i;
	}

	/**
	 * @return array
	 */
	private static function getCache() :array {

		$cache = go()->getCache()->get('entity-types');

		if($cache === null) {
			$cache= [
				'id' => [],
				'name' => [],
				'models' => self::findFromDb()
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
	 */
	public static function findById(int $id) {

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
	 */
	public static function findByName(string $name) {

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
	 * @throws Exception
	 */
	public static function namesToIds(array $names): array
	{
		return array_map(function($name) {
			$e = static::findByName($name);
			if(!$e) {
				throw new Exception("Entity '$name'  not found");
			}
			return $e->getId();
		}, $names);
	}


	private static function fromRecord($record): EntityType
	{
		$e = new static;
		$e->id = $record['id'];
		$e->name = $record['name'];
		$e->clientName = $record['clientName'];
		$e->moduleId = $record['moduleId'];
		$e->highestModSeq = (int) $record['highestModSeq'];
		$e->defaultAclId = $record['defaultAclId'] ?? null; // in the upgrade situation this column is not there yet.
		$e->enabled = $record['enabled'];

		if (isset($record['modulePackage'])) {
			if($record['modulePackage'] == 'core') {

				switch($e->name) {
					case "Blob":
						$e->className = Blob::class;
						Break;

					case "Method":
						$e->className = Method::class;
						Break;

					default:
						$e->className = 'go\\core\\model\\' . ucfirst($e->name);
						if (!class_exists($e->className) || !is_a($e->className, Entity::class, true)) {
							$e->className = 'GO\\Base\\Model\\' . ucfirst($e->name);
						}
				}
			} else {
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
	public function changes($changedEntities): bool
	{
		if(!jmap\Entity::$trackChanges) {
			return true;
		}

		$maxChanges = 10000;

		if(!is_array($changedEntities)) {
			//we have to select now because later these id's are gone from the db
			$changedEntities = $changedEntities
				->limit(++$maxChanges)
				->fetchMode(PDO::FETCH_ASSOC)
				->all();
		}

		if(empty($changedEntities)) {
			return true;
		}

		if(count($changedEntities) == $maxChanges) {
			// if there are more than the max resync the entity
			$this->resetSyncState();
			return true;
		}

		if(!is_array($changedEntities[0])) {
			// array of id's. What about acl here?
			foreach($changedEntities as $entityId) {
				$this->queueChange($entityId);
			}
		} else{
			if(count($changedEntities[0]) != 3) {
				throw new InvalidArgumentException("Invalid array given");
			}

			foreach($changedEntities as $r) {
				//query results may pass associative arrays but they must be in the correct order
				$r = array_values($r);
				$this->queueChange($r[0], $r[1], $r[2]);
			}
		}


		return true;
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
	 * @param Entity $entity
	 * @param bool $isDeleted
	 */
	public function change(Entity $entity, bool $isDeleted = false) {
		if(!jmap\Entity::$trackChanges) {
			return;
		}

		$this->queueChange($entity->id(), $entity->findAclId(), $isDeleted);
	}

	/**
	 *
	 * @param int|string $entityId
	 * @param int|null $aclId
	 * @param bool $destroyed
	 * @return void
	 */
	private function queueChange($entityId, ?int $aclId = null, bool $destroyed = false) {

		$id = $this->getId();

		if(!isset(self::$changes[$id])) {
			self::$changes[$id] = [];
		}

		if(!isset(self::$changes[$id][$entityId])) {
			self::$changes[$id][$entityId] = [
				'entityId' => $entityId,
				'aclId' => $aclId,
				'destroyed' => $destroyed
			];
		} else{
			if($destroyed) {
				self::$changes[$id][$entityId]['destroyed'] = true;
			}

			if($aclId) {
				self::$changes[$id][$entityId]['aclId'] = $aclId;
			}
		}
	}

	private static array $changes = [];


	public function undoChanges() : void {
		$id = $this->getId();
		self::$changes[$id] = [];
	}


	/**
	 * Push changes to the database
	 *
	 * When changes are made to entities they are queued. Calling push() will write
	 * them all to the database. When {@see App} is destructed it will also call this method.
	 * {@see EntityController} calls it in defaultSet() so it can return the new state.
	 * We do it like this so these entries are written outside of transactions. Otherwise
	 * this will lead to concurrency problems with deadlocks in mysql.
	 *
	 * @param int $minChanges Only do it if there are more changes than this number
	 * @return void
	 * @throws Exception
	 */
	public static function push(int $minChanges = 1) {

		if(count(self::$changes) < $minChanges) {
			return;
		}

		//db transaction caused deadlocks
		if(!Lock::exists("jmap-set-lock")) {
			$l = new Lock("jmap-set-lock");
			if (!$l->lock()) {
				throw new Exception("Could not obtain lock");
			}
		}

		go()->getDbConnection()->beginTransaction();
		self::pushRecords();
		go()->getDbConnection()->commit();

		if(isset($l)) {
			$l->unlock();
		}
	}

	private static function pushRecords() {

		if(empty(self::$changes)) {
			return;
		}
		$now = new DateTime();
		$allChanges = [];
		foreach(self::$changes as $entityTypeId => $changes) {
			if(empty($changes)) {
				continue;
			}
			$type = self::findById($entityTypeId);

			$modSeq = $type->nextModSeq();

			$allChanges = array_merge($allChanges, array_map(function($change) use($modSeq, $now, $entityTypeId) {
				$change['createdAt'] = $now;
				$change['modSeq'] = $modSeq;
				$change['entityTypeId'] = $entityTypeId;
				return $change;
			}, $changes));

			//Notify SSE that there's a change
		}

		$allChanges = array_values($allChanges);

		go()->debug("Pushing " . count($allChanges). " JMAP sync changes");

		foreach(self::splitRecords($allChanges) as $chunk) {
			$stmt = go()->getDbConnection()->insert('core_change', $chunk);
			$stmt->execute();
		}

		self::$changes = [];
	}

	private static function splitRecords(array $allChanges) : array {
		// mysql limit
		$maxPlaceHolders = 1000;

		if(empty($allChanges)) {
			return [];
		}

		$colCount = count($allChanges[0]);
		$maxRecords = floor($maxPlaceHolders / $colCount);

		return array_chunk($allChanges, $maxRecords);
	}

	/**
	 * Resets the sync state causing all clients to resync this entity
	 *
	 * @return void
	 */
	public function resetSyncState() : void {
		$this->clearCache();

		go()->getDbConnection()
			->update(
				"core_entity",
				['highestModSeq' => 0],
				Query::normalize(["id" => $this->id])
					->tableAlias('entity')
			)->execute(); //mod seq is a global integer that is incremented on any entity update

		go()->getDbConnection()
			->delete(
				"core_change",
				['entityTypeId' => $this->id]
			)
			->execute();

		go()->getCache()->delete('entity-types');
	}

	/**
	 * Resets all entity state so all clients must resync data.
	 */
	public static function resetAllSyncState() {
		//reset all mod seqs
		go()->getDbConnection()->update('core_entity', ['highestModSeq' => 0])->execute();

		// use delete and not truncate to keep transactions
		go()->getDbConnection()->exec("DELETE FROM core_change");
		go()->getDbConnection()->exec("DELETE FROM core_change_user");
		go()->getDbConnection()->exec("DELETE FROM core_acl_group_changes");

		go()->getCache()->delete('entity-types');
	}

	/**
	 * Checks if a saved entity needs changes for the JMAP API with change() and userChange()
	 *
	 * @param Entity $entity
	 * @param bool $force
	 * @throws SaveException
	 */
	public function checkChange(Entity $entity, bool $force = false) {

		$modifiedPropnames = array_keys($entity->getModified());
		/** @noinspection PhpPossiblePolymorphicInvocationInspection */
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
		$stmt->execute();
	}

	/**
	 * Get the modSeq for the user specific properties.
	 *
	 * @return int
	 * @throws PDOException
	 */
	public function getHighestUserModSeq() : int {
		if(!isset($this->highestUserModSeq)) {

			// create reusable statement
			$stmt = go()->getDbConnection()->getCachedStatment("highestUserModSeqStmt");

			if(!$stmt) {
				$stmt = (new Query())
					->selectSingleValue("highestModSeq")
					->from("core_change_user_modseq")
					->where('entityTypeId = :entityTypeId AND userId = :userId')
					->createStatement();

				go()->getDbConnection()->cacheStatement("highestUserModSeqStmt", $stmt);
			}

			$stmt->bindValue(':entityTypeId', $this->id);
			$stmt->bindValue(':userId', go()->getUserId());
			$stmt->execute();

			$this->highestUserModSeq = $stmt->fetch() ?? 0;
		}
		return $this->highestUserModSeq;
	}


	/**
	 * Get the modification sequence
	 *
	 * @return int
	 * @throws PDOException
	 */
	public function nextModSeq() : int {

		$stmt = go()->getDbConnection()
			->update(
				"core_entity",
				'highestModSeq = LAST_INSERT_ID(highestModSeq  +  1)',
				Query::normalize(["id" => $this->id])->tableAlias('entity')
			);

		// on deadlocks retry 10 times
		for($i = 10; $i > -1; $i--) {
			try {
				$stmt->execute(); //mod seq is a global integer that is incremented on any entity update
				break;
			} catch (PDOException $e) {

				if($i == 0) {
					throw $e;
				}
			}
		}

		$modSeq = go()->getDbConnection()
			->query("SELECT LAST_INSERT_ID()")
			->fetch(PDO::FETCH_COLUMN, 0);

		$this->modSeqIncremented = true;
		$this->highestModSeq = $modSeq;

		return $modSeq;
	}

	/**
	 * Get the modification sequence
	 *
	 * @return int
	 * @throws PDOException
	 */
	public function nextUserModSeq() : int {

		$stmt = go()->getDbConnection()
			->update(
				"core_change_user_modseq",
				'highestModSeq = LAST_INSERT_ID(highestModSeq  +  1)',
				Query::normalize([
					"entityTypeId" => $this->id,
					"userId" => go()->getUserId()
				])->tableAlias('entity')
			);

		// on deadlocks retry 10 times
		for($i = 10; $i > -1; $i--) {
			try {
				$stmt->execute(); //mod seq is a global integer that is incremented on any entity update
				break;
			} catch (PDOException $e) {

				if($i == 0) {
					throw $e;
				}
			}
		}


		$modSeq = go()->getDbConnection()
			->query("SELECT LAST_INSERT_ID()")
			->fetch(PDO::FETCH_COLUMN, 0);

		$this->userModSeqIncremented = true;
		$this->highestUserModSeq = $modSeq;

		return $modSeq;
	}

	/**
	 * @return Acl
	 * @throws SaveException
	 * @throws Exception
	 */
	private function createAcl(): Acl
	{
		$acl = new Acl();
		$acl->usedIn = 'core_entity.defaultAclId';
		$acl->entityTypeId = $this->id;
		$acl->ownedBy = 1;
		if(!$acl->save()) {
			throw new SaveException($acl);
		}

		return $acl;
	}

	/**
	 * @throws SaveException|Exception
	 */
	public static function checkDatabase() {

		$all = static::findAll();

		foreach($all as $type) {

			if($type->isAclOwner()) {
				$defaultAclId = $type->getDefaultAclId();
				if (!$defaultAclId) {
					return;
				}
				$acl = Acl::findById($defaultAclId);

				$acl->usedIn = 'core_entity.defaultAclId';
				$acl->entityTypeId = $type->id;
				$acl->ownedBy = 1;
				if (!$acl->save()) {
					throw new SaveException($acl);
				}
			} else{
				if($type->defaultAclId) {
					$type->defaultAclId = null;
					go()->getDbConnection()->update('core_entity',
						['defaultAclId' => null], ['id' => $type->getId()])
						->execute();

				}
			}
		}
	}

	/**
	 * Get ACL id of ACL that holds default permissions
	 *
	 * @return int|null
	 * @throws PDOException
	 * @throws SaveException
	 */
	public function getDefaultAclId(): ?int
	{
		if(!$this->isAclOwner()) {
			return null;
		}

		if(!isset($this->defaultAclId)) {

			go()->getDbConnection()->beginTransaction();

			$acl = $this->createAcl();

			go()->getDbConnection()->update('core_entity', ['defaultAclId' => $acl->id], ['id' => $this->getId()])->execute();

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
	public function isAclOwner(): bool
	{
		$cls = $this->getClassName();
		/** @noinspection PhpPossiblePolymorphicInvocationInspection */
		return $cls != Search::class &&
			(
				is_subclass_of($cls, AclOwnerEntity::class) ||
				(is_subclass_of($cls, ActiveRecord::class) && $cls::model()->aclField() && (!$cls::model()->isJoinedAclField || $cls == Folder::class))
			);
	}

	/**
	 * Returns true if this entity supports custom fields
	 *
	 * @return bool
	 */
	public function supportsCustomFields(): bool
	{
		return method_exists($this->getClassName(), "getCustomFields");
	}

	/**
	 * Returns true if the entity supports a files folder.
	 *
	 * @return bool
	 */
	public function supportsFiles(): bool
	{
		$cls = $this->getClassName();
		return property_exists($cls, 'filesFolderId') || (is_a($cls, ActiveRecord::class, true) && $cls::model()->hasFiles());
	}

	/**
	 * Returns an array with group ID as key and permission level as value.
	 *
	 * @return array eg. ["2" => 50, "3" => 10]
	 * @throws SaveException
	 */
	public function getDefaultAcl(): ?array
	{

		$defaultAclId = $this->getDefaultAclId();
		if(!$defaultAclId) {
			return null;
		}
		$a = Acl::findById($defaultAclId);
		$acl = [];
		foreach($a->groups as $group) {
			$acl[$group->groupId] = $group->level;
		}

		return empty($acl) ? null : $acl;
	}

	/**
	 *
	 * @param $acl
	 * @return bool
	 * @throws SaveException
	 * @throws Exception
	 * @example
	 *
	 * You can manually set the default for a group like this:
	 *
	 * ```
	 * Calendar::entityType()->setDefaultAcl([Group::ID_INTERNAL => Acl::LEVEL_WRITE]);
	 * ```
	 */
	public function setDefaultAcl($acl): bool
	{
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

	/**
	 * @throws SaveException
	 */
	public function toArray(array|null $properties = null): array|null
	{
		return [
			"name" => $this->getName(),
			"isAclOwner" => $this->isAclOwner(),
			"defaultAcl" => $this->getDefaultAcl(),
			"supportsCustomFields" => $this->supportsCustomFields(),
			"supportsFiles" => $this->supportsFiles()
		];
	}
}
