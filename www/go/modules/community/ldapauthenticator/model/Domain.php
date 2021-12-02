<?php
namespace go\modules\community\ldapauthenticator\model;

use go\core\jmap\Entity;
use go\core\orm\Mapping;

class Domain extends \go\core\orm\Property {
	
	public $id;
	public $serverId;
	public $name;
	
	protected static function defineMapping(): Mapping
	{
		return parent::defineMapping()
						->addTable('ldapauth_server_domain');
						
	}
}
