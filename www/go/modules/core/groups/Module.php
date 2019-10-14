<?php
namespace go\modules\core\groups;

use go\core\module\Base;
use go\modules\core\groups\model\Group;
use go\modules\core\modules\model\Module as ModuleModel;
use go\modules\core\users\model\Settings;

class Module extends Base {
	public function getAuthor() {
		return "Intermesh BV";
	}
	
	protected function afterInstall(ModuleModel $model): bool {
		
		Settings::get()->setDefaultGroups([Group::ID_EVERYONE]);
		
		return parent::afterInstall($model);
	}
}
