<?php
namespace go\core\model;

use go\core\orm\Mapping;

class UserGroup extends \go\core\orm\Property {
	
	public int $userId;
	public int $groupId;
	
	protected static function defineMapping(): Mapping
	{
		return parent::defineMapping()->addTable('core_user_group', 'ug');
	}
}
