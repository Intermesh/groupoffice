<?php
namespace go\modules\community\addressbook\model;
use go\core;


class Settings extends core\Settings {
		
	/**
	 * Automatically link email to contacts
	 * 
	 * @var bool
	 */
	public bool $autoLinkEmail = false;

	/**
	 * Create a personal address book for each new user
	 *
	 * @var bool
	 */
	public bool $createPersonalAddressBooks = true;

	// on: auto link all contacts
	// off: never auto link
	// incl: auto link contact in addressbooks listed in $autoLinkaddressBookIds
	// excl auto link all contact except from addressbooks listed in $autoLinkaddressBookIds
	public string $autoLink = "off";
	protected ?string $autoLinkAddressBookIds = null;

	public function getAutoLinkAddressBookIds() : array {
		return !empty($this->autoLinkAddressBookIds) ? json_decode($this->autoLinkAddressBookIds) : [];
	}

	public function setAutoLinkAddressBookIds(array $v) {
		$this->autoLinkAddressBookIds = json_encode($v);
	}

}
