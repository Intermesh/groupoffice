<?php
namespace go\modules\community\comments\model;

use go\core\orm\Property;

class Settings extends Property {

	public $enableQuickAdd;
	public $userId;

	protected static function defineMapping() {
		return parent::defineMapping()->addTable("comments_settings", "comms");
	}
	
}