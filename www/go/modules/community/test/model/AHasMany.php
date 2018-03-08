<?php
namespace go\modules\community\test\model;


class AHasMany extends \go\core\orm\Property {
	
	protected $id;
	
	protected $aId;
	
	public $propOfHasManyA;
	
	protected static function defineMapping() {
		return parent::defineMapping()->addTable('test_a_has_many', 'h');
	}
}
