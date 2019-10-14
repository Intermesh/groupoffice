<?php
namespace go\modules\community\notes;

use go\core\module\Base;
use go\modules\core\groups\model\Group;
use go\modules\core\modules\model\Module as ModuleModel;

class Module extends Base {	

	public function getAuthor() {
		return "Intermesh BV";
	}
	
	protected function afterInstall(ModuleModel $model): bool {
		
		if(!$model->findAcl()
						->addGroup(Group::ID_INTERNAL)
						->save()) {
			return false;
		}
		
		return parent::afterInstall($model);
	}
	

	
	
//	public function defineListeners() {
//		model\Note::on(model\Note::EVENT_SAVE, static::class, 'onSave');
//	}
//	
//	public static function onSave(model\Note $note) {
//		
//		\go\core\App::get()->debug("A note has been saved :)");
//		
//		return true;
//	}
}
