<?php

namespace go\modules\community\maildomains\model;

use go\core\acl\model\AclItemEntity;
use go\core\db\Criteria;
use go\core\orm\Filters;
use go\core\orm\Mapping;
use go\core\orm\SearchableTrait;
use go\core\util\DateTime;

final class Mailbox extends AclItemEntity
{
	use SearchableTrait;

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
	protected static function aclEntityClass(): string
	{
		return Domain::class;
	}

	protected static function aclEntityKeys(): array
	{
		return ['domainId' => 'id'];
	}

	public static function getClientName(): string
	{
		return "MailBox";
	}

	/**
	 * @inheritDoc
	 */
	protected static function defineFilters(): Filters
	{
		return parent::defineFilters()
			->add("domainId", function(Criteria $criteria, $value) {
				$criteria->andWhere('domainId', '=', $value);
			});
	}

	/**
	 * @return array|null
	 */
	protected function getSearchKeywords(): ?array
	{
		return [$this->name, $this->username];
	}

	/**
	 * @inheritDoc
	 */
	protected function getSearchDescription() : string
	{
		return $this->name;
	}

	/**
	 * @inheritDoc
	 */
	protected static function textFilterColumns(): array
	{
		return ['name', 'username'];
	}

	/**
	 * @inheritDoc
	 */
	public function title(): string
	{
		return $this->name;
	}

}