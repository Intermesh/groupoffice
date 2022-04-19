<?php

namespace go\core\model;

use go\core\db\Criteria;
use go\core\db\Expression;
use go\core\jmap\Entity;
use go\core\orm\Filters;
use go\core\orm\Mapping;
use go\core\orm\Query;
use go\core\orm\SearchableTrait;
use go\core\util\ArrayObject;


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

	protected static function textFilterColumns(): array
	{
		return ['username', 'displayName', 'email'];
	}


	protected static function defineMapping(): Mapping
	{
		return parent::defineMapping()
			->addTable('core_user', 'u');
	}

	protected static function defineFilters(): Filters
	{
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
			})
			->add('groupMember',function (Criteria $criteria, $value, Query $query){
				//this filter doesn't actually filter but sorts the selected members on top
				$query->join('core_user_group', 'ug_sort', 'ug_sort.userId = u.id AND ug_sort.groupId = ' . (int) $value, 'LEFT');
				$query->orderBy(array_merge([new Expression('ISNULL(ug_sort.userId) ASC')], $query->getOrderBy()));
				$query->groupBy(['u.id']);
			})
			->add('aclId',  function (Criteria $criteria, $value, Query $query) {

				$query->join('core_user_group', 'aclIdUg', 'aclIdUg.userId = u.id')
					->join('core_acl_group', 'aclIdAg', 'aclIdAg.groupId = aclIdUg.groupId')
					->groupBy(['u.id'], true);

				$criteria->where('aclIdAg.aclId', '=', $value);
			})
			->add('aclPermissionLevel',  function (Criteria $criteria, $value, Query $query) {

				// can be used in conjunction with the aclId filter.

				$criteria->where('aclIdAg.level', '>=', $value);
			});
	}
}
