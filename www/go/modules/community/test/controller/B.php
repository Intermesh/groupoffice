<?php
namespace go\modules\community\test\controller;

use go\core\jmap\EntityController;
use go\modules\community\test\model;

class B extends EntityController {

	protected function entityClass() {
		return model\B;
	}

}
