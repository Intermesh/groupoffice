<?php
namespace go\modules\community\test\model;


class AMap extends \go\core\orm\Property {
	
	protected $aId;
	
	protected $anotherAId;
	
	public $description;
	
	protected static function defineMapping() {
		return parent::defineMapping()->addTable('test_a_map', 'h');
	}
}
