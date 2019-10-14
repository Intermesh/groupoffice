<?php
namespace go\core\model;

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
	
	protected static function defineMapping() {
		return parent::defineMapping()->addTable('core_user_group', 'ug');
	}
}
