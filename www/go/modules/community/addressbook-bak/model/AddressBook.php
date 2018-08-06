<?php
namespace go\modules\community\addressbook\model;

use go\core\acl\model\AclEntity;

class AddressBook extends AclEntity {
	
	/**
	 * @var int
	 */
	public $id;

	/**
	 * @var string
	 */
	public $name;

	/**
	 * @var int
	 */
	public $acid;

	/**
	 * @var int
	 */
	public $createdBy;
	
	protected static function defineMapping() {
		return parent::defineMapping()
						->addTable("addressbook_addressbook");
	}
}
