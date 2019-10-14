<?php
namespace go\modules\community\tasks\model;

use go\core\acl\model\AclEntity;

class TaskList extends AclEntity {
	
	public $id;
	public $name;
	
	protected static function defineMapping() {
		return parent::defineMapping()
						->addTable("ta_tasklists")
						->setQuery((new \go\core\db\Query)->select('acl_id AS aclId')); //temporary hack
	}
}
