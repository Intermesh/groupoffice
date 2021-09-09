<?php
namespace go\modules\community\email\model;

use go\core\acl\model\AclOwnerEntity;

class Account extends AclOwnerEntity {
	
	public $id;
	public $username;
	
	protected static function defineMapping() {
		return parent::defineMapping()
						->addTable("em_accounts")
						->addQuery((new \go\core\db\Query)->select('acl_id AS aclId')); //temporary hack
	}
}
