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

	// on: auto link all contacts
	// off: never auto link
	// incl: auto link contact in addressbooks listed in $autoLinkaddressBookIds
	// excl auto link all contact except from addressbooks listed in $autoLinkaddressBookIds
	public $autoLink;
	protected $autoLinkAddressBookIds;

	public function getAutoLinkAddressBookIds() {
		return json_decode($this->autoLinkAddressBookIds);
	}

	public function setAutoLinkAddressBookIds($v) {
		$this->autoLinkAddressBookIds = json_encode($v);
	}

}
