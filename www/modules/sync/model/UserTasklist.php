<?php
namespace GO\Sync\Model;

use go\core\orm\Mapping;
use go\core\orm\Property;

class UserTasklist extends Property {
	
	/**
	 *
	 * @var int
	 */
	public $userId;
	
	/**
	 *
	 * @var int
	 */
	public $tasklistId;
	
	/**
	 *
	 * @var boolean
	 */
	public $isDefault;

	protected static function defineMapping(): Mapping
	{
		return parent::defineMapping()->addTable('sync_tasklist_user');
	}
}
