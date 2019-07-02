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

	public $salutationTemplate;

	public $sortBy = 'name';

	protected static function defineMapping() {
		return parent::defineMapping()->addTable("addressbook_user_settings", "abs");
	}

	protected function init()
	{
		
		if(empty($this->salutationTemplate)) {
			$this->salutationTemplate = 'Dear [if {{contact.prefixes}}]{{contact.prefixes}}[else][if !{{contact.gender}}]Ms./Mr.[else][if {{contact.gender}}=="M"]Mr.[else]Ms.[/if][/if][/if] {{contact.lastName}}';
		}

		parent::init();
	}
}
