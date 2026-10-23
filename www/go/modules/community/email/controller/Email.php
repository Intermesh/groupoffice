<?php

namespace go\modules\community\email\controller;

use go\core\http\Exception as HttpException;
use go\core\jmap\EntityController;
use go\core\model\Acl;
use go\modules\community\email\model;


class Email extends EntityController {
	
	/**
	 * The class name of the entity this controller is for.
	 * 
	 * @return string
	 */
	protected function entityClass(): string
	{
		return model\Email::class;
	}	

	public function query($params) {
		return $this->defaultQuery($params);
	}

	public function get($params) {
		if(!isset($params['properties'])) {
			$params['properties'] = ["id","accountId", "blobId", "threadId", "mailboxIds", "keywords", "size",
				"receivedAt", "messageId", "inReplyTo", "references", "sender", "from",
				"to", "cc", "bcc", "replyTo", "subject", "sentAt", "hasAttachment",
				"preview"];
		}
		return $this->defaultGet($params);
	}

	public function set($params) {
		// check account id.
		if(empty($params['accountId'])) {
			throw new \Exception("Missing accountId");
		}
		// fetch account object
		$account = model\EmailAccount::findById($params['accountId']);
		if(empty($account)) {
			throw new \Exception("Account not found");
		}
		// create, update and destroy all write to the mail server
		if($account->getPermissionLevel() < Acl::LEVEL_WRITE) {
			throw new HttpException(403, "You don't have write access to this account");
		}
		// connect to account dsn
		$backend = $account->connect();
		// perform create / update / destroy on account
		$result = $backend->setEmail((object)$params);

		// update local index
		return $result;
		//return $this->defaultSet($params);
	}

	public function changes($params) {
		return $this->defaultChanges($params);
	}

}
