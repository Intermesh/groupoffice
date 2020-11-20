<?php

namespace go\core\controller;

use GO;
use go\core\exception\Forbidden;
use go\core\jmap\EntityController;
use go\core\jmap\exception\InvalidArguments;
use go\core\jmap\Response;
use go\core\jmap\Entity;
use go\core\model;



class User extends EntityController {

	protected function getDefaultQueryFilter()
	{
		return ['showDisabled'=> false];
	}

	protected function canUpdate(Entity $entity) {
		
		if(!go()->getAuthState()->isAdmin()) {
			if($entity->isModified('groups')) {
				return false;
			}
		}
		
		return parent::canUpdate($entity);
	}
	
	/**
	 * The class name of the entity this controller is for.
	 * 
	 * @return string
	 */
	protected function entityClass() {
		return model\User::class;
	}
	
	public function loginAs($params) {
		
		if(!isset($params['userId'])) {
			throw new InvalidArguments("Missing parameter userId");
		}
		
		if(!go()->getAuthState()->isAdmin()) {
			throw new Forbidden();
		}
		
		$user = model\User::findById($params['userId']);
		
		if(!$user->enabled) {
			throw new Exception("This user is disabled");
		}
		
		$success = go()->getAuthState()->changeUser($params['userId']);
		
		Response::get()->addResponse(['success' => $success]);
	}

  /**
   * Handles the Foo entity's Foo/query command
   *
   * @param array $params
   * @return array
   * @throws InvalidArguments
   * @see https://jmap.io/spec-core.html#/query
   */
	public function query($params) {
		return $this->defaultQuery($params);
	}

  /**
   * Handles the Foo entity's Foo/get command
   *
   * @param array $params
   * @return array
   * @throws InvalidArguments
   * @see https://jmap.io/spec-core.html#/get
   */
	public function get($params) {
		return $this->defaultGet($params);
	}

  /**
   * Handles the Foo entity's Foo/set command
   *
   * @see https://jmap.io/spec-core.html#/set
   * @param array $params
   * @return array
   * @throws InvalidArguments
   * @throws \go\core\jmap\exception\StateMismatch
   */
	public function set($params) {
		return $this->defaultSet($params);
	}


  /**
   * Handles the Foo entity's Foo/changes command
   *
   * @param array $params
   * @return array
   * @throws InvalidArguments
   * @see https://jmap.io/spec-core.html#/changes
   */
	public function changes($params) {
		return $this->defaultChanges($params);
	}

	public function export($params) {
		return $this->defaultExport($params);
	}

	public function import($params) {
		return $this->defaultImport($params);
	}

	public function importCsvMapping($params) {
		return $this->defaultImportCSVMapping($params);
	}
	/**
	 * @param $params
	 * @return array
	 */
	public function exportColumns($params) {
		return $this->defaultExportColumns($params);
	}
}
