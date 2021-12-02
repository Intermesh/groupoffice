<?php
namespace go\modules\community\test\model;


use go\core\orm\Mapping;

class AHasOne extends \go\core\orm\Property {
	
	protected $id;
	
	protected $aId;
	
	public $propA;
	
	protected static function defineMapping(): Mapping
	{
		return parent::defineMapping()->addTable('test_a_has_one', 'h');
	}
}
