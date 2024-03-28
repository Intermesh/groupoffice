<?php

namespace go\modules\community\maildomains\model;

use go\core\orm\Mapping;
use go\core\orm\Property;
use go\core\util\DateTime;

final class Alias extends Property
{
	/** @var int */
	public $domainId;

	/** @var string */
	public $address;

	/** @var string */
	public $goto;

	/** @var int */
	public $createdBy;

	/** @var DateTime */
	public $createdAt;

	/** @var int */
	public $modifiedBy;

	/** @var DateTime */
	public $modifiedAt;

	/** @var bool */
	public $active = true;

	/**
	 * @inheritDoc
	 */
	protected static function defineMapping(): Mapping
	{
		return parent::defineMapping()
			->addTable("community_maildomains_alias");
	}

}