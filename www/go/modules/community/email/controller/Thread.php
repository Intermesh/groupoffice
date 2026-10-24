<?php

namespace go\modules\community\email\controller;

use go\core\jmap\EntityController;
use go\modules\community\email\model;


class Thread extends EntityController {
	
	/**
	 * The class name of the entity this controller is for.
	 * 
	 * @return string
	 */
	protected function entityClass(): string
	{
		return model\Thread::class;
	}	


	/**
	 * Thread/get: emailIds sorted by receivedAt (RFC 8621 section 3.1)
	 */
	public function get($params) {
		$ids = array_map('intval', $params['ids'] ?? []);

		$query = model\Email::find()
			->select('e.id, e.threadId')
			->andWhere('e.threadId', 'IN', $ids)
			->orderBy(['e.receivedAt' => 'ASC', 'e.id' => 'ASC']);
		if (!empty($params['accountId'])) {
			$query->andWhere('e.accountId', '=', $params['accountId']);
		}
		model\Email::applyAclToQuery($query);

		$threads = [];
		foreach ($query->fetchMode(\PDO::FETCH_ASSOC)->all() as $email) {
			$threads[$email['threadId']] ??= ['id' => (int)$email['threadId'], 'emailIds' => []];
			$threads[$email['threadId']]['emailIds'][] = (int)$email['id'];
		}
		$notFound = array_values(array_diff($ids, array_keys($threads)));

		return [
			'accountId' => $params['accountId'] ?? null,
			'state' => model\Thread::getState(),
			'list' => array_values($threads),
			'notFound' => $notFound
		];
	}

	public function changes($params) {
		return $this->defaultChanges($params);
	}

}
