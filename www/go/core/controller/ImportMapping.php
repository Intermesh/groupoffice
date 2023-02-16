<?php

namespace go\core\controller;

use go\core\jmap\EntityController;
use go\core\model;

class ImportMapping extends EntityController {

	protected function entityClass(): string {
		return model\ImportMapping::class;
	}

	public function query($params) {
		return $this->defaultQuery($params);
	}

	public function get($params) {
		return $this->defaultGet($params);
	}

	public function set($params) {
		return $this->defaultSet($params);
	}

	public function changes($params) {
		return $this->defaultChanges($params);
	}
}