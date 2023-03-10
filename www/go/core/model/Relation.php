<?php

namespace go\core\model;

use go\core\orm\Mapping;
use go\core\orm\Property;

class Relation extends Property
{
	public int $fieldId;

	public int $entityTypeId;

	public int $entityId;

	protected static function defineMapping(): Mapping
	{
		return parent::defineMapping()
			->addTable("core_customfields_relation");
	}

}