<?php

namespace go\modules\community\maildomains\controller;

use go\core\jmap\EntityController;
use go\core\jmap\exception\InvalidArguments;
use go\core\jmap\exception\StateMismatch;
use go\core\util\ArrayObject;
use go\modules\community\maildomains\model;

final class Alias extends EntityController
{
	protected function entityClass(): string
	{
		return model\Alias::class;
	}

	/**
	 * Handles the Domain entity's Domain/query command
	 *
	 * @param array $params
	 * @return ArrayObject
	 * @throws InvalidArguments
	 * @see https://jmap.io/spec-core.html#/query
	 */
	public function query($params): ArrayObject
	{
		return $this->defaultQuery($params);
	}

	/**
	 * Handles the Domain entity's Domain/get command
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
	 * Handles the Domain entity's Domain/set command
	 *
	 * @see https://jmap.io/spec-core.html#/set
	 * @param array $params
	 * @return ArrayObject
	 * @throws StateMismatch
	 * @throws InvalidArguments
	 */
	public function set($params): ArrayObject
	{
		return $this->defaultSet($params);
	}

	/**
	 * Handles the Domain entity's Domain/changes command
	 * @param array $params
	 * @return ArrayObject
	 * @throws InvalidArguments
	 * @see https://jmap.io/spec-core.html#/changes
	 */
	public function changes($params): ArrayObject
	{
		return $this->defaultChanges($params);
	}

	/**
	 * @param $params
	 * @return ArrayObject
	 * @throws InvalidArguments
	 */
	public function export($params): ArrayObject
	{
		return $this->defaultExport($params);
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
	public function importCSVMapping($params): ArrayObject
	{
		return $this->defaultImportCSVMapping($params);
	}

	/**
	 * @param $params
	 * @return ArrayObject
	 */
	public function exportColumns($params): ArrayObject
	{
		return $this->defaultExportColumns($params);
	}

}
