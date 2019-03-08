<?php

namespace go\core\model;

use go\core\acl\model\Acl;
use go\core\acl\model\AclOwnerEntity;
use go\core\db\Criteria;
use go\core\model\UserGroup;
use go\core\validate\ErrorCode;

/**
 * Group model
 */
class Group extends AclOwnerEntity {

	const ID_ADMINS = 1;
	const ID_EVERYONE = 2;
	const ID_INTERNAL = 3;

	/**
	 *
	 * @var int
	 */
	public $id;
	
	/**
	 *
	 * @var string
	 */
	public $name;

	/**
	 * When this is set this group is the personal group for this user. And only
	 * that user will be member of this group. It's used for granting permissions
	 * to single users but keeping the database simple.
	 * 
	 * @var int
	 */
	public $isUserGroupFor;
	
	/**
	 * Created by user ID 
	 * 
	 * @var int
	 */
	public $createdBy;
	
	/**
	 * The users in this group
	 * 
	 * @var UserGroup[]
	 */
	public $users;
	
	protected function aclEntityClass() {
		
	}
	
	protected static function defineMapping() {
		return parent::defineMapping()
						->addTable('core_group', 'g')
						->addRelation('users', UserGroup::class, ['id' => 'groupId']);
	}
	
	protected static function defineFilters() {
		return parent::defineFilters()
						->add('hideUsers', function(Criteria $criteria, $value) {
							if($value) {
								$criteria->andWhere(['isUserGroupFor' => null]);	
							}
						})
						->add('excludeEveryone', function(Criteria $criteria, $value) {
							if($value) {
								$criteria->andWhere('id', '!=', Group::ID_EVERYONE);
							}
						})
						->add('excludeAdmins', function(Criteria $criteria, $value) {
							if($value) {
								$criteria->andWhere('id', '!=', Group::ID_ADMINS);
							}
						});
						
	}
	
	protected static function searchColumns() {
		return ['name'];
	}
	
	protected function internalSave() {
		
		if(!parent::internalSave()) {
			return false;
		}
		
		if(!$this->isNew()) {
			return true;
		}
		
		return $this->setDefaultPermissions();		
	}
	
	private function setDefaultPermissions() {
		$acl = $this->findAcl();
		//Share group with itself. So members of this group can share with eachother.
		if($this->id !== Group::ID_ADMINS) {
			$acl->addGroup($this->id, Acl::LEVEL_READ);
		}
		
		return $acl->internalSave();
	}
	
	protected function internalDelete() {
		
		if(isset($this->isUserGroupFor)) {
			$this->setValidationError('isUserGroupFor', ErrorCode::FORBIDDEN, "You can't delete a user's personal group");
			return false;
		}
		
		return parent::internalDelete();
	}

}
