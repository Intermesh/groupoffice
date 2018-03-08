<?php
namespace go\modules\community\notes;

use go\core\module\Base;

class Module extends Base {	

	public function getAuthor() {
		return "Intermesh BV";
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
