<?php
namespace go\modules\community\comments\model;

use go\core\jmap\Entity;

class Label extends Entity {

	public $id; // was removed from jmap\Entity?
	
	public $name;
	public $color;
	public $createdBy;

	protected static function defineMapping() {
		return parent::defineMapping()
						->addTable("comments_label");
	}
	
	public static function getClientName() {
		return "CommentLabel";
	}
}