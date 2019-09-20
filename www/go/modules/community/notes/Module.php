<?php
namespace go\modules\community\notes;

use go\core;
use go\core\model\Acl;
use go\core\model\Group;
use go\core\model\Module as ModuleModel;
use go\modules\community\notes\model\NoteBook;

class Module extends core\Module {	

	public function getAuthor() {
		return "Intermesh BV";
	}
	
	protected function afterInstall(ModuleModel $model) {	
		
		$noteBook = new NoteBook();
		$noteBook->name = go()->t("Shared");
		$noteBook->setAcl([
			Group::ID_INTERNAL => Acl::LEVEL_DELETE
		]);
		$noteBook->save();

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
