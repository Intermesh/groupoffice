<?php

namespace go\core\controller;

use GO;
use go\core\exception\Forbidden;
use go\core\jmap\EntityController;
use go\core\jmap\exception\InvalidArguments;
use go\core\jmap\Response;
use go\core\jmap\Entity;
use go\core\model;



class UserDisplay extends EntityController {

	/**
	 * The class name of the entity this controller is for.
	 * 
	 * @return string
	 */
	protected function entityClass() {
		return model\UserDisplay::class;
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
