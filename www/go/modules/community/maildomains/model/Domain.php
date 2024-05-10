<?php

namespace go\modules\community\maildomains\model;

use go\core\acl\model\AclOwnerEntity;
use go\core\db\Criteria;
use go\core\orm\Filters;
use go\core\orm\Mapping;
use go\core\orm\Query;
use go\core\orm\SearchableTrait;
use go\core\util\ArrayObject;
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

	public $spf;

	public $spfStatus;

	public $mx;

	public $mxStatus;

	public $dmarc;

	public $dmarcStatus;
	public $dkimRecords;

	/** @var int */
	public $numAliases;

	/** @var int */
	public $numMailboxes;

	/** @var int */
	public $sumUsedQuota;

	/** @var int */
	public $sumUsage;

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
			->addScalar('mailboxes', 'community_maildomains_mailbox', ['id' => 'domainId'])
			->addArray('dkimRecords', DkimKey::class, ['id' => 'domainId']);
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
			->add('id', function (Criteria $criteria, $value) {
				if (!empty($value)) {
					$criteria->where(['id' => $value]);
				}
			});
	}

	/**
	 * @inheritDoc
	 */
	protected function getSearchDescription(): string
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


	/**
	 * Upon manually checking DNS settings, update the domain record and DkimKey records as per DNS checks
	 *
	 * @param ArrayObject $record
	 * @throws \Exception
	 */
	public function updateDns(ArrayObject $record)
	{
		$this->mxStatus = $record['mx'];
		$this->mx = implode(", ", $record['mxTargets']);
		$this->spf = $record['spf']; // TODO
		$this->spfStatus = !empty($record['spf']);
		$this->dmarc = $record['dmarc'];
		$this->dmarcStatus = !empty($record['dmarc']);

		if (!count($this->dkimRecords)) {
			$dkimRecord= new DkimKey($this);
			$dkimRecord->domainId = $this->id;
			$dkimRecord->selector = "mail1"; // TODO: make this configurable?
			$dkimRecord->txt = $record['dkim'] ?? "";
			$dkimRecord->status = !empty($record['dkim']);
			$this->dkimRecords[] = $dkimRecord;
		} else {
			// TODO: Dkim should be returned as an array. Therefore, this iteration below will have to be rewritten
			foreach ($this->dkimRecords as $k => $dkimRecord) {
				$dkimRecord->selector = "mail".($k+1);
				$dkimRecord->txt = $record['dkim'] ?? "";;
				$dkimRecord->status = !empty($record['dkim']);
			}
		}
		$this->save();
	}
}