<?php
namespace go\modules\community\email\model;

use go\core\acl\model\AclOwnerEntity;
use go\core\orm\Mapping;

class Account extends AclOwnerEntity {
	
	public $id;
	public $username;
	
	protected static function defineMapping(): Mapping
	{
		return parent::defineMapping()
						->addTable("em_accounts")
						->addQuery((new \go\core\db\Query)->select('acl_id AS aclId')); //temporary hack
	}
}
