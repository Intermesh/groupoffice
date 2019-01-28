<?php

namespace go\modules\community\comments\controller;

use go\core\jmap\EntityController;
use go\modules\community\comments\model;


class Comment extends EntityController {
	
	/**
	 * The class name of the entity this controller is for.
	 * 
	 * @return string
	 */
	protected function entityClass() {
		return model\Comment::class;
	}
}