<?php

namespace go\modules\community\addressbook\model;

use go\core\orm\Property;

class UserSettings extends Property {

	/**
	 * Primary key to User id
	 * 
	 * @var int
	 */
	public $userId;
	
	/**
	 * Default address book ID
	 * 
	 * @var int
	 */
	public $defaultAddressBookId;


	public $sortBy = 'name';

	protected static function defineMapping() {
		return parent::defineMapping()->addTable("addressbook_user_settings", "abs");
	}

	
}
