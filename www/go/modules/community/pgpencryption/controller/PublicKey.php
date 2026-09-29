<?php

namespace go\modules\community\pgpencryption\controller;

use go\core\jmap\EntityController;
use go\core\jmap\exception\InvalidArguments;
use go\core\jmap\exception\StateMismatch;
use go\core\util\ArrayObject;
use go\modules\community\pgpencryption\model;

class PublicKey extends EntityController
{

	protected function entityClass(): string
	{
		return model\PublicKey::class;
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
	 * @param $params
	 * @return ArrayObject
	 * @throws Exception
	 */
	public function import($params): ArrayObject
	{
		return $this->defaultImport($params);
	}

	/**
	 * @param $params
	 * @return ArrayObject
	 * @throws Exception
	 */
	public function export($params): ArrayObject
	{
		return $this->defaultExport($params);
	}


}