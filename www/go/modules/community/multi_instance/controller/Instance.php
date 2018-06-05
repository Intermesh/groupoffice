<?php

namespace go\modules\community\multi_instance\controller;

use go\core\jmap\EntityController;
use go\modules\community\multi_instance\model;


class Instance extends EntityController {
	
	/**
	 * The class name of the entity this controller is for.
	 * 
	 * @return string
	 */
	protected function entityClass() {
		return model\Instance::class;
	}
}
