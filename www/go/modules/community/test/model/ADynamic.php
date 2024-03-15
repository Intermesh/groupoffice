<?php
namespace go\modules\community\test\model;


use go\core\orm\Mapping;

class ADynamic extends \go\core\orm\Property {
	
	protected ?int $id;
	
	protected ?int $aId;
	
	public string $propA;
	
	public string $propC = "dynamic!";
	
	protected static function defineMapping(): Mapping
	{
		return parent::defineMapping()->addTable('test_a_has_one', 'h');
	}
}
