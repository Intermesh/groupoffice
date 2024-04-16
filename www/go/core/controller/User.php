<?php

namespace go\core\controller;

use Exception;
use GO;
use go\core\exception\Forbidden;
use go\core\jmap\EntityController;
use go\core\jmap\exception\InvalidArguments;
use go\core\jmap\Response;
use go\core\jmap\Entity;
use go\core\model;
use go\core\util\ArrayObject;


class User extends EntityController {

	protected function getDefaultQueryFilter() : array
	{
		return ['showDisabled'=> false];
	}

	protected function canUpdate(Entity $entity): bool
	{
		
		if($this->rights->mayChangeUsers) {
			// Level is not used for users. When user management is enabled only check read permissions
			return $entity->hasPermissionLevel(model\Acl::LEVEL_READ);
		}

		if($entity->isModified('groups')) {
			return false;
		}

		
		return parent::canUpdate($entity);
	}

	protected function canDestroy(Entity $entity): bool
	{
		return go()->getAuthState()->isAdmin();
	}

	protected function canCreate(Entity $entity): bool
	{
		return go()->getAuthState()->isAdmin();
	}
	
	/**
	 * The class name of the entity this controller is for.
	 * 
	 * @return string
	 */
	protected function entityClass(): string
	{
		return model\User::class;
	}

	/**
	 * @throws InvalidArguments
	 * @throws Forbidden
	 */
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
   * @inheritDoc
   * @see https://jmap.io/spec-core.html#/query
   */
	public function query(array $params): ArrayObject
	{
		return $this->defaultQuery($params);
	}

  /**
   * Handles the Foo entity's Foo/get command
   *
   * @inheritDoc
   * @see https://jmap.io/spec-core.html#/get
   */
	public function get(array $params): ArrayObject
	{
		return $this->defaultGet($params);
	}

  /**
   * Handles the Foo entity's Foo/set command
   *
   * @see https://jmap.io/spec-core.html#/set
   * @inheritDoc
   */
	public function set(array $params): ArrayObject
	{
		return $this->defaultSet($params);
	}


  /**
   * Handles the Foo entity's Foo/changes command
   *
   * @inheritDoc
   * @see https://jmap.io/spec-core.html#/changes
   */
	public function changes(array $params): ArrayObject
	{
		return $this->defaultChanges($params);
	}

	public function export(array $params)
	{
		return $this->defaultExport($params);
	}

	public function import(array $params)
	{
		return $this->defaultImport($params);
	}

	public function importCsvMapping(array $params)
	{
		return $this->defaultImportCSVMapping($params);
	}
	/**
	 * @param array $params
	 * @return ArrayObject
	 */
	public function exportColumns(array $params)
	{
		return $this->defaultExportColumns($params);
	}
}
