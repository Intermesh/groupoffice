<?php


namespace go\modules\community\calendar\model;

use go\core\orm\Mapping;
use go\core\jmap\Entity;

class ResourceGroup extends Entity
{
	// Omit for the default alerts (with or without time)
	public $id;
	/** @var string The user-visible name of the calendar */
	public $name;
	public $description;

	protected static function defineMapping(): Mapping
	{
		return parent::defineMapping()->addTable('calendar_resource_group', "rg");
	}

}