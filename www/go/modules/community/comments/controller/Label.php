<?php

namespace go\modules\community\comments\controller;

use go\core\jmap\EntityController;
use go\modules\community\comments\model;


class Label extends EntityController {
	
	protected function entityClass() {
		return model\Label::class;
	}
}