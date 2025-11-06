<?php
namespace go\modules\community\comments\model;

use go\core\jmap\Entity;
use go\core\orm\Mapping;

class Label extends Entity {

	public ?string $id; // was removed from jmap\Entity?
	
	public string $name;
	public ?string $color = null;
	public ?string $createdBy = null;

	protected static function defineMapping(): Mapping
	{
		return parent::defineMapping()
						->addTable("comments_label");
	}

	protected function canCreate(): bool
	{
		return true;
	}

	public static function getClientName(): string
	{
		return "CommentLabel";
	}
}