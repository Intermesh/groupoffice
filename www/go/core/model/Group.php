<?php

namespace go\core\model;

use Exception;
use go\core\acl\model\AclOwnerEntity;
use go\core\db\Criteria;
use go\core\db\Expression;
use go\core\db\Query as DbQuery;
use go\core\exception\Forbidden;
use go\core\orm\EntityType;
use go\core\orm\Filters;
use go\core\orm\Mapping;
use go\core\orm\Query;
use go\core\validate\ErrorCode;

/**
 * Group model
 */
class Group extends AclOwnerEntity {

	const ID_ADMINS = 1;
	const ID_EVERYONE = 2;
	const ID_INTERNAL = 3;

	public ?string $id = null;
	
	/**
	 *
	 * @var string
	 */
	public string $name;

	/**
	 * When this is set this group is the personal group for this user. And only
	 * that user will be member of this group. It's used for granting permissions
	 * to single users but keeping the database simple.
	 * 
	 * @var ?string
	 */
	public ?string $isUserGroupFor = null;
	
	/**
	 * Created by user ID 
	 * 
	 * @var ?string
	 */
	public ?string $createdBy;
	
	/**
	 * The users in this group
	 * 
	 * @var int[]
	 */
	public array $users = [];

	protected static function defineMapping(): Mapping
	{
		return parent::defineMapping()
						->addTable('core_group', 'g')
						->addScalar('users', 'core_user_group', ['id' => 'groupId']);
	}
	
	protected static function defineFilters(): Filters
	{
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
						})
						->add('groupMember',function (Criteria $criteria, $value, Query $query){
							//this filter doesn't actually filter but sorts the selected members on top
							$query->join('core_user_group', 'ug_sort', 'ug_sort.groupId = g.id AND ug_sort.userId = ' . (int) $value, 'LEFT');
							$query->orderBy(array_merge([new Expression('ISNULL(ug_sort.groupId) ASC')], $query->getOrderBy()));
						})
					->add('inAcl',function (Criteria $criteria, $value, Query $query) {


						if(is_array($value)) {
							$type = EntityType::findByName($value['entity']);
							if (!empty($value['default']) || empty($value['id'])) {
								$aclId = $type->getDefaultAclId();
							} else {
								$cls = $type->getClassName();
								$aclId = $cls::find()->selectSingleValue($cls::$aclColumnName)->where('id', '=', $value['id'])->single();
							}
						} else{
							$aclId = $value;
						}

						//this filter doesn't actually filter but sorts the selected members on top
						$query->join('core_acl_group', 'ag_sort', 'ag_sort.groupId = g.id AND ag_sort.aclId = ' . (int) $aclId, 'LEFT');
						$query->orderBy(array_merge([new Expression('ISNULL(ag_sort.groupId) ASC')], $query->getOrderBy()));
						$query->groupBy(['g.id']);
					});
						
	}
	
	protected static function textFilterColumns(): array
	{
		return ['name', 'u.displayName'];
	}

	protected static function search(Criteria $criteria, string $expression, DbQuery $query): Criteria
	{
		$query->join('core_user', 'u', 'u.id = g.isUserGroupFor', 'LEFT');
		return parent::search($criteria, $expression, $query);
	}

	protected function internalValidate()
	{
		if(!$this->isNew() && $this->id === self::ID_ADMINS && !in_array(1, $this->users)) {
			$this->setValidationError('users', ErrorCode::FORBIDDEN, go()->t("You can't remove the admin user from the administrators group"));
		}

		// If this is a personal user group the user must be in it.
		if($this->isUserGroupFor && !in_array($this->isUserGroupFor, $this->users))
		{
			$this->setValidationError('users', ErrorCode::FORBIDDEN, go()->t("You can't remove the group owner from the group"));
		}

		// the group itself may not be removed from the ACL of the group
		if($this->isUserGroupFor && $this->isAclModified())
		{
			if(!isset($this->setAcl[$this->id])) {
				$this->setValidationError('users', ErrorCode::FORBIDDEN, go()->t("You can't remove the group owner from the group"));
			}
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

		//share groups with themselves
//		$stmt = go()->getDbConnection()
//			->insertIgnore(
//				'core_acl_group',
//				go()->getDbConnection()->select('aclId, id, "' . Acl::LEVEL_READ .'"')->from("core_group"),
//				['aclId', 'groupId', 'level']
//			);
//
//		$stmt->execute();

		return parent::check();
	}

	protected function internalSave(): bool
	{
		
		if(!parent::internalSave()) {
			return false;
		}
		
//		$this->saveModules();

		if(!$this->isNew()) {
			return true;
		}

		return $this->setDefaultPermissions();		
	}

	protected function canCreate(): bool
	{
		return go()->getAuthState()->isAdmin();
	}
	
	private function setDefaultPermissions(): bool
	{
		$acl = $this->findAcl();
		//Share group with itself. So members of this group can share with eachother.
		if($this->id !== Group::ID_ADMINS) {
			$acl->addGroup($this->id, Acl::LEVEL_READ);
		}
		
		return $acl->save();
	}
	
	protected static function internalDelete(Query $query): bool
	{

		$query->andWhere(['isUserGroupFor' => null]);

		$ids = array_map(fn($record): int => $record['id'], $query->all());

		if(in_array(self::ID_ADMINS, $ids)) {
			throw new Forbidden("You can't delete the 'Administrators' group");
		}

		if(in_array(self::ID_INTERNAL, $ids)) {
			throw new Forbidden("You can't delete the 'Internal' group");
		}

		if(in_array(self::ID_EVERYONE, $ids)) {
			throw new Forbidden("You can't delete the 'Everyone' group");
		}
		
		return parent::internalDelete($query);
	}
	
	/**
	 * Get the group ID that is used for granting permissions for the given user ID
	 *
	 * @param int $userId
	 * @return int
	 * @throws Exception
	 */
	public static function findPersonalGroupID(int $userId) : int {
		$groupId = Group::find()
							->where(['isUserGroupFor' => $userId])
							->selectSingleValue('id')
							->single();
		if($groupId) {
			return $groupId;
		}
		$user = User::findById($userId, ['username']);
		if(!$user) {
			throw new Exception("Invalid userId given");
		}
		$personalGroup = new Group();
		$personalGroup->name = $user->username;
		$personalGroup->isUserGroupFor = $userId;
		$personalGroup->users[] = $userId;
		
		if(!$personalGroup->save()) {
			throw new Exception("Could not create personal group");
		}

		return $personalGroup->id;
		
	}

}
