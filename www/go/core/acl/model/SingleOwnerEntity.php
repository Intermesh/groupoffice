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

	public static function applyAclToQuery(Query $query, $level = Acl::LEVEL_READ, $userId = null, $groups = null)
	{
		$query->andWhere($query->getTableAlias() . '.userId', $userId);
	}

	protected function internalGetPermissionLevel()
	{
		return $this->userId == go()->getAuthState()->getUserId() ? Acl::LEVEL_MANAGE : false;
	}
}