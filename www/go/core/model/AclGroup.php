<?php
namespace go\core\model;

use go\core\App;
use go\core\db\Query;
use go\core\orm\Mapping;
use go\core\orm\Property;

/**
 * The AclGroup class
 * 
 * Belong to an {@see Acl} and hold the groups that have access with a permission level.
 * 
 * @todo What if a group get's deleted? Then changes are not recoverable.
 */
class AclGroup extends Property {
	
	protected $aclId;
	
	public $groupId;
	
	/**
	 * The level of access see. The LEVEL_* constants in Acl
	 * 
	 * @var int
	 */
	public $level;
	
	protected $grantModSeq;
	
	protected $revokeModSeq;
	
	protected static function defineMapping(): Mapping
	{
		return parent::defineMapping()
						->addTable('core_acl_group');						
	}
}
