<?php

namespace go\modules\community\addressbook\controller;

use go\core\jmap\EntityController;
use go\modules\community\addressbook\model;


class AddressBook extends EntityController {
	
	/**
	 * The class name of the entity this controller is for.
	 * 
	 * @return string
	 */
	protected function entityClass() {
		return model\AddressBook::class;
	}	
}
