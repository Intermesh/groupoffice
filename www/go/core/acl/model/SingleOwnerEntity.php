<?php

namespace go\core\acl\model;

use go\core\jmap\Entity;
use go\core\model\Acl;
use go\core\orm\Query;
use function GO;

abstract class SingleOwnerEntity extends Entity
{
	/**
	 * The user ID owning this entity
	 *
	 * @var int
	 */
	public $userId;

	/**
	 * @param Query $query
	 * @param int $level
	 * @param int|null $userId
	 * @param array|null $groups
	 * @return Query
	 */
	public static function applyAclToQuery(Query $query, int $level = Acl::LEVEL_READ, int $userId = null, array $groups = null) : Query
	{
		$query->andWhere($query->getTableAlias() . '.userId', $userId);

		return $query;
	}

	public function getPermissionLevel()
	{
		return $this->userId == go()->getAuthState()->getUserId() ? Acl::LEVEL_MANAGE : false;
	}
}