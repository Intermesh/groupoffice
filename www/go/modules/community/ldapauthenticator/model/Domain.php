<?php
namespace go\modules\community\ldapauthenticator\model;

use go\core\jmap\Entity;

class Domain extends \go\core\orm\Property {
	
	public $id;
	public $serverId;
	public $name;
	
	protected static function defineMapping() {
		return parent::defineMapping()
						->addTable('ldapauth_server_domain');
						
	}
}
