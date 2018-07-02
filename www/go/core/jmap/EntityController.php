<?php

namespace go\core\jmap;

use go\core\acl\model\Acl;
use go\core\App;
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
				$result['notCreated'][$id] = new SetError("forbidden");
				continue;
			}
			
			$entity = $this->create($properties);

			if (!$entity->hasValidationErrors()) {
				$result['created'][$clientId] = $this->diff($entity, $properties);
			} else {				
				$result['notCreated'][$clientId] = new SetError("invalidProperties");
				$result['notCreated'][$clientId]->properties = array_keys($entity->getValidationErrors());
				$result['notCreated'][$clientId]->validationErrors = $entity->getValidationErrors();
			}
		}
	}
	
	protected function canCreate() {
		return true;
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
	 * The server must return all properties that were changed during a create or update operation for the JMAP spec
	 * 
	 * @param \go\modules\community\notes\controller\notes\Note $entity
	 * @param type $properties
	 * @return type
	 */
	private function diff(Entity $entity, $properties) {

		$diff = [];
		
		$serverProps = $entity->toArray();
		
		foreach ($serverProps as $key => $value) {
			if (!isset($properties[$key]) || $properties[$key] !== $value) {
				$diff[$key] = $value;
			}
		}

		return empty($diff) ? null : $diff;
	}
	
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
			
			$result['updated'][$entity->id] = $this->diff($entity, $properties);
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
				$result['destroyed'][] = $entity->id; //todo map of properties changed during save
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
		
		//We might optimize this later but for now when there is a change in permissions we can't calculate changes.
		//Client must invalidate cache and refetch all required items.
		
		$cls = $this->entityClass();
		
		if(!($cls instanceof Entity)) {
			//not jmap entity so we can't calculate
			throw new CannotCalculateChanges();
		}
		
		$acls = $cls::findAcls();		
		if($acls && (Acl::findGrantedSince(App::get()->getAuthState()->getUserId(), $p['sinceState'], $acls)->limit(1)->execute()->fetch() ||
			Acl::findRevokedSince(App::get()->getAuthState()->getUserId(), $p['sinceState'], $acls)->limit(1)->execute()->fetch())) {
			throw new CannotCalculateChanges();
		}			

		$result = [
				'accountId' => $p['accountId'],
				'oldState' => $p['sinceState'],
				'newState' => null,
				'hasMoreUpdates' => false,
				'changed' => [],
				'removed' => []
		];

		$tables = $cls::getMapping()->getTables();
		$firstTable = array_shift($tables);

		$entities = $cls::find()
						->select([$firstTable->getAlias() . '.id', $firstTable->getAlias() . '.modSeq', $firstTable->getAlias() . '.deletedAt'])
						->fetchMode(PDO::FETCH_ASSOC)
						->limit($p['maxChanges'])
						->orderBy(['modSeq' => 'ASC'])
						->andWhere('modSeq', '>', $p['sinceState']);
		
		$cls::applyAclToQuery($entities);
		
		foreach ($entities as $entity) {
			if (isset($entity['deletedAt'])) {
				$result['removed'][] = $entity['id'];
			} else {
				$result['changed'][] = $entity['id'];
			}
		}

		if(isset($entity)){
			$result['newState'] = $entity['modSeq'];
		} else
		{
			$result['newState'] = $this->getState();
		}
		
		$result['hasMoreUpdates'] = $result['newState'] != $this->getState();

		Response::get()->addResponse($result);
	}

}
