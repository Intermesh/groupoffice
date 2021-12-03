<?php

namespace go\core\controller;

use go\core\jmap\Entity;
use go\core\jmap\exception\InvalidArguments;
use go\core\model;
use go\core\jmap\EntityController;



class Group extends EntityController {
	
	protected function canUpdate(Entity $entity): bool
	{
		
		return $this->rights->mayChangeGroups;
	}

	protected function canDestroy(Entity $entity): bool
	{
		return $this->rights->mayChangeGroups;
	}

	protected function canCreate(Entity $entity): bool
	{
		return $this->rights->mayChangeGroups;
	}

	/**
	 * The class name of the entity this controller is for.
	 * 
	 * @return string
	 */
	protected function entityClass(): string
	{
		return model\Group::class;
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
}
