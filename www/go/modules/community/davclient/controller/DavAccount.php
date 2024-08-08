<?php

namespace go\modules\community\davclient\controller;

use go\core\db\Query;
use go\core\jmap\Entity;
use go\core\jmap\EntityController;
use go\modules\community\calendar\model;


class DavAccount extends EntityController {

	protected function entityClass(): string {
		return model\DavAccount::class;
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

	protected function canCreate(Entity $entity): bool
	{
		return $this->rights->mayChangeAccount;
	}
}
