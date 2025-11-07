<?php
namespace go\modules\community\ldapauthenticator\controller;

use go\core\controller;
use go\core\jmap\Response;
use go\core\util\ArrayObject;
use go\modules\community\ldapauthenticator\model;

class Server extends \go\core\jmap\EntityController {
	
	protected function entityClass(): string
	{
		return model\Server::class;
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
