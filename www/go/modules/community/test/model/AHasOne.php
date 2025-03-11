<?php
namespace go\modules\community\test\model;


use go\core\orm\Mapping;

class AHasOne extends \go\core\orm\Property {
	
	protected ?int $id;
	
	protected ?int $aId;
	
	public string $propA;
	
	protected static function defineMapping(): Mapping
	{
		return parent::defineMapping()->addTable('test_a_has_one', 'h');
	}
}
