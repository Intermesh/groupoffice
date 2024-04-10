<?php

namespace go\modules\community\maildomains\model;

use go\core\acl\model\AclOwnerEntity;
use go\core\db\Criteria;
use go\core\orm\Filters;
use go\core\orm\Mapping;
use go\core\orm\Query;
use go\core\orm\SearchableTrait;
use go\core\util\DateTime;

final class Domain extends AclOwnerEntity
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

	/** @var int */
	public $numAliases;

	/** @var int */
	public $numMailboxes;

	/** @var int */
	public $sumUsedQuota;

	/** @var int */
	public $sumUsage;

	/*
	// TODO: default attributes!
	public function defaultAttributes()
	{
		$attr = parent::defaultAttributes();
		$attr['total_quota']=1024*1024*10;//10 GB of quota per domain by default.
		$attr['default_quota']=1024*512; //512 MB of default quota
		return $attr;
	}
	*/

	/**
	 * @inheritDoc
	 */
	protected static function defineMapping(): Mapping
	{
		return parent::defineMapping()
			->addTable('community_maildomains_domain', 'cmd')
			->addQuery((new Query())->select('SUM(COALESCE(`cmm`.`quota`,0)) as `sumUsedQuota`, SUM(COALESCE(`cmm`.`usage`,0)) as `sumUsage`')
				->join('community_maildomains_mailbox', 'cmm', '`cmd`.`id`=`cmm`.`domainId`', 'LEFT')
				->groupBy(['`cmm`.`domainId`']))
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

	public function getSumUsedQuota(): int
	{
		return $this->sumUsedQuota;
	}

	public function getSumUsage(): int
	{
		return $this->sumUsage;
	}


	public function getNumAliases(): int
	{
		return count($this->aliases);

	}

	public function getNumMailboxes(): int
	{
		return count($this->mailboxes);
	}

	// TODO? Import / Export?

	/*
	 * 	public function import($data) {
		$mailboxes = $data['mailboxes'];
		$aliases = $data['aliases'];

		unset($data['mailboxes']);
		unset($data['aliases']);

		$data['total_quota']=$data['max_mailboxes']=$data['max_aliases']=0;

		$this->setAttributes($data, false);

		if(!$this->save()){
			throw new \Exception("couldnt save domain");
		}

		foreach($mailboxes as $mailboxAttr){
			$mailbox = new Mailbox();
			$mailbox->setAttributes($mailboxAttr, false);
			$mailbox->domain_id = $this->id;
			$mailbox->skipPasswordEncryption = true;
			if(!$mailbox->save()) {
				echo "Failed to save mailbox: ".var_export($mailbox->getValidationErrors(), true)."\n\n";
			}
		}


		foreach($aliases as $aliasAttr){
			$alias = new Alias();
			$alias->setAttributes($aliasAttr, false);
			$alias->domain_id = $this->id;


			if(!$alias->save()) {
				echo "Failed to save alias: ".var_export($alias->getValidationErrors(), true)."\n\n";
			}
		}

		return true;
	}
	 */
}