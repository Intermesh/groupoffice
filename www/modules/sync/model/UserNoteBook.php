<?php
namespace GO\Sync\Model;

use go\core\orm\Property;

class UserNoteBook extends Property {
	
	/**
	 *
	 * @var int
	 */
	public $userId;
	
	/**
	 *
	 * @var int
	 */
	public $noteBookId;
	
	/**
	 *
	 * @var boolean
	 */
	public $isDefault;
	protected static function defineMapping() {
		return parent::defineMapping()->addTable('sync_user_note_book');
	}
}
