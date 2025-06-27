<?php
namespace go\modules\community\email\model;

use go\core\acl\model\AclOwnerEntity;
use go\core\orm\Mapping;

/**
 * @property \go\modules\community\oauth2client\model\Oauth2Account|null $oauth2_account
 */
class Account extends AclOwnerEntity {
	
	public ?string $id;
	public string $username;
	
	protected static function defineMapping(): Mapping
	{
		return parent::defineMapping()
			->addTable("em_accounts")
			->addQuery((new \go\core\db\Query)->select('acl_id AS aclId')); //temporary hack
	}
}
