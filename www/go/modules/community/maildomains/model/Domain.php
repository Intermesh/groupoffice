<?php

namespace go\modules\community\maildomains\model;

use go\core\acl\model\AclOwnerEntity;
use go\core\db\Criteria;
use go\core\orm\Filters;
use go\core\orm\Mapping;
use go\core\orm\SearchableTrait;
use go\core\util\DateTime;

class Domain extends AclOwnerEntity
{
	use SearchableTrait;

	/** @var int */
	public $id;

	/** @var int */
	public $userId;

	/** @var string */
	public $domain;

	/** @var string */
	public $description;

	/** @var int >= 0 */
	public $maxAliases = 0;

	/** @var int */
	public $maxMailboxes = 0;

	/** @var int */
	public $totalQuota = 0;

	/** @var int */
	public $defaultQuota = 0;

	/** @var string */
	public $transport;

	/** @var bool */
	public $backupMx = false;

	/** @var int */
	public $createdBy;

	/** @var DateTime */
	public $createdAt;

	/** @var int */
	public $modifiedBy;

	/** @var DateTime */
	public $modifiedAt;

	/** @var boolean */
	public $active = true;

	public $aliases;

	public $mailboxes;

	/**
	 * @inheritDoc
	 */
	protected static function defineMapping(): Mapping
	{
		return parent::defineMapping()
			->addTable('community_maildomains_domain', 'cmd')
			->addScalar('aliases', 'community_maildomains_alias', ['id' => 'domainId'])
			->addScalar('mailboxes', 'community_maildomains_mailbox', ['id' => 'domainId']);
	}

	/**
	 * Prevent conflict with the old Postfix Admin module
	 *
	 * @return string
	 */
	public static function getClientName(): string
	{
		return "MailDomain";
	}


	/**
	 * @return array|null
	 */
	protected function getSearchKeywords(): ?array
	{
		return [$this->domain, $this->description];
	}

	/**
	 * @inheritDoc
	 */
	protected static function defineFilters(): Filters
	{
		return parent::defineFilters()
			->add('id', function(Criteria $criteria, $value) {
				if(!empty($value)) {
					$criteria->where(['id' => $value]);
				}
			});
	}

	/**
	 * @inheritDoc
	 */
	protected function getSearchDescription() : string
	{
		return $this->domain;
	}

	/**
	 * @inheritDoc
	 */
	protected static function textFilterColumns(): array
	{
		return ['domain', 'description'];
	}

	/**
	 * @inheritDoc
	 */
	public function title(): string
	{
		return $this->domain;
	}
}