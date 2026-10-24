<?php

namespace go\modules\community\email\controller;

use go\core\jmap\EntityController;
use go\core\model\Acl;
use go\modules\community\email\model;


class Mailbox extends EntityController {
	
	/**
	 * The class name of the entity this controller is for.
	 * 
	 * @return string
	 */
	protected function entityClass(): string
	{
		return model\Mailbox::class;
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
		// the client's generic sync calls this without an account; only pull from IMAP for a readable account
		if(!empty($params['accountId'])) {
			$account = model\EmailAccount::findById($params['accountId']);
			if($account && $account->getPermissionLevel() >= Acl::LEVEL_READ) {
				$backend = $account->connect();
				if ($backend) {
					$backend->fetchChanges();
				}
			}
		}
		return $this->defaultChanges($params);
	}

}
