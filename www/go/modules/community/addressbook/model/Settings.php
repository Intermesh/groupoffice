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

	public $createPersonalAddressBooks = true;

	public $restrictExportToAdmins = false;
}
