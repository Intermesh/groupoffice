<?php
namespace go\modules\community\addressbook\model;
use go\core;


class Settings extends core\Settings {
		
	/**
	 * Automatically link email to contacts
	 * 
	 * @var bool
	 */
	public $autoLinkEmail = false;

	/**
	 * Create a personal address book for each new user
	 *
	 * @var bool
	 */
	public $createPersonalAddressBooks = true;

}
