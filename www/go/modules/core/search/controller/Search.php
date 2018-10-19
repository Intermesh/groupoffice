<?php

namespace go\modules\core\search\controller;

use go\core\jmap\Controller;
use go\modules\core\search\model;

class Search extends \go\core\jmap\EntityController {

	protected function entityClass() {
		return model\Search::class;
	}

}
