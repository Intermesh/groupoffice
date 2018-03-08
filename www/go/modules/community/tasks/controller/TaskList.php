<?php

namespace go\modules\community\tasks\controller;

use go\core\jmap\EntityController;
use go\modules\community\tasks\model;


class TaskList extends EntityController {
	
	/**
	 * The class name of the entity this controller is for.
	 * 
	 * @return string
	 */
	protected function entityClass() {
		return model\TaskList::class;
	}	
}
