<?php
namespace go\modules\community\test\model;

use go\core\jmap\Entity;
use go\core\orm\Mapping;

class C extends Entity {
	
	public $id;
	
	public $name;
	
	protected static function defineMapping(): Mapping
	{
		return parent::defineMapping()->addTable('test_c');
	}

}
