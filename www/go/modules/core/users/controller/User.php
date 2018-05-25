<?php

namespace go\modules\core\users\controller;

use go\core\auth\model;
use go\core\jmap\EntityController;



class User extends EntityController {
	
	/**
	 * The class name of the entity this controller is for.
	 * 
	 * @return string
	 */
	protected function entityClass() {
		return model\User::class;
	}
}
