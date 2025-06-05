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
	 */
	public ?int $userId;

	/**
	 * @param Query $query
	 * @param int $level
	 * @param int|null $userId
	 * @param array|null $groups
	 * @return Query
	 */
	public static function applyAclToQuery(Query $query, int $level = Acl::LEVEL_READ, int|null $userId = null, array|null $groups = null) : Query
	{
		$query->andWhere($query->getTableAlias() . '.userId', $userId);

		return $query;
	}

	protected function internalGetPermissionLevel(): int
	{
		return $this->userId == go()->getAuthState()->getUserId() ? Acl::LEVEL_MANAGE : false;
	}
}