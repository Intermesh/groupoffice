<?php

namespace go\modules\community\privacy\model;

use go\core\orm\Mapping;
use go\core\orm\Property;
use go\core\util\DateTime;
use go\modules\community\addressbook\model\Contact;

class ContactDeletion extends Property
{

	/**
	 * @var int
	 */
	public $contactId;

	/**
	 * @var DateTime
	 */
	public $deleteAt;

	/**
	 * @return Mapping
	 * @throws \Exception
	 */
	protected static function defineMapping(): Mapping
	{
		return parent::defineMapping()
			->addTable("community_privacy_contact", "cpc");
	}
}