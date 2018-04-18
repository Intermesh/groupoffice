<?php

namespace go\core\auth\model;

use go\core\acl\model\AclEntity;
use go\core\db\Criteria;
use go\core\db\Query;

/**
 * Group model
 */
class Group extends AclEntity {

	const ID_ADMINS = 1;
	const ID_EVERYONE = 2;
	const ID_INTERNAL = 3;

	public $id;
	public $name;
	public $isUserGroupFor;
	public $createdBy;

	protected static function defineMapping() {
		return parent::defineMapping()->addTable('core_group');
	}

	public static function filter(Query $query, array $filter) {
		if (empty($filter['includeUsers'])) {
			$query->andWhere(['isUserGroupFor' => null]);
		}

		if (!empty($filter['q'])) {
			$query->andWhere(
							(new Criteria())
											->where('name', 'LIKE', '%' . $filter['q'] . '%')
			);
		}

		return parent::filter($query, $filter);
	}

}
