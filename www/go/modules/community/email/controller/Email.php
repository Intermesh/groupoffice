<?php

namespace go\modules\community\email\controller;

use go\core\http\Exception as HttpException;
use go\core\db\Query;
use go\core\jmap\EntityController;
use go\core\orm\Query as OrmQuery;
use go\core\util\ArrayObject;
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

	/**
	 * Email/query with collapseThreads (RFC 8621 section 4.4): only the newest email of each thread in the
	 * result. With a mailboxId filter the newest email of the thread in that mailbox is used.
	 */
	protected function getQueryQuery(ArrayObject $params): OrmQuery
	{
		$query = parent::getQueryQuery($params);
		if(empty($params['collapseThreads'])) {
			return $query;
		}

		$newer = (new Query)
			->select('1')
			->from('email_email', 'newer')
			->where('newer.threadId = e.threadId')
			->andWhere('(newer.receivedAt > e.receivedAt OR (newer.receivedAt = e.receivedAt AND newer.id > e.id))');

		$mailboxId = $params['filter']['mailboxId'] ?? $params['filter']['inMailboxes'] ?? null;
		if(isset($mailboxId)) {
			$newer->join('email_map', 'newerMap', 'newerMap.fk = newer.id')
				->andWhere('newerMap.mailboxId', 'IN', (array)$mailboxId);
		}

		return $query->andWhereExists($newer, true);
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
