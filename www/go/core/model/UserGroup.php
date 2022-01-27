<?php
namespace go\core\model;

use go\core\orm\Mapping;

class UserGroup extends \go\core\orm\Property {
	
	/**
	 *
	 * @var int
	 */
	public $userId;
	
	/**
	 *
	 * @var int
	 */
	public $groupId;
	
	protected static function defineMapping(): Mapping
	{
		return parent::defineMapping()->addTable('core_user_group', 'ug');
	}
}
