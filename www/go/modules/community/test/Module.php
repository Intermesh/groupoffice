<?php
namespace go\modules\community\test;

use go\core\App;
use go\core\module\Base;
use go\core\orm\Mapping;
use go\core\orm\Property;
use go\modules\community\test\model\A;
use go\modules\community\test\model\ADynamic;

class Module extends Base {	

	public function getAuthor() {
		return "Intermesh BV";
	}	
	
	public function defineListeners() {
		A::on(Property::EVENT_MAPPING, static::class, 'onMap');
	}
	
	public static function onMap(Mapping $mapping) {		
		$mapping->addRelation("dynamic", ADynamic::class, ['id' => 'aId'], false);		
		$mapping->addTable('test_d', 'd', ['id' => 'id'], ['propD']);
		return true;
	}
}
