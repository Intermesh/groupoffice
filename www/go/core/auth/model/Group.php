<?php

namespace go\core\auth\model;

use go\core\orm\Entity;

class Group extends \go\core\acl\model\AclEntity {
	
	const ID_ADMINS = 1;
	
	const ID_EVERYONE = 2;
	
	const ID_INTERNAL = 3;
	
	public $id;
	
	public $name;
	
	public $isUserGroupFor;
	
	public $createdBy;
	
	protected static function defineMapping() {
		return parent::defineMapping()->addTable('core_group');
	}

}
