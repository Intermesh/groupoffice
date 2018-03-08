<?php

namespace go\modules\community\calendar\controller;

use go\core\jmap\EntityController;
use go\modules\community\calendar\model;


class Calendar extends EntityController {
	
	/**
	 * The class name of the entity this controller is for.
	 * 
	 * @return string
	 */
	protected function entityClass() {
		return model\Calendar::class;
	}	
}
