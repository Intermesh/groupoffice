<?php
namespace go\core\acl\model;

use go\core\App;
use go\core\db\Query;
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
	
	protected static function defineMapping() {
		return parent::defineMapping()
						->addTable('core_acl_group');						
	}
	

	protected function internalSave() {
		
		if(\go\core\jmap\Entity::$trackChanges) {
			$success = App::get()->getDbConnection()
							->insert('core_acl_group_changes', 
											[
													'aclId' => $this->aclId, 
													'groupId' => $this->groupId, 
													'grantModSeq' => Acl::getType()->nextModSeq(),
													'revokeModSeq' => null
											]
											)->execute();

			if(!$success) {
				return false;
			}
		}
		
		return parent::internalSave();
	}
	
	protected function internalDelete() {
		
		$success = App::get()->getDbConnection()
						->update('core_acl_group_changes', 
										[												
											'revokeModSeq' => Acl::getType()->nextModSeq()											
										],
										[
											'aclId' => $this->aclId, 
											'groupId' => $this->groupId,
											'revokeModSeq' => null
										]
										)->execute();
		
		if(!$success) {
			return false;
		}
		
		return $this->internalSave();
	}
}
