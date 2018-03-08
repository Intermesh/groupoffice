<?php

namespace go\modules\community\email\controller;

use go\core\jmap\EntityController;
use go\modules\community\email\model;


class Account extends EntityController {
	
	/**
	 * The class name of the entity this controller is for.
	 * 
	 * @return string
	 */
	protected function entityClass() {
		return model\Account::class;
	}	
}
