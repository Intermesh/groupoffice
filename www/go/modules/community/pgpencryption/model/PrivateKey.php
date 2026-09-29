<?php

namespace go\modules\community\pgpencryption\model;

use go\core\db\Criteria;
use go\core\jmap\Entity;
use go\core\orm\Filters;
use go\core\orm\Mapping;

class PrivateKey extends Entity
{
	public $id;

	public $key;

	public $createdAt;

	public $userId;

	public $email;

	public $publicKeyId;

	public $expiresAt;

	protected static function defineMapping(): Mapping
	{
		return parent::defineMapping()
			->addTable("pgp_private_key", "pgpprivate");
	}

	protected static function defineFilters(): Filters
	{
		return parent::defineFilters()
			->add("publicKeyId", function (Criteria $criteria, $value) {
				$criteria->andWhere("publicKeyId", "=", $value);
			});
	}
}