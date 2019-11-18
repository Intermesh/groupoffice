<?php

namespace go\core\jmap;

use Exception;
use go\core\model\Acl;
use go\core\acl\model\AclEntity;
use go\core\App;
use go\core\Controller;
use go\core\data\convert\AbstractConverter;
use go\core\db\Criteria;
use go\core\exception\Forbidden;
use go\core\fs\Blob;
use go\core\jmap\exception\CannotCalculateChanges;
use go\core\jmap\exception\InvalidArguments;
use go\core\jmap\exception\StateMismatch;
use go\core\jmap\SetError;
use go\core\orm\Entity;
use go\core\orm\Query;
use go\core\util\ArrayObject;
use PDO;

abstract class EntityController extends Controller {	
	
	/**
	 * The class name of the entity this controller is for.
	 * 
	 * @return string
	 */
	abstract protected function entityClass();

	
	/**
	 * Creates a short name based on the class name.
	 * 
	 * This is used to generate response name. 
	 * 
	 * eg. class go\modules\community\notes\model\Note becomes just "note"
	 * 
	 * @return string
	 */
	protected function getShortName() {
		$cls = $this->entityClass();
		return lcfirst(substr($cls, strrpos($cls, '\\') + 1));
	}
	
	/**
	 * Creates a short plural name 
	 * 
	 * @see getShortName()
	 * 
	 * @return string
	 */
	protected function getShortPluralName() {
		
		$shortName = $this->getShortName();
		
		if(substr($shortName, -1) == 'y') {
			return substr($shortName, 0, -1) . 'ies';
		} else
		{
			return $shortName . 's';
		}
	}
	
	/**
	 * 
	 * @param array $params
	 * @return Query
	 */
	protected function getQueryQuery($params) {
		$cls = $this->entityClass();

		$query = $cls::find($cls::getPrimaryKey(false), true)						
						->limit($params['limit'])
						->offset($params['position']);

		if($params['calculateTotal']) {
			$query->calcFoundRows();
		}
		
		/* @var $query Query */

		$sort = $this->transformSort($params['sort']);		

		if(!empty($query->getGroupBy())) {
			//always add primary key for a stable sort. (https://dba.stackexchange.com/questions/22609/mysql-group-by-and-order-by-giving-inconsistent-results)		
			$keys = $cls::getPrimaryKey();
			foreach($keys as $key) {
				if(!isset($sort[$key])) {
					$sort[$key] = 'ASC';
				}
			}
		}
		
		$cls::sort($query, $sort);

		$this->applyFilterCondition($params['filter'], $query);		
				
		if(!$this->permissionLevelFoundInFilters && is_a($this->entityClass(), AclEntity::class, true)) {
			$query->filter(["permissionLevel" => Acl::LEVEL_READ]);
		}
		
		//go()->info($query);
		$query->select($cls::getPrimaryKey(true)); //only select primary key
		
		return $query;
	}
	
	private $permissionLevelFoundInFilters = false;
	
	/**
	 * 
	 * @param array $filter
	 * @param Query $query
	 * @return Query
	 */
	private function applyFilterCondition($filter, $query, $criteria = null)  {
		
		if(!isset($criteria)) {
			$criteria = $query;
		}
		
		$cls = $this->entityClass();
		if(isset($filter['conditions']) && isset($filter['operator'])) { // is FilterOperator
			
			foreach($filter['conditions'] as $condition) {
				$subCriteria = new Criteria();
				$this->applyFilterCondition($condition, $query, $subCriteria);
			
				if(!$subCriteria->hasConditions()) {
					continue;
				}
				
				switch(strtoupper($filter['operator'])) {
					case 'AND':
						$criteria->where($subCriteria);
						break;

					case 'OR':
						$criteria->orWhere($subCriteria);
						break;

					case 'NOT':
						$criteria->andWhereNotOrNull($subCriteria);
						break;
				}
			}
			
		} else {	
			// is FilterCondition		
			$subCriteria = new Criteria();			
			
			if(!$this->permissionLevelFoundInFilters) {
				$this->permissionLevelFoundInFilters = !empty($filter['permissionLevel']);			
			}
			
			$cls::filter($query, $subCriteria, $filter);			
			
			if($subCriteria->hasConditions()) {
				$criteria->andWhere($subCriteria);	
			}
		}
	}
	
	/**
	 * Takes the request arguments, validates them and fills it with defaults.
	 * 
	 * @param array $params
	 * @return array
	 * @throws InvalidArguments
	 */
	protected function paramsQuery(array $params) {
		if(!isset($params['limit'])) {
			$params['limit'] = 0;
		}		

		if ($params['limit'] < 0) {
			throw new InvalidArguments("Limit MUST be positive");
		}
		//cap at max of 50
		//$params['limit'] = min([$params['limit'], Capabilities::get()->maxObjectsInGet]);
		
		if(!isset($params['position'])) {
			$params['position'] = 0;
		}

		if ($params['position'] < 0) {
			throw new InvalidArguments("Position MUST be positive");
		}
		
		if(!isset($params['sort'])) {
			$params['sort'] = [];
		} else
		{
			if(!is_array($params['sort'])) {
				throw new InvalidArguments("Parameter 'sort' must be an array");
			}
		}
		
		if(!isset($params['filter'])) {
			$params['filter'] = [];
		} else
		{
			if(!is_array($params['filter'])) {
				throw new InvalidArguments("Parameter 'filter' must be an array");
			}
		}
		
		if(!isset($params['accountId'])) {
			$params['accountId'] = null;
		}
		
		$params['calculateTotal'] = !empty($params['calculateTotal']) ? true : false;
		
		return $params;
	}

	/**
	 * Handles the Foo entity's  "getFooList" command
	 * 
	 * @param array $params
	 */
	protected function defaultQuery($params) {
		
		$p = $this->paramsQuery($params);
		$idsQuery = $this->getQueryQuery($p);
		$idsQuery->fetchMode(PDO::FETCH_NUM);
		
		$state = $this->getState();
		
		$ids = [];		
			
		foreach($idsQuery as $record) {
			if(!isset($count)) {
				$count = count($record);
			}
			$ids[] = $count ? $record[0] : implode('-', $record);
		}
	

		$response = [
				'accountId' => $p['accountId'],
				'state' => $state,
				'ids' => $ids,
				'notfound' => [],
				'canCalculateUpdates' => false
		];
		
		if($p['calculateTotal']) {
			// $totalQuery = clone $idsQuery;
			// $response['total'] = $totalQuery
			// 								->selectSingleValue("count(distinct " . $totalQuery->getTableAlias() . ".id)")
			// 								->orderBy([], false)
			// 								->groupBy([])
			// 								->limit(1)
			// 								->offset(0)
			// 								->single();

			$response['total'] = go()->getDbConnection()->query("SELECT FOUND_ROWS()")->fetch(PDO::FETCH_COLUMN, 0);
		}
		
		return $response;
	}
	
	protected function getState() {
		$cls = $this->entityClass();
		
		//entities that don't support syncing can be listed and fetched with the read only controller
		return $cls::getState();
	}

	/**
	 * Transforms ['name ASC'] into: ['name' => 'ASC']
	 * 
	 * @param string[] $sort
	 * @return array[]
	 */
	protected function transformSort($sort) {		
		if(empty($sort)) {
			return [];
		}
		
		$transformed = [];

		foreach ($sort as $s) {
			if(is_array($s) && isset($s['property'])) {
				$transformed[$s['property']] = (isset($s['isAscending']) && $s['isAscending']===false) ? 'DESC' : 'ASC';
			} else { // for backward compatibility
				$parts = explode(' ', $s);
				$transformed[$parts[0]] = $parts[1] ?? 'ASC';
			}
		}
		
		return $transformed;		
	}
	
	

	/**
	 * 
	 * @param string $id
	 * @return boolean|Entity
	 */
	protected function getEntity($id, array $properties = []) {
		$cls = $this->entityClass();

		$entity = $cls::findById($id, $properties);

		if(!$entity){
			return false;
		}
		
		if (isset($entity->deletedAt)) {
			return false;
		}
		
		if(!$entity->hasPermissionLevel(Acl::LEVEL_READ)) {
//			throw new Forbidden();
			
			App::get()->debug("Forbidden: ".$cls.": ".$id);
							
			return false; //not found
		}

		return $entity;
	}

	
	/**
	 * Takes the request arguments, validates them and fills it with defaults.
	 * 
	 * @param array $params
	 * @return array
	 * @throws InvalidArguments
	 */
	protected function paramsGet(array $params) {
		if(isset($params['ids']) && !is_array($params['ids'])) {
			throw new InvalidArguments("ids must be of type array");
		}
		
//		if(isset($params['ids']) && count($params['ids']) > Capabilities::get()->maxObjectsInGet) {
//			throw new InvalidArguments("You can't get more than " . Capabilities::get()->maxObjectsInGet . " objects");
//		}
		
		if(!isset($params['properties'])) {
			$params['properties'] = [];
		}
		
		if(!isset($params['accountId'])) {
			$params['accountId'] = [];
		}
		
		return $params;
	}
	
	/**
	 * Override to add more query options for the "get" method.
	 * @return Query
	 */
	protected function getGetQuery($params) {
		$cls = $this->entityClass();
		
		if(!isset($params['ids'])) {
			$query = $cls::find($params['properties'], true);
		} else
		{
			$query = $cls::findByIds($params['ids'], $params['properties'], true);
		}
		
		//filter permissions
		$cls::applyAclToQuery($query, Acl::LEVEL_READ);
		
		return $query;	
	}

	
	/**
	 * Handles the Foo entity's getFoo command
	 * 
	 * @param array $params
	 */
	protected function defaultGet($params) {
		
		$p = $this->paramsGet($params);

		$result = [
				'accountId' => $p['accountId'],
				'state' => $this->getState(),
				'list' => [],
				'notFound' => []
		];
		
		//empty array should return empty result. but ids == null should return all.
		if(isset($p['ids']) && !count($p['ids'])) {
			return $result;
		}
		go()->getDebugger()->debugTiming('before query');
		$query = $this->getGetQuery($p);		

		go()->getDebugger()->debugTiming('after query');
			
		$foundIds = [];
		$result['list'] = [];
		$unsorted = [];
		foreach($query as $e) {
			$arr = $e->toArray();
			$arr['id'] = $e->id();
			$unsorted[$arr['id']] = $arr; 
			$foundIds[] = $arr['id'];

			go()->getDebugger()->debugTiming('item to array');
		}

		
		$result['notFound'] = [];
		if(isset($p['ids'])) {
			
			foreach($p['ids'] as $id) {
				if(isset($unsorted[$id])) {
					$result['list'][] = $unsorted[$id];
				}else{
					$result['notFound'][] = $id;
				}
			}
		} else {
			$result['list'] = array_values($unsorted);
		}


		return $result;
	}
	
	/**
	 * Takes the request arguments, validates them and fills it with defaults.
	 * 
	 * @param array $params
	 * @return array
	 * @throws InvalidArguments
	 */
	protected function paramsSet(array $params) {
		if(!isset($params['accountId'])) {
			$params['accountId'] = null;
		}
		
		if(!isset($params['create']) && !isset($params['update']) && !isset($params['destroy'])) {
			throw new InvalidArguments("You must pass one of these arguments: create, update or destroy");
		}
		
		if(!isset($params['create'])) {
			$params['create'] = [];
		}
		
		if(!isset($params['update'])) {
			$params['update'] = [];
		}
		
		if(!isset($params['destroy'])) {
			$params['destroy'] = [];
		}
		
		
		if(count($params['create']) + count($params['update'])  + count($params['destroy']) > Capabilities::get()->maxObjectsInSet) {
			throw new InvalidArguments("You can't set more than " . Capabilities::get()->maxObjectsInGet . " objects");
		}
		
		return $params;
	}


	/**
	 * When doing a set we update and create models. But sometimes the models itself create or update other models. When this happen
	 * we must also return those in the client or it won't sync them because the chage occurred in the same modseq.
	 */
	private function trackSaves() {
		$cls = $this->entityClass();
		$cls::on(Entity::EVENT_SAVE, static::class, 'onEntitySave');		
	}

	public static $createdEntitities = [];
	public static $updatedEntitities = [];

	public static function onEntitySave(Entity $entity) {

		$mod = array_map(function($mod) { return $mod[0];}, $entity->getModified()); //Get only modified values
		if($entity->isNew()) {
			static::$createdEntitities[$entity->id()] = $mod;
		} else {
			static::$updatedEntitities[$entity->id()] = $mod;
		}
	}


	/**
	 * Put all modified entities tracked by trackSave into the result array
	 */
	private function mergeOtherSaves(&$result) {

		//build a list of ID's of entities that were created/ updated in the set requests. We can filter them out to avoid duplicates in the response.
		$setIds = [];
		if(isset($result['updated'])) {
			$setIds = array_keys($result['updated']);
		}
		if(isset($result['created'])) {
			$setIds = array_merge(array_map(function($mod) {return $mod['id'];}, $result['created']));
		}

		if(count(static::$updatedEntitities) > 1) {						
			static::$updatedEntitities = array_filter(static::$updatedEntitities, function($id) use($setIds) {
				return !in_array($id, $setIds);
			}, ARRAY_FILTER_USE_KEY);

			$result['updated'] = isset($result['updated']) ? array_replace($result['updated'], static::$updatedEntitities) : static::$updatedEntitities;
		}
		if(count(static::$createdEntitities) > 1) {

			static::$createdEntitities = array_filter(static::$createdEntitities, function($id) use($setIds) {
				return !in_array($id, $setIds);
			}, ARRAY_FILTER_USE_KEY);

			$result['created'] = isset($result['created']) ? array_replace($result['created'], static::$createdEntitities) : static::$createdEntitities;
		}
	}

	/**
	 * Handles the Foo entity setFoos command
	 * 
	 * @param array $params
	 * @throws StateMismatch
	 */
	protected function defaultSet($params) {

		$this->trackSaves();

		$p = $this->paramsSet($params);

		$oldState = $this->getState();

		if (isset($p['ifInState']) && $p['ifInState'] != $oldState) {
			throw new StateMismatch();
		}

		$result = [
				'accountId' => $p['accountId'],
				'created' => null,
				'updated' => null,
				'destroyed' => null,
				'notCreated' => null,
				'notUpdated' => null,
				'notDestroyed' => null,
		];

		$this->createEntitites($p['create'], $result);
		$this->updateEntities($p['update'], $result);
		$this->destroyEntities($p['destroy'], $result);

		$this->mergeOtherSaves($result);

		$result['oldState'] = $oldState;
		$result['newState'] = $this->getState();

		return $result;
	}

	private function createEntitites($create, &$result) {
		foreach ($create as $clientId => $properties) {

			$entity = $this->create($properties);
			
			if(!$this->canCreate($entity)) {
				$result['notCreated'][$clientId] = new SetError("forbidden", go()->t("Permission denied"));
				continue;
			}

			if ($entity->save()) {
				$entityProps = new ArrayObject($entity->toArray());
				$diff = $entityProps->diff($properties);
				$diff['id'] = $entity->id();
				
				$result['created'][$clientId] = empty($diff) ? null : $diff;
			} else {				
				$result['notCreated'][$clientId] = new SetError("invalidProperties");
				$result['notCreated'][$clientId]->properties = array_keys($entity->getValidationErrors());
				$result['notCreated'][$clientId]->validationErrors = $entity->getValidationErrors();
			}
		}
	}
	
	/**
	 * Override this if you want to implement permissions for creating entities
	 * 
	 * @return boolean
	 */
	protected function canCreate(Entity $entity) {		
		return $entity->hasPermissionLevel(Acl::LEVEL_CREATE);
	}
	
	/**
	 * @todo Check permissions
	 * 
	 * @param array $properties
	 * @return Entity
	 */
	protected function create(array $properties) {
		
		$cls = $this->entityClass();

		$entity = new $cls;
		$entity->setValues($properties); 
		
		return $entity;
	}

	/**
	 * Override this if you want to change the default permissions for updating an entity.
	 * 
	 * @param Entity $entity
	 * @return bool
	 */
	protected function canUpdate(Entity $entity) {
		return $entity->hasPermissionLevel(Acl::LEVEL_WRITE);
	}

	/**
	 * 
	 * @param type $update
	 * @param type $result
	 */
	private function updateEntities($update, &$result) {
		foreach ($update as $id => $properties) {
			$entity = $this->getEntity($id);			
			if (!$entity) {
				$result['notUpdated'][$id] = new SetError('notFound', go()->t("Item not found"));
				continue;
			}
			
			//create snapshot of props client should be aware of
			$clientProps = array_merge($entity->toArray(), $properties);
			
			//apply new values before canUpdate so this function can check for modified properties too.
			$entity->setValues($properties);
			
			
			if(!$this->canUpdate($entity)) {
				$result['notUpdated'][$id] = new SetError("forbidden", go()->t("Permission denied"));
				continue;
			}
			
			if (!$entity->save()) {				
				$result['notUpdated'][$id] = new SetError("invalidProperties");				
				$result['notUpdated'][$id]->properties = array_keys($entity->getValidationErrors());
				$result['notUpdated'][$id]->validationErrors = $entity->getValidationErrors();				
				continue;
			}
			
			//The server must return all properties that were changed during a create or update operation for the JMAP spec
			$entityProps = new ArrayObject($entity->toArray());			
			$diff = $entityProps->diff($clientProps);
			
			$result['updated'][$id] = empty($diff) ? null : $diff;
		}
	}
	
	protected function canDestroy(Entity $entity) {
		return $entity->hasPermissionLevel(Acl::LEVEL_DELETE);
	}

	private function destroyEntities($destroy, &$result) {

		$doDestroy = [];
		foreach ($destroy as $id) {
			$entity = $this->getEntity($id);
			if (!$entity) {
				$result['notDestroyed'][$id] = new SetError('notFound', go()->t("Item not found"));
				continue;
			}
			
			if(!$this->canDestroy($entity)) {
				$result['notDestroyed'][$id] = new SetError("forbidden", go()->t("Permission denied"));
				continue;
			}

			$doDestroy[] = $id;
		}
		$cls = $this->entityClass();

		if(!empty($doDestroy)) {
			$query = new Query();
			foreach($doDestroy as $id) {
				$query->orWhere($cls::parseId($id));
			}
			$success = $cls::delete($query);
		} else {
			$success = true;
		}
			
		if ($success) {
			$result['destroyed'] = $doDestroy;
		} else {
			throw Exception("Delete error");
		}
	}
	
	/**
	 * Takes the request arguments, validates them and fills it with defaults.
	 * 
	 * @param array $params
	 * @return array
	 * @throws InvalidArguments
	 */
	protected function paramsGetUpdates(array $params) {
		
		if(!isset($params['maxChanges'])) {
			$params['maxChanges'] = Capabilities::get()->maxObjectsInGet;
		}
		
		if ($params['maxChanges'] < 1 || $params['maxChanges'] > Capabilities::get()->maxObjectsInGet) {
			throw new InvalidArguments("maxChanges should be greater than 0 and smaller than 50");
		}
		
		if(!isset($params['sinceState'])) {
			throw new InvalidArguments('sinceState is required');
		}
		
		if(!isset($params['accountId'])) {
			$params['accountId'] = null;
		}
		
		return $params;
		
	}


	/**
	 * Handles the Foo entity's getFooUpdates command
	 * 
	 * @param array $params
	 * @throws CannotCalculateChanges
	 */
	protected function defaultChanges($params) {						
		$p = $this->paramsGetUpdates($params);	
		$cls = $this->entityClass();		
		
		try {
			$result = $cls::getChanges($p['sinceState'], $p['maxChanges']);		
		} catch (CannotCalculateChanges $e) {
			$result["message"] = $e->getMessage();
			go()->warn($e->getMessage());
		}
		
		$result['accountId'] = $p['accountId'];

		return $result;
	}	
	
	protected function paramsExport($params){
		
		if(!isset($params['contentType'])) {
			throw new InvalidArguments("'contentType' parameter is required");
		}
		
		return $this->paramsGet($params);
	}
	
	protected function paramsImport($params){		
		
		if(!isset($params['blobId'])) {
			throw new InvalidArguments("'blobId' parameter is required");
		}
		
		if(!isset($params['values'])) {
			$params['values'] = [];
		}
		
		return $params;
	}
	
	/**
	 * Default handler for Foo/import method
	 * 
	 * @param type $params
	 * @return type
	 * @throws Exception
	 */
	protected function defaultImport($params) {

		ini_set('max_execution_time', 10 * 60);
		
		$params = $this->paramsImport($params);
		
		$blob = Blob::findById($params['blobId']);	
		
		$converter = $this->findConverter($blob->type);
		
		$response = $converter->importFile($blob->getFile(), $this->entityClass(), $params);
		
		if(!$response) {
			throw new \Exception("Invalid response from import convertor");
		}
		
		return $response;
	}
	
	/**
	 * Default handler for Foo/importCSVMapping method
	 * 
	 * @param type $params
	 * @return type
	 * @throws Exception
	 */
	protected function defaultImportCSVMapping($params) {
		
		$blob = Blob::findById($params['blobId']);	
		
		$converter = $this->findConverter($blob->type);
		
		$response['goHeaders'] = $converter->getHeaders($this->entityClass());
		$response['csvHeaders'] = $converter->getCsvHeaders($blob->getFile());
		
		if(!$response) {
			throw new \Exception("Invalid response from import convertor");
		}
		
		return $response;
	}
	
	/**
	 * 
	 * 
	 * @return AbstractConverter
	 * @throws InvalidArguments
	 */
	private function findConverter($contentType) {
		
		$cls = $this->entityClass();		
		$map = $cls::converters();
		
		if(!isset($map[$contentType])) {
			throw new InvalidArguments("Converter for file type '" . $contentType .'" is not found');		
		}
		
		return new $map[$contentType];		
	}
	
	/**
	 * Standard export function
	 * 
	 * You can use Foo/query first and then pass the ids of that result to 
	 * Foo/export().
	 * 
	 * @see AbstractConverter
	 * 
	 * @param array $params Identical to Foo/get. Additionally you MUST pass a 'contentType'. It will find the converter class using the Entity::converter() method.
	 * @throws InvalidArguments
	 * @throws Exception
	 */
	protected function defaultExport($params) {

		ini_set('max_execution_time', 10 * 60);
		
		$params = $this->paramsExport($params);
		
		$convertor = $this->findConverter($params['contentType']);
				
		$entities = $this->getGetQuery($params);
		
		$cls = $this->entityClass();
		$name = $cls::entityType()->getName();
		
		$blob = $convertor->exportToBlob($name, $entities);
		
		return ['blobId' => $blob->id];		
	}

	/**
	 * Merge entities into one
	 * 
	 * The first ID in the list will be kept after the merge.
	 */
	protected function defaultMerge($params) {
		if(empty($params['ids'])) {
			throw new InvalidArguments('ids is required');
		}

		if(count($params['ids']) < 2) {
			throw new InvalidArguments('At least 2 id\'s are required');

		}
		$primaryId = array_shift($params['ids']);

		$cls = $this->entityClass();

		$entity = $cls::findById($primaryId);

		if(!$this->canUpdate($entity)) {
			throw new Forbidden();
		}

		$oldState = $this->getState();

		go()->getDbConnection()->beginTransaction();
		foreach($params['ids'] as $id) {
			$other = $cls::findById($id);
			if(!$this->canDestroy($other)) {
				throw new Forbidden();
			}
			if(!$entity->merge($other)) {
				throw new \Exception("Failed to merge ID: ".$id . ", Validation errors: ". var_export($entity->getValidationErrors(), true));
			}
		}

		go()->getDbConnection()->commit();

		return [
			"updated" => [$primaryId],
			"destroyed" => $params['ids'],
			'oldState' => $oldState,
			'newState' => $this->getState()
		];
	}
}
