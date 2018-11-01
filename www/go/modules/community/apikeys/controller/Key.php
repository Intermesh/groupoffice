<?php

namespace go\modules\community\apikeys\controller;

use go\core\jmap\EntityController;
use go\modules\community\apikeys\model;


class Key extends EntityController {
	
	/**
	 * The class name of the entity this controller is for.
	 * 
	 * @return string
	 */
	protected function entityClass() {
		return model\Key::class;
	}
}
