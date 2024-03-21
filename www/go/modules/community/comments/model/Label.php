<?php
namespace go\modules\community\comments\model;

use go\core\jmap\Entity;
use go\core\orm\Mapping;

class Label extends Entity {

	public $id; // was removed from jmap\Entity?
	
	public $name;
	public $color;
	public $createdBy;

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