<?php

namespace go\core\model;

use go\core\db\Criteria;
use go\core\jmap\Entity;
use go\core\orm\Query;
use go\core\orm\SearchableTrait;


class UserDisplay extends Entity {


	/**
	 * The ID
	 *
	 * @var int
	 */
	public $id;

	/**
	 * Username eg. "john"
	 * @var string
	 */
	public $username;

	/**
	 * Display name eg. "John Smith"
	 * @var string
	 */
	public $displayName;

	public $avatarId;

	/**
	 * E-mail address
	 *
	 * @var string
	 */
	public $email;

	protected static function textFilterColumns()
	{
		return ['username', 'displayName', 'email'];
	}


	protected static function defineMapping() {
		return parent::defineMapping()
			->addTable('core_user', 'u');
	}

	protected static function defineFilters() {
		return parent::defineFilters()
			->add('permissionLevel', function(Criteria $criteria, $value, Query $query) {
				if(!$query->isJoined('core_group', 'g')) {
					$query->join('core_group', 'g', 'u.id = g.isUserGroupFor');
				}
				Acl::applyToQuery($query, 'g.aclId', $value);
			}, Acl::LEVEL_READ)
			->add('showDisabled', function (Criteria $criteria, $value){
				if($value === false) {
					$criteria->andWhere('enabled', '=', 1);
				}
			}, false)
			->add('groupId', function (Criteria $criteria, $value, Query $query){
				$query->join('core_user_group', 'ug', 'ug.userId = u.id')->andWhere(['ug.groupId' => $value]);
			});
	}
}
