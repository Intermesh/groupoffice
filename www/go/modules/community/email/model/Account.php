<?php
namespace go\modules\community\email\model;

use go\core\acl\model\AclEntity;

class Account extends AclEntity {
	
	public $id;
	public $username;
	
	protected static function defineMapping() {
		return parent::defineMapping()
						->addTable("em_accounts")
						->setQuery((new \go\core\db\Query)->select('acl_id AS aclId')); //temporary hack
	}
}
