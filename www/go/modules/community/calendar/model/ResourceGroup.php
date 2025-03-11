<?php


namespace go\modules\community\calendar\model;

use go\core\orm\Mapping;
use go\core\jmap\Entity;
use go\modules\business\projects3\model\ProjectResource;

class ResourceGroup extends Entity
{
	// Omit for the default alerts (with or without time)
	public $id;
	/** @var string The user-visible name of the calendar */
	public $name;
	public $description;
	/** @var int Will be set to any resource calendar that is created without owner.*/
	public $defaultOwnerId;

	protected static function defineMapping(): Mapping
	{
		return parent::defineMapping()
			->addTable('calendar_resource_group', "rg");
	}

}