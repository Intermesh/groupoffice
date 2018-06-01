<?php

namespace go\modules\core\search\controller;

use go\core\jmap\Controller;
use go\core\search\Search as S;

class Search extends \go\core\jmap\ReadOnlyEntityController {

	protected function entityClass() {
		return S::class;
	}

}
