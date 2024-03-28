<?php

namespace go\modules\community\maildomains\model;

use go\core\orm\Mapping;
use go\core\orm\Property;
use go\core\util\DateTime;

final class Mailbox extends Property
{
	/** @var int */
	public $id;

	/** @var int */
	public $domainId;

	/** @var string */
	public $username;

	/** @var string */
	public $password;

	/** @var bool */
	public $smtpAllowed;

	/** @var string */
	public $name;

	/** @var string */
	public $maildir;

	/** @var string */
	public $homedir;

	/** @var int */
	public $quota;

	/** @var int */
	public $createdBy;

	/** @var DateTime */
	public $createdAt;

	/** @var int */
	public $modifiedBy;

	/** @var DateTime */
	public $modifiedAt;

	/** @var bool  */
	public $active = true;

	/** @var int */
	public $usage;

	/**
	 * @inheritDoc
	 */
	protected static function defineMapping(): Mapping
	{
		return parent::defineMapping()
			->addTable("community_maildomains_mailbox");
	}

}