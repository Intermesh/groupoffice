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


	public function get($params) {

		//return Api::get(Thread::class, $json);
		$emails = model\Email::find()
			->select('id, threadId') //->groupBy('threadId')
			->andWhere('threadId', '=', $params['ids'] ?? [])
			->all();

		$threads = [];
		//$emailIds = [];
		foreach ($emails as $email) {
			if (!isset($threads[$email->threadId])) {
				$threads[$email->threadId] = ['id' => $email->threadId, 'emailIds' => []];
			}
			$threads[$email->threadId]['emailIds'][] = $email->id;
			//$emailIds[] = $email->id;
		}
		return [
			'state' => model\Thread::getState(),
			'list' => array_values($threads),
			'notFound' => null
		];
	}

	public function set($params) {
		return $this->defaultSet($params);
	}

}
