<?php
namespace go\modules\community\test;

use go\core\App;
use go\core;
use go\core\orm\Mapping;
use go\core\orm\Property;
use go\modules\community\test\model\A;
use go\modules\community\test\model\ADynamic;

class Module extends core\Module {	

	public function getAuthor() {
		return "Intermesh BV";
	}	
	
	public function defineListeners() {
		A::on(Property::EVENT_MAPPING, static::class, 'onMap');
	}
	
	public static function onMap(Mapping $mapping) {		
		$mapping->addHasOne("dynamic", ADynamic::class, ['id' => 'aId']);		
		$mapping->addTable('test_d', 'd', ['id' => 'id'], ['propD']);
		return true;
	}
}
