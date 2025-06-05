<?php
namespace go\modules\community\imapauthenticator\model;

use go\core\jmap\Entity;
use go\core\orm\Mapping;
use go\core\orm\Property;

class Domain extends Property {
	
	public ?string $id;
	public string $serverId;
	public string $name;
	
	protected static function defineMapping(): Mapping
	{
		return parent::defineMapping()
						->addTable('imapauth_server_domain');
						
	}
}
