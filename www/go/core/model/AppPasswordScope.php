<?php

namespace go\core\model;

use go\core\orm\Mapping;
use go\core\orm\Property;

class AppPasswordScope extends Property
{
	public ?string $id;

	public string $appPasswordId;

	public string $protocol;

	protected static function defineMapping(): Mapping
	{
		return parent::defineMapping()
			->addTable("core_app_password_scope", "caps");
	}
}