<?php
namespace go\modules\community\imapauthenticator\model;

use go\core\jmap\Entity;
use go\core\orm\Mapping;

class Group extends \go\core\orm\Property {
	
	public int $groupId;
	public int $serverId;
	
	protected static function defineMapping(): Mapping
	{
		return parent::defineMapping()
						->addTable('imapauth_server_group');
						
	}
}

