<?php


namespace go\modules\community\addressbook\model;

use go\core\orm\Mapping;
use go\core\orm\Property;


final class AddressBookPortletBirthday extends Property
{
	/** @var int */
	public $id;

	/** @var int */
	public $userId;

	/** @var int */
	public $addressBookId;

	protected static function defineMapping(): Mapping
	{
		return parent::defineMapping()
			->addTable("addressbook_portlet_birthday");
	}

}