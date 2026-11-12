<?php

namespace go\modules\community\davclient\controller;

use go\core\exception\NotFound;
use go\core\jmap\Entity;
use go\core\jmap\EntityController;
use go\modules\community\davclient\model;


/**
 * @extends EntityController<\go\modules\community\davclient\model\DavAccount>
 */
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
		// getEntity() returns false when the user may not read the account
		$account = $this->getEntity($params['accountId']);
		if(empty($account)) {
			throw new NotFound();
		}
		set_time_limit(600);
		$success = false;
		$collection = null;
		if(isset($params['collectionId'])) {
			if(isset($account->collections[$params['collectionId']])) {
				$collection = $account->collections[$params['collectionId']];
				$success = $account->syncCollection($collection);
			}
		} else {
			$success = $account->sync(true); // full sync, incl homeset
		}
		return ['accountId'=>$params['accountId'], 'collection'=>$collection, 'success' => $success, 'error' => $account->lastError];
	}

	protected function canCreate(Entity $entity): bool
	{
		return $this->rights->mayChangeAccount;
	}

	/**
	 * Changing the host or credentials of an account requires the "Change account" permission, also for the owner
	 */
	protected function canUpdate(Entity $entity): bool
	{
		return $this->rights->mayChangeAccount && parent::canUpdate($entity);
	}

	protected function canDestroy(Entity $entity): bool
	{
		return $this->rights->mayChangeAccount && parent::canDestroy($entity);
	}
}
