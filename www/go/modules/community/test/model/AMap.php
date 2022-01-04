<?php
namespace go\modules\community\test\model;


use go\core\orm\Mapping;

class AMap extends \go\core\orm\Property {
	
	public $aId;
	
	public $anotherAId;
	
	public $description;
	
	protected static function defineMapping(): Mapping
	{
		return parent::defineMapping()->addTable('test_a_map', 'h');
	}
}
