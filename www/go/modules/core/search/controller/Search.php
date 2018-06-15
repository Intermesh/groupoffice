<?php

namespace go\modules\core\search\controller;

use go\core\jmap\Controller;
use go\modules\core\search\model\Search as S;

class Search extends \go\core\jmap\ReadOnlyEntityController {

	protected function entityClass() {
		return S::class;
	}

}
