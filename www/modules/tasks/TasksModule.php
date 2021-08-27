<?php

namespace GO\Tasks;

use go\core\db\Criteria;
use go\core\jmap\Entity;
use go\core\model\Link;
use go\core\model\User;
use go\core\orm\Filters;
use go\core\orm\Mapping;
use go\core\orm\Property;
use go\core\orm\Query;
use GO\Tasks\Model\Task;
use GO\Tasks\Model\Tasklist;

class TasksModule extends \GO\Base\Module {

	public function autoInstall() {
		return false;
	}

}
