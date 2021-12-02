<?php
namespace go\modules\community\calendar\model;

use go\core\acl\model\AclOwnerEntity;
use go\core\orm\Mapping;

class Calendar extends AclOwnerEntity {
	
	public $id;
	public $name;
	
	protected static function defineMapping(): Mapping
	{
		return parent::defineMapping()
						->addTable("cal_calendars")
						->addQuery((new \go\core\db\Query)->select('acl_id AS aclId')); //temporary hack
	}
}
