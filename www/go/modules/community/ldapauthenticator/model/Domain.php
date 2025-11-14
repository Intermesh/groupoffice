<?php

namespace go\modules\community\ldapauthenticator\model;

use go\core\orm\Mapping;
use go\core\orm\Property;

class Domain extends Property
{

	public $id;
	public $serverId;
	public $name;

	protected static function defineMapping(): Mapping
	{
		return parent::defineMapping()
			->addTable('ldapauth_server_domain');

	}
}
