<?php
namespace go\modules\community\ldapauthenticator\model;

use go\core\jmap\Entity;

class Group extends \go\core\orm\Property {
	
	public $groupId;
	public $serverId;
	
	protected static function defineMapping() {
		return parent::defineMapping()
						->addTable('ldapauth_server_group');
						
	}
}

