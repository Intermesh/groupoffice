<?php

namespace go\modules\community\notes\controller;

use go\core\jmap\EntityController;
use go\modules\community\notes\model;


class Note extends EntityController {
	
	/**
	 * The class name of the entity this controller is for.
	 * 
	 * @return string
	 */
	protected function entityClass() {
		return model\Note::class;
	}
	
	public function decrypt($params) {
		$note = $this->getEntity($params['id']);
		
		$descrypted = \go\core\util\Crypt::decrypt($note->content, $params['password']);
		
		if(!$descrypted) {
			throw new \Exception("Invalid password");
		}
		
		\go\core\jmap\Response::get()->addResponse([
				'content' => $descrypted
		]);
	}
}
