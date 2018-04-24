<?php
namespace go\modules\community\files;

use go\core\auth\model\User;
use go\core\module\Base;
use go\core\orm\Mapping;
use go\core\orm\Property;
use go\modules\community\files\model\Storage;

class Module extends Base {	

	public function getAuthor() {
		return "Intermesh BV";
	}
	
	public function defineListeners() {
		User::on(Property::EVENT_MAPPING, static::class, 'onMap');
	}
	
	public static function onMap(Mapping $mapping) {		
		$mapping->addRelation("storage", Storage::class, ['id' => 'userId'], false);		
		return true;
	}
	
}
