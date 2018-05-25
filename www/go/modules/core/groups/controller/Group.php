<?php

namespace go\modules\core\groups\controller;

use go\core\auth\model;
use go\core\jmap\EntityController;



class Group extends EntityController {
	
	/**
	 * The class name of the entity this controller is for.
	 * 
	 * @return string
	 */
	protected function entityClass() {
		return model\Group::class;
	}
}
