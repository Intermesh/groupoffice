<?php
namespace go\modules\community\test\model;


use go\core\orm\Mapping;

class ADynamic extends \go\core\orm\Property {
	
	protected $id;
	
	protected $aId;
	
	public $propA;
	
	public $propC = "dynamic!";
	
	protected static function defineMapping(): Mapping
	{
		return parent::defineMapping()->addTable('test_a_has_one', 'h');
	}
}
