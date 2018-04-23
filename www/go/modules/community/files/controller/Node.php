<?php

namespace go\modules\community\files\controller;

use go\core\jmap\EntityController;
use go\modules\community\files\model;


class Node extends EntityController {
	
	/**
	 * The class name of the entity this controller is for.
	 * 
	 * @return string
	 */
	protected function entityClass() {
		return model\Node::class;
	}
}