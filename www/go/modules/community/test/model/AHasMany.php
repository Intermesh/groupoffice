<?php
namespace go\modules\community\test\model;


use go\core\orm\Mapping;

class AHasMany extends \go\core\orm\Property {
	
	protected ?int $id;
	
	protected ?int $aId;
	
	public string $propOfHasManyA;
	
	protected static function defineMapping(): Mapping
	{
		return parent::defineMapping()->addTable('test_a_has_many', 'h');
	}
}
