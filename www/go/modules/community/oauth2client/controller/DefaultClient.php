<?php

namespace go\modules\community\oauth2client\controller;

use go\core\jmap\EntityController;
use go\modules\community\oauth2client\model;

final class DefaultClient extends EntityController
{

	protected function entityClass() {
		return model\DefaultClient::class;
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
