<?php

namespace go\modules\community\maildomains\model;

use go\core\acl\model\AclItemEntity;
use go\core\db\Criteria;
use go\core\db\Query;
use go\core\exception\Forbidden;
use go\core\fs\Folder;
use go\core\orm\Filters;
use go\core\orm\Mapping;
use go\core\orm\SearchableTrait;
use go\core\util\DateTime;
use go\core\util\StringUtil;
use go\core\validate\ErrorCode;
use go\modules\community\maildomains\convert\Spreadsheet;

final class Mailbox extends AclItemEntity
{
	const EVENT_PASSWORD_VERIFIED = 'passwordverified';

	public ?string $id;
	public string $domainId;
	public string $username;
	protected ?string $password;


	/**
	 * When enabled this user can login to all mailboxes of the domain using user@example.com*thisuser@example.com
	 * @var bool
	 */
	public bool $domainOwner = false;

	/**
	 * Enable this account for SMTP
	 *
	 * @var bool
	 */
	public bool $smtpAllowed;

	/**
	 * Enable Full Text Search indexing for this mailbox
	 *
	 * @var bool
	 */
	public bool $fts;
	public ?string $description;
	public string $maildir;
	public string $homedir;

	/**
	 * Quota in bytes
	 */
	public float $quota;

	/**
	 * Auto expunge in this period.
	 *
	 * Use "0" to disable
	 *
	 * @link https://doc.dovecot.org/settings/types/#time
	 * @var string
	 */
	public string $autoExpunge = "30d";
	public int $createdBy;
	public \DateTimeInterface $createdAt;
	public ?int $modifiedBy;
	public ?\DateTimeInterface $modifiedAt;
	public bool $active = true;

	/**
	 * Usage in bytes
	 *
	 * @var int|null
	 */
	public ?int $bytes;
	public ?int $messages;

	private ?string $plainPassword = null;
	private ?Domain $domain;

	/**
	 * @inheritDoc
	 */
	protected static function defineMapping(): Mapping
	{
		return parent::defineMapping()
			->addTable("community_maildomains_mailbox", 'm');
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
			->add("username", function (Criteria $criteria, $value) {
				$criteria->andWhere('username', '=', $value);
			})
			->add("domainId", function (Criteria $criteria, $value) {
				$criteria->andWhere('domainId', '=', $value);
			});
	}


	/**
	 * @inheritDoc
	 */
	protected static function textFilterColumns(): array
	{
		return ['description', 'username'];
	}

	/**
	 * @inheritDoc
	 */
	public function title(): string
	{
		return $this->username;
	}

	/**
	 * @return void
	 * @throws Forbidden
	 * @throws \Exception
	 */
	protected function internalValidate()
	{
		$d = $this->getDomain();

		if($this->isNew() && !$this->isModified(['quota'])) {
			$this->quota = $d->defaultQuota;
		}

		if (isset($this->plainPassword)) {
			if (strlen($this->plainPassword) < go()->getSettings()->passwordMinLength) {
				$this->setValidationError('password', ErrorCode::INVALID_INPUT, "Minimum password length is " . go()->getSettings()->passwordMinLength . " chars");
			}
		}

		$this->checkQuota();

		if (!empty($d->maxMailboxes) && $this->isNew() && $d->countMailboxes() + 1 > $d->maxMailboxes) {
			throw new Forbidden('The maximum number of mailboxes for this domain has been reached.');
		}

		parent::internalValidate();
	}

	protected function internalSave(): bool
	{
		if (isset($this->plainPassword)) {
			$this->password = $this->crypt($this->plainPassword);
		}

		if ($this->isNew() || empty($this->homedir)) {
			$d = $this->getDomain();
			$parts = explode('@', $this->username);
			$this->homedir = $d->domain . '/' . $parts[0] . '/';
			$this->maildir = $d->domain . '/' . $parts[0] . '/Maildir/';
		}

		return parent::internalSave();
	}


	/**
	 * Trigger a change on the domain upon deleting a mailbox
	 *
	 * @param \go\core\orm\Query $query
	 * @return bool
	 * @throws \Exception
	 */
	protected static function internalDelete(\go\core\orm\Query $query): bool
	{
		$deleteQuery = clone $query;
		$deleteQuery->selectSingleValue('domainId');
		Domain::entityType()->changes($deleteQuery->all());
		return parent::internalDelete($query);
	}


	/**
	 * Encrypt a mailbox password
	 *
	 * Shamelessly stolen from the ActiveRecord version of this model. :-P
	 *
	 * @param string $password
	 * @return string
	 */

	private function crypt(string $password): string
	{
		/* To generate the salt, first generate enough random bytes. Because
         * base64 returns one character for each 6 bits, the we should generate
         * at least 22*6/8=16.5 bytes, so we generate 17. Then we get the first
         * 22 base64 characters
         */
		$salt = substr(base64_encode(openssl_random_pseudo_bytes(17)), 0, 22);
		/* As blowfish takes a salt with the alphabet ./A-Za-z0-9 we have to
		 * replace any '+' in the base64 string with '.'. We don't have to do
		 * anything about the '=', as this only occurs when the b64 string is
		 * padded, which is always after the first 22 characters.
		 */
		$salt = str_replace("+", ".", $salt);
		/* Next, create a string that will be passed to crypt, containing all
		 * of the settings, separated by dollar signs
		 */

		//$5$ will use CRYPT_SHA256
		$param = '$5$rounds=5000$' . $salt; //add the salt

		//now do the actual hashing
		return crypt($password, $param);
	}

	/**
	 * @inheritDoc
	 */
	public function historyLog(): bool|array
	{
		$log = parent::historyLog();

		if (isset($log['password'])) {
			if (isset($log['password'][0])) {
				$log['password'][0] = "MASKED";
			}

			if (isset($log['password'][1])) {
				$log['password'][1] = "MASKED";
			}
		}

		return $log;
	}


	/**
	 * Get the filesystem folder with mail data.
	 *
	 * @return Folder
	 */
	public function getMaildirFolder()
	{
		$vmail = empty(go()->getConfig()['vmail_path']) ? '/var/mail/vhosts/' : go()->getConfig()['vmail_path'];
		return new Folder($vmail . $this->maildir);
	}


	/**
	 * Save mailbox usage from dovecot input
	 *
	 * @return bool
	 * @throws \Exception
	 */
	public function cacheUsage()
	{
		$this->usage = $this->active ? $this->getUsageFromDovecot() : false;

		if ($this->usage === false) {
			$folder = $this->getMaildirFolder();
			$this->usage = $folder->exists() ? $folder->calculateSize() / 1024 : 0;
		}

		return $this->save();
	}


	/**
	 * Check current quota for mailbox and max quota for domain
	 *
	 * Refactored from old postfix module as per new PHP framework.
	 *
	 * @throws \Exception
	 */
	private function checkQuota()
	{
		$d = $this->getDomain();
		$totalQuota = $d->totalQuota;
		if (!empty($totalQuota)) {
			if(empty($this->quota)) {
				$this->quota = $d->defaultQuota;
			}
			if (empty($this->quota)) {
				$this->setValidationError('quota', ErrorCode::FORBIDDEN,
					'You are not allowed to disable mailbox quota');
			}
			if ($this->isNew() || $this->isModified("quota")) {

				$currentQuota = $this->getOldValue("quota") ?? 0;

				$existingQuota = $this->isNew() ? 0 : $currentQuota;

				$sumUsedQuotaOtherwise = $this->getDomain()->getSumUsedQuota() - $existingQuota; // Domain's used quota w/o the current mailbox's quota.
				if ($sumUsedQuotaOtherwise + $this->quota > $totalQuota) {
					$quotaLeft = $totalQuota - $sumUsedQuotaOtherwise;
					throw new \Exception('The maximum quota has been reached. You have ' .
						StringUtil::localizeNumber($quotaLeft / 1024, 0) . 'MB left');
				}
			}
		}
	}


	/**
	 * As the ORM does currently not support retrieving its owner entity through a relation, we simply retrieve the entity by ID
	 * @return Domain|null
	 * @throws \Exception
	 */
	private function getDomain(): Domain|null
	{
		if(!isset($this->domainId) && isset($this->username)) {
			$domain = explode("@", $this->username)[1];

			$this->domain = Domain::find()->where(['domain' => $domain])->single();

			if($this->domain) {
				$this->domainId = $this->domain->id;
			}
		}

		if (!isset($this->domain) && isset($this->domainId)) {
			$this->domain = Domain::findById($this->domainId);
		}
		return $this->domain;
	}

	public function plainPassword(): string | null
	{
		return $this->plainPassword;
	}

	public function setPassword(string $password): void
	{
		$this->plainPassword = $password;
	}

	public function getPassword() {
		return null;
	}

	/**
	 * @inheritDoc
	 */
	public static function converters(): array
	{
		return array_merge(parent::converters(), [Spreadsheet::class]);
	}

}