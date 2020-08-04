<?php
/*
 * Copyright Intermesh BV.
 *
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 *
 * If you have questions write an e-mail to info@intermesh.nl
 *
 */

/**
 * 
 * The Group model
 * 
 * @version $Id: Group.php 7607 2011-08-04 13:41:42Z mschering $
 * @copyright Copyright Intermesh BV.
 * @author Wesley Smits <wsmits@intermesh.nl>
 * @package GO.base.model
 * 
 * @property int $aclId
  * @property int $groupId
 * @property int $level {@see Acl::READ_PERMISSION etc}
 */

namespace GO\Base\Model;

use GO;
use GO\Base\Db\ActiveRecord;
use go\core\App;
use go\core\orm\StateManager;


class AclUsersGroups extends ActiveRecord {

	/**
	 * Returns a static model of itself
	 * 
	 * @param String $className
	 * @return AclUsersGroups 
	 */
	public static function model($className=__CLASS__)
	{	
		return parent::model($className);
	}
	
	public function tableName() {
		return 'core_acl_group';
	}
	
	/**
	 * The ACL record itself never has an ACL field so always return false
	 * @return boolean
	 */
	public function aclField() {
	  return false;
	}
  
  public function primaryKey() {
    return array('aclId','groupId');
  }
	
	
	public function relations() {
		return array('aclItem'=>array(
			"type"=>self::BELONGS_TO,
			"model"=>"GO\Base\Model\Acl",
			"field"=>'aclId'
		));
	}
	
	protected function afterDelete() {
		
		if($this->aclItem){
			$this->aclItem->touch();
		}
		
		$success = App::get()->getDbConnection()
							->update('core_acl_group_changes', 
											[
													'revokeModSeq' => \go\core\model\Acl::entityType()->nextModSeq()
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
		
		return parent::afterDelete();
	}
	

	
	protected function afterSave($wasNew) {
		if($this->aclItem){
			$this->aclItem->touch();
		}
		
		$success = App::get()->getDbConnection()
							->insert('core_acl_group_changes', 
											[
													'aclId' => $this->aclId, 
													'groupId' => $this->groupId, 
													'grantModSeq' => \go\core\model\Acl::entityType()->nextModSeq()
											]
											)->execute();
		if(!$success) {
			return false;
		}
		
		return parent::afterSave($wasNew);
	}
}
