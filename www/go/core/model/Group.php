<?php

namespace go\core\model;

use go\core\acl\model\AclOwnerEntity;
use go\core\db\Criteria;
use go\core\exception\Forbidden;
use go\core\orm\Query;
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
	 * @var int[]
	 */
	public $users;	

	protected static function defineMapping() {
		return parent::defineMapping()
						->addTable('core_group', 'g')
						->addScalar('users', 'core_user_group', ['id' => 'groupId']);
	}
	
	protected static function defineFilters() {
		return parent::defineFilters()
						->add('hideUsers', function(Criteria $criteria, $value, Query $query) {
							if($value) {
								$criteria->andWhere(['isUserGroupFor' => null]);	
							} else {
								$query->join('core_user', 'user', 'user.id = g.isUserGroupFor', 'LEFT');
								$criteria->where('(user.enabled is null or user.enabled = 1)');
							}
						})
						->add('hideGroups', function(Criteria $criteria, $value) {
							if($value) {
								$criteria->andWhere('isUserGroupFor','IS NOT', null);
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
						})->add('forUserId', function(Criteria $criteria, $value, Query $query) {
							
							$query->join('core_user_group','ug', 'ug.groupId=g.id')
											->groupBy(['g.id']);
							
							if($value) {
								$criteria->andWhere(['ug.userId' => $value]);	
							}
						});
						
	}
	
	protected static function textFilterColumns() {
		return ['name'];
	}

	protected function internalValidate()
	{
		if(!$this->isNew() && $this->id === self::ID_ADMINS && !in_array(1, $this->users)) {
			$this->setValidationError('users', ErrorCode::FORBIDDEN, go()->t("You can't remove the admin user from the administrators group"));
		}

		if($this->isUserGroupFor && !in_array($this->isUserGroupFor, $this->users))
		{
			$this->setValidationError('users', ErrorCode::FORBIDDEN, go()->t("You can't remove the group owner from the group"));
		}

		if($this->id == self::ID_EVERYONE && $this->isModified(['users'])) {
			$this->setValidationError('users', ErrorCode::FORBIDDEN, go()->t("You can't modify members of the everyone group"));
		}

		return parent::internalValidate();
	}

	public static function check()
	{
		//make sure all users are in group everyone
		go()->getDbConnection()->exec("INSERT IGNORE INTO core_user_group (SELECT " . self::ID_EVERYONE .", id from core_user)");
		return parent::check();
	}

	protected function internalSave() {
		
		if(!parent::internalSave()) {
			return false;
		}
		
		$this->saveModules();

		if(!$this->isNew()) {
			return true;
		}

		return $this->setDefaultPermissions();		
	}

	protected function canCreate()
	{
		return go()->getAuthState()->isAdmin();
	}
	
	private function setDefaultPermissions() {
		$acl = $this->findAcl();
		//Share group with itself. So members of this group can share with eachother.
		if($this->id !== Group::ID_ADMINS) {
			$acl->addGroup($this->id, Acl::LEVEL_READ);
		}
		
		return $acl->save();
	}
	
	protected static function internalDelete(Query $query) {

		$query->andWhere(['isUserGroupFor' => null]);

		$ids = $query->all();

		if(in_array(self::ID_ADMINS, $ids)) {
			throw new Forbidden("You can't delete the administrators group");
		}

		if(in_array(self::ID_INTERNAL, $ids)) {
			throw new Forbidden("You can't delete the internal group");
		}

		if(in_array(self::ID_EVERYONE, $ids)) {
			throw new Forbidden("You can't delete the internal group");
		}
		
		// if(isset($this->isUserGroupFor)) {
		// 	$this->setValidationError('isUserGroupFor', ErrorCode::FORBIDDEN, "You can't delete a user's personal group");
		// 	return false;
		// }
		
		return parent::internalDelete($query);
	}


	public function getModules() {
		$modules = [];

		$mods = Module::find()
							->select('id,level')
							->fetchMode(\PDO::FETCH_ASSOC)
							->join('core_acl_group', 'acl_g', 'acl_g.aclId=m.aclId')
							->where(['acl_g.groupId' => $this->id])
							->all();

		if(empty($mods)) {
			//return null because an empty array is serialzed as [] instead of {}
			return null;
		}

		foreach($mods as $m) {
			$modules[$m['id']] = $m['level'];
		}

		return $modules;
	}

	private $setModules;

	public function setModules($modules) {
		$this->setModules = $modules;
	}

	private function saveModules() {
		if(!isset($this->setModules)) {
			return true;
		}

		foreach($this->setModules as $moduleId => $level) {
			$module = Module::findById($moduleId);
			if(!$module) {
				throw new \Exception("Module with ID " . $moduleId . " not found");
			}
			$module->setAcl([
				$this->id => $level
			]);
			$module->save();
		}
	}

	public static function findPersonalGroupID($userId) {
		$groupId = Group::find()
							->where(['isUserGroupFor' => $userId])
							->selectSingleValue('id')
							->single();
		if($groupId) {
			return $groupId;
		}
		$user = User::findById($userId, ['username']);
		if(!$user) {
			throw new \Exception("Invalid userId given");
		}
		$personalGroup = new Group();
		$personalGroup->name = $user->username;
		$personalGroup->isUserGroupFor = $userId;
		$personalGroup->users[] = $userId;
		
		if(!$personalGroup->save()) {
			throw new \Exception("Could not create personal group");
		}

		return $personalGroup->id;
		
	}

}
