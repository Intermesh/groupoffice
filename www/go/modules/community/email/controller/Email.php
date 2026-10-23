<?php

namespace go\modules\community\email\controller;

use go\core\http\Exception as HttpException;
use go\core\jmap\EntityController;
use go\core\util\ArrayObject;
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
		if(empty($params['collapseThreads'])) {
			return $this->defaultQuery($params);
		}
		return $this->collapsedQuery($params);
	}

	/**
	 * Email/query with collapseThreads (RFC 8621 section 4.4.3): after filtering and sorting only the first
	 * email of each thread is kept, then position and limit are applied.
	 */
	private function collapsedQuery(array $params): ArrayObject
	{
		$state = $this->getState();
		$p = $this->paramsQuery($params);
		$position = (int)$p['position'];
		$limit = (int)$p['limit'];

		$p['position'] = 0;
		$p['limit'] = 0;
		$query = $this->getQueryQuery($p)
			->select('e.id, e.threadId')
			->fetchMode(\PDO::FETCH_NUM);

		$ids = [];
		$threads = [];
		foreach($query->all() as [$id, $threadId]) {
			if(!isset($threads[$threadId])) {
				$threads[$threadId] = true;
				$ids[] = (string)$id;
			}
		}

		$response = new ArrayObject([
			'accountId' => $p['accountId'],
			'state' => $state,
			'queryState' => $state,
			'collapseThreads' => true,
			'position' => $position,
			'ids' => array_slice($ids, $position, $limit ?: null),
			'notfound' => [],
			'canCalculateUpdates' => false
		]);
		if(!empty($p['calculateTotal'])) {
			$response['total'] = count($ids);
		}
		if(!empty($p['calculateHasMore'])) {
			$response['hasMore'] = $limit && count($ids) > $position + $limit;
		}
		return $response;
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
		// create, move and destroy need write access; changing keywords only needs mayFlagItems
		$right = self::isKeywordsOnly($params) ? 'mayFlagItems' : 'mayWriteItems';
		if(empty($account->getMyRights()[$right])) {
			throw new HttpException(403, "You don't have permission to change mail in this account");
		}
		// connect to account dsn
		$backend = $account->connect();
		// perform create / update / destroy on account
		$result = $backend->setEmail((object)$params);

		// update local index
		return $result;
		//return $this->defaultSet($params);
	}

	private static function isKeywordsOnly(array $params): bool
	{
		if(!empty($params['create']) || !empty($params['destroy'])) {
			return false;
		}
		foreach($params['update'] ?? [] as $patch) {
			foreach(array_keys((array)$patch) as $path) {
				if($path !== 'keywords' && !str_starts_with($path, 'keywords/')) {
					return false;
				}
			}
		}
		return true;
	}

	public function changes($params) {
		return $this->defaultChanges($params);
	}

}
