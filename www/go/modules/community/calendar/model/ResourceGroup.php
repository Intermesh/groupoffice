<?php


namespace go\modules\community\calendar\model;

use go\core\orm\Mapping;
use go\core\jmap\Entity;
use go\modules\business\projects3\model\ProjectResource;

class ResourceGroup extends Entity
{
	// Omit for the default alerts (with or without time)
	public ?string $id;
	/** @var string The user-visible name of the calendar */
	public string $name;
	public ?string $description;
	/** @var ?string Will be set to any resource calendar that is created without owner.*/
	public ?string $defaultOwnerId;

	protected static function defineMapping(): Mapping
	{
		return parent::defineMapping()
			->addTable('calendar_resource_group', "rg");
	}

}