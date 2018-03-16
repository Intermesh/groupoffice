<?php
namespace go\modules\imapauth\model;

use go\core\jmap\Entity;

class Server extends \go\core\orm\Property {
	
	public $id;
	public $serverId;
	public $name;
	
	protected static function defineMapping() {
		return parent::defineMapping()
						->addTable('imapauth_domain');
						
	}
}
