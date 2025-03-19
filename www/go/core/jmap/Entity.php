<?php

namespace go\core\jmap;

use DateTimeInterface;
use Exception;
use GO\Base\Exception\AccessDenied;
use go\core\App;
use go\core\ErrorHandler;
use go\core\model\Alert;
use go\core\model\Module;
use go\core\orm\Query;
use go\core\jmap\exception\CannotCalculateChanges;
use go\core\orm\Entity as OrmEntity;
use PDO;
use go\core\acl\model\AclOwnerEntity;
use go\core\acl\model\AclItemEntity;
use go\core\orm\Relation as GoRelation;
use go\core\util\ClassFinder;
use GO\Files\Model\Folder;

/**
 * Entity model
 * 
 * An entity is a model that is saved to the database. An entity can have 
 * multiple database tables. It can be extended with has one related tables and
 * it can also have properties in other tables.
 */
abstract class Entity  extends OrmEntity {

	/**
	 * Fires when an URL for the entity is generated
	 */
	const EVENT_URL = "url";

	/**
	 * Track changes in the core_change log for the JMAP protocol.
	 * Disabled during install and upgrade.
	 * 
	 * @var boolean 
	 */
	public static $trackChanges = true;


	/**
	 * Returns true if the entity should be logged in the history module
	 *
	 * @return bool
	 */
	public static function loggable(): bool
	{
		return true;
	}

	/**
	 * Used by the history module to log changes. Override to hide sensitive details for example.
	 *
	 * @return bool|array
	 */
	public function historyLog(): bool|array
	{
		return $this->getModified();
	}


	/**
	 * Get the current state of this entity
	 *
	 * This is the modSeq of the main entity joined with a ":" char with user
	 * table states {@see Mapping::addUserTable()}
	 *
	 * eg."1:2"
	 *
	 * @todo ACL state should be per entity and not global. eg. Notebook should return highest mod seq of acl's used by note books.
	 * @param null $entityState
	 * @return string
	 */
	public static function getState($entityState = null): string
	{
		$state = ($entityState ?? static::entityType()->getHighestModSeq()) . ':';
		
		$state .= static::getMapping()->hasUserTable  ? static::entityType()->getHighestUserModSeq() : "0";		

		return self::appendCacheCleared($state);
	}

  /**
   * Saves the model and property relations to the database
   *
   * Important: When you override this make sure you call this parent function first so
   * that validation takes place!
   *
   * @return boolean
   * @throws Exception
   */
	protected function internalSave(): bool
	{
		
		if(!parent::internalSave()) {
			App::get()->debug(static::class."::internalSave() returned false");
			return false;
		}

		if(static::supportsFiles()) {
			$this->checkFilesFolder();
		}

		$this->saveTmpFiles();

		return true;
	}

	protected function commitToDatabase(): bool
	{
		if(!parent::commitToDatabase()) {
			return false;
		}

		if(self::$trackChanges) {
			try {
				$this->change();
			} catch(Exception $e) {
				//if committing succeeded we must return true otherwise the parent class will rollback an already committed transaction
				//messing up the flow
				ErrorHandler::logException($e);
			}
		}

		return true;
	}

	private $tmpFiles;

	/**
	 * Logs change for this entity
	 *
	 * @param bool $force
	 * @throws Exception
	 */
	public function change(bool $force = false) {

		$this->entityType()->checkChange($this, $force);
		$this->checkChangeForScalarRelations();
	}

	/**
	 * Set files to be saved into the files folder after save
	 *
	 * eg.
	 *
	 * [
	 *  ['name'=>'foo.txt', 'tmpFile' => 'relative/path/to/foo.txt']
	 * ]
	 * @param array $files
	 */
	public function setTmpFiles(array $files) {
		$this->tmpFiles = $files;
	}

	/**
	 * @throws Exception
	 */
	private function saveTmpFiles() {
		if(empty($this->tmpFiles)) {
			return;
		}

		$folder = Folder::model()->findForEntity($this);
		while ($f = array_shift($this->tmpFiles)) {
			if (!empty($f['tmpFile'])) {
				$file = go()->getTmpFolder()->getFolder(go()->getAuthState()->getUserId())->getFile($f['tmpFile']);
				$dest = go()->getDataFolder()->getFile($folder->path . '/' . $f['name']);
				$dest->appendNumberToNameIfExists();
				$file->move($dest);
				$folder->addFile($dest->getName());
			}
		}
	}

	/**
	 * Toggle checking of files folder. Used to speedup 6.3 to 6.4 upgrade
	 * @var bool
	 */
	public static $checkFilesFolder = true;

	/**
	 * @param bool $force Used in database check to force a check
	 * @return bool
	 * @throws AccessDenied
	 * @noinspection PhpReturnValueOfMethodIsNeverUsedInspection
	 */
	private function checkFilesFolder(bool $force = false): bool
	{
		if(!self::$checkFilesFolder || empty($this->filesFolderId)) {
			return true;
		}

		$filesPathProperties = static::filesPathProperties();
		if(!empty($filesPathProperties)) {

			if($force || $this->isModified($filesPathProperties)) {
				$oldFilesFolderId = $this->filesFolderId;
				$folder = Folder::model()->findForEntity($this, false);

				if($folder->id != $oldFilesFolderId) {
					$this->filesFolderId = $folder->id;
					if(!go()->getDbConnection()
							->update($this->getMapping()->getPrimaryTable()->getName(), 
											['filesFolderId' => $this->filesFolderId], 
											['id' => $this->id])
							->execute()) {
						return false;
					}
				}
			}
		}
		return true;
	}

	public static function check()
	{
		parent::check();

		static::checkFiles();
	}

	/**
	 * @throws AccessDenied
	 */
	protected static function checkFiles() {
		if(static::supportsFiles()) {

			$filesPathProperties = static::filesPathProperties();
			if(is_a(static::class, AclOwnerEntity::class, true)) {
				$filesPathProperties[] = static::$aclColumnName;
			}

			/** @noinspection PhpRedundantOptionalArgumentInspection */
			$entities = static::find(array_merge(['id', 'filesFolderId'], $filesPathProperties))
				->where('filesFolderId', '!=', null);
//				->where('filesFolderId', 'NOT IN', (new Query())->select('id')->from('fs_folders'));

			foreach($entities as $e) {
				$e->checkFilesFolder(true);
			}

//			//update fs_folders set acl_id = 0 where acl_id not in (select id from core_acl)
//			// select * from fs_folders where acl_id not in (select id from core_acl)
//			if(is_a(static::class, AclOwnerEntity::class, true)) {
//				$entities = static::find(array_merge(['id', 'filesFolderId'], static::filesPathProperties()));
//
//				$entities->join('fs_folders', 'f', 'f.id = '.$entities->getTableAlias() .'.filesFolderId')
//					->where('f.acl_id', 'NOT IN', (new Query())->select('id')->from('core_acl'));
//
//				//$sql = (string) $entities;
//
//				foreach($entities as $e) {
//					$e->checkFilesFolder(true);
//				}
//			}
		}
	}

	/**
	 * Check if this entity supports a files folder
	 * 
	 * @return bool
	 */
	private static function supportsFiles(): bool
	{
		return property_exists(static::class, 'filesFolderId') && Module::isInstalled("legacy", "files", true);
	}

	/**
	 * Override to use different ACL for files.
	 *
	 * @return int
	 * @throws Exception
	 */
	public function filesFolderAclId(): int
	{
		return $this->findAclId();
	}

  /**
   * Return a relative path to store the files in. Must be unique!
   *
   * @return string
   * @throws Exception
   */
	public function buildFilesPath(): string
	{
		$entityType = self::entityType();
		return $entityType->getModule()->name. '/' . $entityType->getName() . '/' . $this->id();
	}

	/**
	 * Returns properties that affect the files returned in "buildFilesPath()"
	 * When these properties change the system will move the folder to the new location.
	 * 
	 * @return string[]
	 */
	protected static function filesPathProperties(): array
	{
		return [];
	}

  /**
   * Check's if this save operation affects other using the same scalar relation entities by using database references
   *
   * For example change user when a group is created with this user
   *
   * @throws Exception
   */
	private function checkChangeForScalarRelations() {
		foreach($this->getMapping()->getRelations() as $r) {

			if($r->type != GoRelation::TYPE_SCALAR && $r->type != GoRelation::TYPE_MAP) {
				continue;
			}
			$modified = $this->getModified([$r->name]);
			if(empty($modified)) {
				continue;
			}

			// The ID"s of the relation
      if($r->type == GoRelation::TYPE_SCALAR) {
        $ids = array_merge(array_diff($modified[$r->name][0], $modified[$r->name][1]), array_diff($modified[$r->name][1], $modified[$r->name][0]));
        $tableName = $r->tableName;
      } else{
        $newKeys = isset($modified[$r->name][0]) ? array_keys($modified[$r->name][0]) : [];
        $oldKeys = isset($modified[$r->name][1]) ? array_keys($modified[$r->name][1]) : [];
        $ids = array_merge(array_diff($newKeys, $oldKeys), array_diff($oldKeys, $newKeys));
        $tableName = $r->propertyName::getMapping()->getPrimaryTable()->getName();
      }

			if(empty($ids)) {
				//Just the order of id's has changed.
				continue;
			}

			$entities = $this->findEntitiesByTable($tableName);
			$classes = array_unique(array_map(function($e) {return $e['cls'];},$entities));
			foreach($classes as $cls) {
			  $query = $cls::find();
        $query->where('id', 'IN', $ids);
        static::changesQuery($query, $cls);
			}			
		}
	}

  /**
   * Marks changes for query already prepared for selecting the right ID's
   *
   * @param Query $query
   * @param class-string<Entity> $cls
   * @throws Exception
   */
	private static function changesQuery(Query $query, string $cls) {

		$pkSelect = static::buildPrimaryKeySelect($query, $cls);

    $query->select($pkSelect . ' AS entityId');

    if(is_a($cls, AclItemEntity::class, true)) {
      $aclAlias = $cls::joinAclEntity($query);
      $query->select($aclAlias, true);
    } else if(is_a($cls, AclOwnerEntity::class, true)) {
      $query->select($query->getTableAlias() . "." . $cls::$aclColumnName, true);
    } else{
      $query->select('NULL AS aclId', true);
    }

    $query->select('"0" AS destroyed', true);

		$type = $cls::entityType();
		$type->changes($query);
  }

  /**
   * Deletes the entity. Implements change logging for sync.
   *
   * @param Query $query The query to select entities in the delete statement
   * @return boolean
   * @throws Exception
   */
	protected static function internalDelete(Query $query): bool
	{
		
		if(self::$trackChanges) {
			static::changeReferencedEntities($query);
			static::logDeleteChanges($query);
		}

		try {
			if (static::supportsFiles()) {
				if (!static::deleteFilesFolders($query)) {
					static::entityType()->undoChanges();
					return false;
				}
			}

			if (!parent::internalDelete($query)) {
				static::entityType()->undoChanges();
				return false;
			}
		} catch(Exception $e) {
			static::entityType()->undoChanges();
			throw $e;
		}
		return true;
	}

  /**
   * Log's deleted entities for JMAP sync
   *
   * @param Query $query The query to select entities in the delete statement
   * @return boolean
   * @throws Exception
   */
	protected static function deleteFilesFolders(Query $query): bool
	{
		$idsQuery = clone $query;
		/** @noinspection PhpRedundantOptionalArgumentInspection */
	 	$idsQuery->selectSingleValue($query->getTableAlias() . '.filesFolderId')
		 	->groupWhere()
			->andWhere($query->getTableAlias() . '.filesFolderId', '!=', null);

		 $ids = $idsQuery->all();



		return static::internalDeleteFilesFolders($ids);
	}

	protected static function internalDeleteFilesFolders(array $folderIds): bool
	{
		// make sure ID=0 is not there. Shouldn't be but this caused a disaster with a root folder with id=0 wiping
		// the data
		$folderIds = array_filter($folderIds, function($id) {
			return !empty($id);
		});
		if(empty($folderIds)) {
			return true;
		}
		$folders = Folder::model()->findByAttribute('id', $folderIds);
		foreach($folders as $folder) {
			if(!$folder->delete(true)) {
				return false;
			}
		}
		return true;
	}

  /**
   * Log's deleted entities for JMAP sync
   *
   * @param Query $query The query to select entities in the delete statement
   * @return boolean
   * @throws Exception
   */
	protected static function logDeleteChanges(Query $query): bool
	{
		$idsQuery = clone $query;
		$records = $idsQuery
			->select(static::buildPrimaryKeySelect($query, static::class) . ' as entityId, null as aclId, "1" as destroyed')
			->fetchMode(PDO::FETCH_ASSOC);
		return static::entityType()->changes($records);
	}


	/**
	 * Marks changes for query already prepared for selecting the right ID's
	 *
	 * @param Query $query
	 * @param class-string<Entity> $cls
	 * @return string
	 */
	protected static function buildPrimaryKeySelect(Query $query, string $cls) : string {
		$primaryKeys = $cls::getPrimaryKey();

		if(count($primaryKeys) == 1) {
			$pkSelect = $query->getTableAlias() . '.'.$primaryKeys[0];
		} else{

			//PK Logic consistent with {@see Property::id()};
			$alias = $query->getTableAlias();
			$pkSelect = "CONCAT(" . $alias .'.' . implode( ', "-", '. $alias .'.', $primaryKeys) .')';
		}

		return $pkSelect;
	}

  /**
   * This function finds all entities that might change because of this delete.
   * This happens when they have a foreign key constraint with SET NULL
   * @param Query $ids
   * @throws Exception
   */
	private static function changeReferencedEntities(Query $ids): void
	{
		foreach(static::getEntityReferences() as $r) {
			$cls = $r['cls'];

			foreach($r['paths'] as $path) {
        /** @var Query $query */
				$query = $cls::find();

				if(!empty($path)) {
					//TODO joinProperties only joins the first table.
					$query->joinProperties($path);
					$query->where(array_pop($path) . '.' .$r['column'], 'IN', $ids);
				} else{
					$query->where($r['column'], 'IN', $ids);					
				}
				static::changesQuery($query, $cls);
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
	protected static function parseState(string $state): array
	{
		$state = static::checkCacheCleared($state);

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
	protected static function intermediateState(array $stateArray): string
	{
		return implode(":", array_map(function($s) {	
			return $s['modSeq'] . '|' . $s['offset'];			
		},$stateArray));
	}


	private static function checkCacheCleared(string $state) : string {
		$firstColon = strpos($state, ":");
		if($firstColon === false || substr($state, 0, $firstColon) != go()->getSettings()->cacheClearedAt) {
			throw new CannotCalculateChanges("Resync required");
		}

		return substr($state, $firstColon + 1);
	}

	private static function appendCacheCleared(string $state) : string {
		return go()->getSettings()->cacheClearedAt . ':' . $state;
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
   * @throws Exception
   */
	public static function getChanges(string $sinceState, int $maxChanges): array
	{
		$result = [
			'oldState' => $sinceState,
			'changed' => [],
			'removed' => []
		];

		if($sinceState == self::getState()) {
			$result['newState'] = $sinceState;
			return $result;
		}
		
		$entityType = static::entityType();
		
		//states are the main entity state combined with user table states. {@see Mapping::addUserTable()}
		$states = static::parseState($sinceState);

		//find the old state changelog entry
		if($states[0]['modSeq']) { //If state == 0 then we don't need to check this
			
			$stmt = (new Query())
							->select("modSeq")
							->from("core_change")
							->where(["entityTypeId" => $entityType->getId()])
							->andWhere('modSeq', '=', $states[0]['modSeq']);

			$change = $stmt->single();

			if(!$change) {			
				throw new CannotCalculateChanges("Can't calculate changes for '" . $entityType->getName() . "' with state: ". $sinceState .' ('.$states[0]['modSeq'].')');
			}
		}	
		

			
		$userChanges = static::getUserChangesQuery($states[1]['modSeq']);
			
		$changesQuery = static::getEntityChangesQuery($states[0]['modSeq'])
						->union($userChanges)
						->offset($states[1]['offset'])
						->limit($maxChanges + 1);
		$changes = $changesQuery->execute();

		
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


		//unofficial response but we use it to process no more than 100000 changes. A resync is
		//more efficient in the webclient in that case.
		$result['totalChanges'] = 0;//$changesQuery->foundRows();
		
		if($changes->rowCount() > $maxChanges && $count) {
			$states[1]['offset'] += $maxChanges;
			$result['newState'] = static::intermediateState($states);
		} else
		{
			$result['newState'] = static::getState();
		}
		
		$result['hasMoreChanges'] = $result['newState'] != static::getState();
		
		return $result;		
	}

  /**
   * Check if this entity has user properties
   *
   * User properties can vary between users. For example "starred" of a contact
   * can be different between users.
   *
   * @return boolean
   * @throws Exception
   */
	public static function hasUserProperties(): bool
	{
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
   * User properties belong to users and can be different between users. For example "starred" of a contact
   * can be different between users.
   *
   * @return string[]
   * @throws Exception
   */
	public static function getUserProperties(): array
	{
		$p = [];
		foreach(static::getMapping()->getTables() as $table) {
			if($table->isUserTable) {
				$p = array_merge($p, $table->getColumnNames());
			}
		}
		
		return $p;
	}

  /**
   * @param $sinceModSeq
   * @return Query
   * @throws Exception
   */
	protected static function getUserChangesQuery($sinceModSeq): Query
	{
		return (new Query())
						->select('entityId, "0" AS destroyed')
						->from("core_change_user", "change_user")
						->where([
								"userId" => go()->getUserId(),
								"entityTypeId" => static::entityType()->getId()
						])
						->andWhere('modSeq', '>', $sinceModSeq);
	}

  /**
   * @param $sinceModSeq
   * @return Query
   * @throws Exception
   */
	protected static function getEntityChangesQuery($sinceModSeq): Query
	{
    return (new Query)
            ->select('entityId,max(destroyed) AS destroyed')
            ->from('core_change', 'change')
	          ->useIndex("USE INDEX (core_change_modSeq_entityTypeId_entityId_index)")
            ->fetchMode(PDO::FETCH_ASSOC)
            ->groupBy(['entityId'])
            ->where(["entityTypeId" => static::entityType()->getId()])
            ->andWhere('modSeq', '>', $sinceModSeq);
	}


  /**
   * Get all table columns referencing the id column of the entity's main table.
   *
   * It uses the 'information_schema' to read all foreign key relations.
   *
   * @return array [['cls'=>'Contact', 'column' => 'id', 'paths' => []]]
   * @throws Exception
   */
	protected static function getEntityReferences(): array
	{
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
   * Find's JMAP entities that have the given table name mapped
   *
   * @param string $tableName
   * @return array[] [['cls'=>'jmap\Entity', 'paths' => 'contactId']]
   */
	protected static function findEntitiesByTable(string $tableName): array
	{
		$cacheKey = "findEntitiesByTable-" . $tableName;
		$cached = go()->getCache()->get($cacheKey);
		if(isset($cached)) {
			return $cached;
		}

		$cf = new ClassFinder();
		$allEntities = $cf->findByParent(Entity::class);

		//don't find the entity itself
    $allEntities = array_filter($allEntities, function($e) {
			return $e != static::class;
		});

		$mapped = array_map(function($e) use ($tableName) {
			$paths = $e::getMapping()->hasTable($tableName);
			return [
				'cls' => $e,
				'paths' => $paths
			];

		}, $allEntities);

		$result = array_filter($mapped, function($m) {
			return !empty($m['paths']);
		});

		go()->getCache()->set($cacheKey, $result);

		return $result;
	}

	public function jsonSerialize() : mixed
	{
		$arr = $this->toArray();
		$arr['id'] = $this->id();

		return $arr;
	}

	/**
	 * Create an alert for this entity.
	 *
	 * Typically, you set a tag and data. Previous alerts with the same tag and model will be replaced with this one.
	 * If you don't use a tag it won't replace anything.
	 *
	 * It's up to the client to display the data in a human friendly way. See
	 * www/go/modules/community/tasks/views/extjs3/Module.js for an example in the initModule() function
	 *
	 * @example
	 * ```
	 * $alert = $task->createAlert(new \DateTime(), 'assigned', $this->responsibleUserId)
	 *  ->setData([
	 *    'assignedBy' => go()->getAuthState()->getUserId()
	 *  ]);
	 *
	 * if (!$alert->save()) {
	 *  throw new SaveException($alert);
	 * }
	 * ```
	 *
	 *
	 *
	 *
	 * @param DateTimeInterface $triggerAt
	 * @param ?string $tag A unique tag for this entity and user. It will replace existing ones.
	 * @param int|null $userId The user this alert is for. Defaults to current user.
	 * @return Alert
	 */
	public function createAlert(DateTimeInterface $triggerAt,
	                            ?string            $tag = null,
	                            int               $userId = null): Alert
	{
		$alert = new Alert();

		$alert->triggerAt = $triggerAt;
		$alert->userId = $userId ?? go()->getAuthState()->getUserId();
		$alert->entityId =  $this->id;
		$alert->entityTypeId = static::entityType()->getId();
		$alert->tag = $tag;

		return $alert;
	}

	/**
	 * Delete an alert
	 *
	 * @param string $tag A unique tag for this entity and user. It will replace existing ones.
	 * @param int|null $userId The user this alert is for. Defaults to current user.
	 * @return bool
	 * @throws Exception
	 */
	public function deleteAlert(string $tag, int $userId = null): bool
	{
		return Alert::delete([
			'entityTypeId' => self::entityType()->getId(),
			'entityId' => $this->id,
			'tag' => $tag,
			'userId' => $userId ?? go()->getAuthState()->getUserId()
		]);
	}

	/**
	 * Called when reminders are deleted / dismissed
	 *
	 *
	 * @param Alert[] $alerts
	 */
	public static function dismissAlerts(array $alerts) {

	}


	public function url() {

		$url = static::fireEvent(self::EVENT_URL, $this);

		if(!empty($url)) {
			return $url;
		}

		return go()->getSettings()->URL . '#' . strtolower(static::entityType()->getName()) . "/" . $this->id();
	}


	/**
	 * Does nothing by default
	 * @return void
	 */
	public static function checkAcls() {

	}

}
