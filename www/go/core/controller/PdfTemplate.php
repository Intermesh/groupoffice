<?php

namespace go\core\controller;

use go\core\jmap\Entity;
use go\core\jmap\EntityController;
use go\core\jmap\exception\InvalidArguments;
use go\core\jmap\exception\StateMismatch;
use go\core\model;

class PdfTemplate extends EntityController {

  /**
   * @return Entity
   */
	protected function entityClass() {
		return model\PdfTemplate::class;
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
   * @throws StateMismatch
   * @throws InvalidArguments
   */
	public function set($params) {
		return $this->defaultSet($params);
	}


  /**
   * Handles the Foo entity's Foo/changes command
   *
   * @param array $params
   * @return mixed
   * @throws InvalidArguments
   * @see https://jmap.io/spec-core.html#/changes
   */
	public function changes($params) {
		return $this->defaultChanges($params);
	}
}
