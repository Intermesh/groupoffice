<?php

namespace go\core\controller;

use go\core\jmap\Entity;
use go\core\jmap\EntityController;
use go\core\model;

/**
 * The controller for the Addressbook entity
 *
 * @copyright (c) 2018, Intermesh BV http://www.intermesh.nl
 * @author Merijn Schering <mschering@intermesh.nl>
 * @license http://www.gnu.org/licenses/agpl-3.0.html AGPLv3
 */
class SpreadSheetExport extends EntityController
{

	/**
	 * The class name of the entity this controller is for.
	 *
	 * @return string
	 */
	protected function entityClass(): string
	{
		return model\SpreadSheetExport::class;
	}

	/**
	 * Handles the Foo entity's Foo/query command
	 *
	 * @param array $params
	 * @see https://jmap.io/spec-core.html#/query
	 */
	public function query(array $params)
	{
		return $this->defaultQuery($params);
	}

	/**
	 * Handles the Foo entity's Foo/get command
	 *
	 * @param array $params
	 * @see https://jmap.io/spec-core.html#/get
	 */
	public function get(array $params)
	{
		return $this->defaultGet($params);
	}

	/**
	 * Handles the Foo entity's Foo/set command
	 *
	 * @see https://jmap.io/spec-core.html#/set
	 * @param array $params
	 */
	public function set(array $params)
	{
		return $this->defaultSet($params);
	}


	/**
	 * Handles the Foo entity's Foo/changes command
	 *
	 * @param array $params
	 * @see https://jmap.io/spec-core.html#/changes
	 */
	public function changes(array $params)
	{
		return $this->defaultChanges($params);
	}

	/**
	 * Enable user to delete their own export preset
	 *
	 * @param Entity $entity
	 * @return bool
	 */
	protected function canDestroy(Entity $entity): bool
	{
		return go()->getUserId() === $entity->userId || go()->getAuthState()->isAdmin();
	}

	/**
	 * Enable user to update their own export preset
	 *
	 * @param Entity $entity
	 * @return bool
	 */

	protected function canUpdate(Entity $entity): bool
	{
		return go()->getUserId() === $entity->userId || go()->getAuthState()->isAdmin();
	}
}

