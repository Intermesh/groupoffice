<?php

namespace go\core\controller;

use go\core\jmap\EntityController;
use go\core\model;
use go\core\util\ArrayObject;

class AppPassword extends EntityController
{

	/**
	 * @inheritDoc
	 */
	protected function entityClass(): string
	{
		return model\AppPassword::class;
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
}