<?php
namespace go\modules\community\calendar\model;

use go\core\acl\model\AclEntity;

class Calendar extends AclEntity {
	
	public $id;
	public $name;
	
	protected static function defineMapping() {
		return parent::defineMapping()
						->addTable("cal_calendars")
						->setQuery((new \go\core\db\Query)->select('acl_id AS aclId')); //temporary hack
	}
}
