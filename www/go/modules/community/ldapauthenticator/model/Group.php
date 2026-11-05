<?php
namespace go\modules\community\ldapauthenticator\model;

use go\core\orm\Mapping;
use go\core\orm\Property;

class Group extends Property {
	
	public int $groupId;
	public int $serverId;
	
	protected static function defineMapping(): Mapping
	{
		return parent::defineMapping()
			->addTable('ldapauth_server_group');

	}
}

