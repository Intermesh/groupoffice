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

	/**
	 * @var string
	 */
	protected $restrictAutoCompleteInAddressBooks;

	/**
	 * @param $value
	 */
	public function setRestrictAutoCompleteInAddressBooks($value)
	{
		$this->restrictAutoCompleteInAddressBooks = implode(',', $value);
	}

	/**
	 * @return false|string[]
	 */
	public function getRestrictAutoCompleteInAddressBooks()
	{
	    if (empty($this->restrictAutoCompleteInAddressBooks)) {
	        return [];
        }
		return explode(',', $this->restrictAutoCompleteInAddressBooks);
	}
}
