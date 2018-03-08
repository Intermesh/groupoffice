<?php
namespace go\modules\community\test\model;


class ADynamic extends \go\core\orm\Property {
	
	protected $id;
	
	protected $aId;
	
	public $propA;
	
	public $propC = "dynamic!";
	
	protected static function defineMapping() {
		return parent::defineMapping()->addTable('test_a_has_one', 'h');
	}
}
