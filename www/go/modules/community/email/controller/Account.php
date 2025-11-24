<?php

namespace go\modules\community\email\controller;

use go\core\jmap\EntityController;
use go\core\util\ArrayObject;
use go\modules\community\email\model;


class Account extends EntityController
{

	/**
	 * The class name of the entity this controller is for.
	 *
	 * @return string
	 */
	protected function entityClass(): string
	{
		return model\Account::class;
	}

	/**
	 * Handles the Foo entity's Foo/query command
	 *
	 * @param array $params
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
	 */
	public function set(array $params): ArrayObject
	{
		return $this->defaultSet($params);
	}


	/**
	 * Handles the Foo entity's Foo/changes command
	 *
	 * @param array $params
	 * @see https://jmap.io/spec-core.html#/changes
	 */
	public function changes(array $params): ArrayObject
	{
		return $this->defaultChanges($params);
	}
}
