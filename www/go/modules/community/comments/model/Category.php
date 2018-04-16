<?php
namespace go\modules\community\comments\model;

use go\core\jmap\Entity;

class Category extends Entity {

	public $name;
	public $createdBy;

	protected static function defineMapping() {
		return parent::defineMapping()
						->addTable("comments_category");
	}
	
	public static function getClientName() {
		return "CommentCategory";
	}
}