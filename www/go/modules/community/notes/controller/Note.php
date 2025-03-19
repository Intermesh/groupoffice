<?php

namespace go\modules\community\notes\controller;

use Exception;
use go\core\jmap\Entity;
use go\core\jmap\EntityController;
use go\core\jmap\exception\InvalidArguments;
use go\core\jmap\exception\StateMismatch;
use go\core\jmap\Response as ResponseAlias;
use go\core\util\ArrayObject;
use go\core\model\Acl;
use go\core\model\Permission;
use go\core\util\Crypt;
use go\modules\community\notes\model;


class Note extends EntityController {
	
	/**
	 * The class name of the entity this controller is for.
	 * 
	 * @return string
	 */
	protected function entityClass(): string
	{
		return model\Note::class;
	}

	/**
	 * @param array $params
	 * @throws Exception
	 */
	public function decrypt($params) {
//		$note = $this->getEntity($params['id']);
		
		$descrypted = Crypt::decrypt($params['data'], $params['password']);
		
		if(!$descrypted) {
			throw new Exception("Invalid password");
		}
		
		ResponseAlias::get()->addResponse([
				'content' => $descrypted
		]);
	}


	/**
	 * Handles the Foo entity's Foo/query command
	 *
	 * @param array $params
	 * @return ArrayObject
	 * @throws InvalidArguments
	 * @see https://jmap.io/spec-core.html#/query
	 */
	public function query(array $params): ArrayObject
	{
		return $this->defaultQuery($params);
	}

	/**
	 * Handles the Foo entity's Foo/get command
	 *
	 * @param array $params
	 * @return ArrayObject
	 * @throws Exception
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
	 * @param array $params
	 * @return ArrayObject
	 * @throws InvalidArguments
	 * @throws StateMismatch
	 */
	public function set(array $params): ArrayObject
	{
		return $this->defaultSet($params);
	}

	/**
	 * @param \go\modules\community\notes\model\Note $entity
	 * @return bool
	 */
	protected function canDestroy(Entity $entity): bool
	{
		if($entity->createdBy === go()->getUserId()) {
			return true; // Anyone should be able to remove their own notes, regardless of people allowed to change noteBOOKs
		}
		if(!$this->rights->mayChangeNoteBooks) {
			return false;
		}
		return parent::canDestroy($entity);
	}


	/**
	 * Handles the Foo entity's Foo/changes command
	 *
	 * @param array $params
	 * @return ArrayObject
	 * @throws InvalidArguments
	 * @see https://jmap.io/spec-core.html#/changes
	 */
	public function changes(array $params): ArrayObject
	{
		return $this->defaultChanges($params);
	}


	/**
	 * Handles export
	 *
	 * @param array $params
	 * @return ArrayObject
	 * @throws InvalidArguments
	 */
	public function export(array $params): ArrayObject
	{
		return $this->defaultExport($params);
	}

	
	/**
	 * Handles import
	 *
	 * @param array $params
	 * @return ArrayObject
	 * @throws Exception
	 */
	public function import(array $params): ArrayObject
	{
		return $this->defaultImport($params);
	}


	/**
	 * Returns a mapping object the client can use for importing data
	 *
	 * @param array $params
	 * @return ArrayObject
	 * @throws Exception
	 */
	public function importCSVMapping(array $params): ArrayObject
	{
		return $this->defaultImportCSVMapping($params);
	}

	/**
	 * Returns columns that can be exported
	 *
	 * @param array $params
	 * @return ArrayObject
	 */
	public function exportColumns(array $params): ArrayObject
	{
		return $this->defaultExportColumns($params);
	}
}
