<?php

namespace go\core\jmap;

use go\core\acl\model\Acl;
use go\core\db\Query;
use go\core\jmap\exception\CannotCalculateChanges;
use go\core\jmap\exception\InvalidArguments;
use go\core\jmap\exception\StateMismatch;
use go\core\jmap\SetError;
use go\core\orm\Entity;
use PDO;

abstract class EntityController extends ReadOnlyEntityController {	
	
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
	 * Handles the Foo entity setFoos command
	 * 
	 * @param array $params
	 * @throws StateMismatch
	 */
	public function set($params) {
		
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

		$result['oldState'] = $oldState;
		$result['newState'] = $this->getState();

		Response::get()->addResponse($result);
	}

	private function createEntitites($create, &$result) {
		foreach ($create as $clientId => $properties) {
			
			if(!$this->canCreate()) {
				$result['notCreated'][$clientId] = new SetError("forbidden");
				continue;
			}
			
			$entity = $this->create($properties);

			if (!$entity->hasValidationErrors()) {
				$entityProps = new \go\core\util\ArrayObject($entity->toArray());
				$diff = $entityProps->diff($properties);
				$diff['id'] = $entity->getId();
				
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
	protected function canCreate() {
		$cls = $this->entityClass();
		return $cls::canCreate();
	}
	
	/**
	 * @todo Check permissions
	 * 
	 * @param array $properties
	 * @return \go\core\jmap\cls
	 */
	protected function create(array $properties) {
		
		$cls = $this->entityClass();

		$entity = new $cls;
		$entity->setValues($properties); 
		
		$entity->save();
		
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
				$result['notUpdated'][$id] = new SetError('notFound');
				continue;
			}
			
			//create snapshot of props client should be aware of
			$clientProps = array_merge($entity->toArray(), $properties);
			
			//apply new values before canUpdate so this function can check for modified properties too.
			$entity->setValues($properties);
			
			
			if(!$this->canUpdate($entity)) {
				$result['notUpdated'][$id] = new SetError("forbidden");
				continue;
			}
			
			if (!$this->update($entity, $properties)) {				
				$result['notUpdated'][$id] = new SetError("invalidProperties");				
				$result['notUpdated'][$id]->properties = array_keys($entity->getValidationErrors());
				$result['notUpdated'][$id]->validationErrors = $entity->getValidationErrors();				
				continue;
			}
			
			//The server must return all properties that were changed during a create or update operation for the JMAP spec
			$entityProps = new \go\core\util\ArrayObject($entity->toArray());			
			$diff = $entityProps->diff($clientProps);
			
			$result['updated'][$id] = empty($diff) ? null : $diff;
		}
	}
	
	
	
	protected function update(Entity $entity, array $properties) {		
		$entity->save();		
		return !$entity->hasValidationErrors();
	}
	
	protected function canDestroy(Entity $entity) {
		return $entity->hasPermissionLevel(Acl::LEVEL_DELETE);
	}

	private function destroyEntities($destroy, &$result) {
		foreach ($destroy as $id) {
			$entity = $this->getEntity($id);
			if (!$entity) {
				$result['notDestroyed'][$id] = new SetError('notFound');
				continue;
			}
			
			if(!$this->canDestroy($entity)) {
				$result['notDestroyed'][$id] = new SetError("forbidden");
				continue;
			}

			$success = $entity->delete();
			
			if ($success) {
				$result['destroyed'][] = $id;
			} else {
				$result['notDestroyed'][] = $entity->getValidationErrors();
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
	public function getUpdates($params) {		
		$p = $this->paramsGetUpdates($params);	
		$cls = $this->entityClass();		
		$result = $cls::getChanges($p['sinceState'], $p['maxChanges']);		
		$result['accountId'] = $p['accountId'];

		Response::get()->addResponse($result);
	}
	
	
	protected function paramsExport($params){
		
		if(!isset($params['converter'])) {
			throw new InvalidArguments("'converter' parameter is required");
		}
		
		return $this->paramsGet($params);
	}
	
	protected function paramsImport($params){
		
		if(!isset($params['converter'])) {
			throw new InvalidArguments("'converter' parameter is required");
		}
		
		if(!isset($params['blobId'])) {
			throw new InvalidArguments("'blobId' parameter is required");
		}
		
		if(!isset($params['values'])) {
			$params['values'] = [];
		}
		
		return $params;
	}
	
	
	public function import($params) {
		$params = $this->paramsImport($params);
		
		$convertor = $this->findConverter($params['converter']);
		
		$response = $convertor->importBlob($params['blobId'], $params['values']);
		
		\go\core\jmap\Response::get()->addResponse($response);
	}
	
	/**
	 * 
	 * 
	 * @return \go\core\data\convert\AbstractConverter
	 * @throws InvalidArguments
	 */
	private function findConverter($converter) {
		$cls = $this->entityClass();
		$module = $cls::getType()->getModule();
		
		//check in module
		$converterCls = "go\\modules\\" . $module->package . "\\" . $module->name . "\\convert\\" . $converter;
		if(!class_exists($converterCls)) {
			$converterCls = "go\\core\\data\\convert\\" . $converter;
			if(!class_exists($converterCls)) {
				throw new InvalidArguments("Convertor '" . $converter .'" is not found');
			}
		}
		
		$converter = new $converterCls;
		return $converter;
	}
	
	
	/**
	 * Standard export function
	 * 
	 * You can use Foo/query first and then pass the ids of that result to 
	 * Foo/export().
	 * 
	 * @see \go\core\data\convert\AbstractConverter
	 * 
	 * @param array $params Identical to Foo/get. Additionally you MUST pass a 'converter' It will look for the AbstractConverter object in the "convert" folder of the module.
	 * @throws InvalidArguments
	 * @throws \Exception
	 */
	public function export($params) {
		
		$params = $this->paramsExport($params);
		
		$convertor = $this->findConverter($params['converter']);
				
		$entities = $this->getGetQuery($params);
		
		$blob = $convertor->exportToBlob($entities);
		
		Response::get()->addResponse(['blobId' => $blob->id]);
		
	}

}
