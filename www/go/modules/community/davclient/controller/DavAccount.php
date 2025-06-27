<?php

namespace go\modules\community\davclient\controller;

use go\core\jmap\Entity;
use go\core\jmap\EntityController;
use go\modules\community\davclient\model;


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
		if(!empty($params['keepData'])) {
			model\DavAccount::$keepData = true;
		}
		return $this->defaultSet($params);
	}

	public function changes($params) {
		return $this->defaultChanges($params);
	}

	public function sync($params) {
		set_time_limit(600);
		$account = model\DavAccount::findById($params['accountId']);
		$success = false;
		$collection = null;
		if(!empty($account)) {
			if(isset($params['collectionId'])) {
				if(isset($account->collections[$params['collectionId']])) {
					$collection = $account->collections[$params['collectionId']];
					$success = $account->syncCollection($collection);
				}
			} else {
				$success = $account->sync(true); // full sync, incl homeset
			}
		}
		return ['accountId'=>$params['accountId'], 'collection'=>$collection, 'success' => $success];
	}

	protected function canCreate(Entity $entity): bool
	{
		return $this->rights->mayChangeAccount;
	}
}
