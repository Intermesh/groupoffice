<?php
namespace go\core\acl\model;

use go\core\App;
use go\core\orm\Mapping;
use go\core\orm\Property;
use go\core\orm\StateManager;

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

//	protected function internalSave() {
//		
//		if($this->isNew()) {
//			$success = App::get()->getDbConnection()
//							->replace('core_acl_group_changes', 
//											[
//													'aclId' => $this->aclId, 
//													'groupId' => $this->groupId, 
//													'grantModSeq' => StateManager::get()->next()
//											]
//											)->execute();
//			
//			if(!$success) {
//				return false;
//			}
//		}
//		
//		return parent::internalSave();
//	}
//	
//	protected function internalDelete() {
//		
//		$success = App::get()->getDbConnection()
//							->update('core_acl_group_changes', 
//											[
//													'revokeModSeq' => StateManager::get()->next()
//											],
//											[
//													'aclId' => $this->aclId, 
//													'groupId' => $this->groupId, 
//													'revokeModSeq' => null
//											]
//											)->execute();
//		
//		if(!$success) {
//			return false;
//		}
//		
//		return $this->internalSave();
//	}
}
