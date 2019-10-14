<?php
namespace go\modules\community\addressbook\model;

use go\core\acl\model\AclEntity;

class AddressBook extends AclEntity {
	
	public $id;
	public $name;
	
	protected static function defineMapping() {
		return parent::defineMapping()
						->addTable("ab_addressbooks")
						->setQuery((new \go\core\db\Query)->select('acl_id AS aclId')); //temporary hack
	}
}
