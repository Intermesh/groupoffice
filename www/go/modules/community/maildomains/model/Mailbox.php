<?php

namespace go\modules\community\maildomains\model;

use go\core\acl\model\AclItemEntity;
use go\core\db\Criteria;
use go\core\exception\Forbidden;
use go\core\fs\Folder;
use go\core\model\User;
use go\core\orm\Filters;
use go\core\orm\Mapping;
use go\core\orm\SearchableTrait;
use go\core\util\DateTime;
use go\core\util\StringUtil;
use go\core\validate\ErrorCode;

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
	private $domain = null;

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

	/**
	 * @return void
	 * @throws Forbidden
	 * @throws \Exception
	 */
	protected function internalValidate()
	{
		$d = $this->getDomain();
		if($this->isModified('password') && strlen($this->password) > 0 && strlen($this->password) < go()->getSettings()->passwordMinLength) {
			if(strlen($this->plainPassword) < go()->getSettings()->passwordMinLength) {
				$this->setValidationError('password', ErrorCode::INVALID_INPUT, "Minimum password length is ".go()->getSettings()->passwordMinLength." chars");
			}
		}

		$this->checkQuota(); // TODO

		if(!empty($d->maxMailboxes) && $this->isNew() && count($d->mailboxes) + 1 > $d->maxMailboxes) {
			throw new Forbidden('The maximum number of mailboxes for this domain has been reached.');
		}
		parent::internalValidate();
	}

	protected function internalSave(): bool
	{
		if ($this->isModified('password') && strlen($this->password)) {
			$this->password = $this->crypt($this->password);
		}

		if($this->isModified('quota') && $this->quota > 0) {
			$this->quota *= 1024;
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
		$salt=substr(base64_encode(openssl_random_pseudo_bytes(17)),0,22);
		/* As blowfish takes a salt with the alphabet ./A-Za-z0-9 we have to
		 * replace any '+' in the base64 string with '.'. We don't have to do
		 * anything about the '=', as this only occurs when the b64 string is
		 * padded, which is always after the first 22 characters.
		 */
		$salt=str_replace("+",".",$salt);
		/* Next, create a string that will be passed to crypt, containing all
		 * of the settings, separated by dollar signs
		 */

		//$5$ will use CRYPT_SHA256
		$param='$5$rounds=5000$'. $salt; //add the salt

		//now do the actual hashing
		return crypt($password,$param);
	}

	/**
	 * @inheritDoc
	 */
	public function historyLog(): bool|array
	{
		$log = parent::historyLog();

		if(isset($log['password'])) {
			if(isset($log['password'][0])) {
				$log['password'][0] = "MASKED";
			}

			if(isset($log['password'][1])) {
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
		$vmail = empty(go()->getConfig()['vmail_path']) ? '/var/mail/vhosts/' :  go()->getConfig()['vmail_path'];
		return new Folder($vmail . $this->maildir);
	}


	/**
	 * @todo: Do we still need this?
	 * @return bool
	 * @throws \Exception
	 */
	public function cacheUsage()
	{
		$this->usage = $this->active ? $this->getUsageFromDovecot() : false;

		if($this->usage === false) {
			$folder = $this->getMaildirFolder();
			$this->usage = $folder->exists() ? $folder->calculateSize() / 1024 : 0;
		}

		return $this->save();
	}


	/**
	 * See function name. Speaks for itself
	 *
	 * @todo: see above: do we still need this?
	 * @return false|int
	 */
	private function getUsageFromDovecot() :false|int
	{
		exec("doveadm quota get -u " . escapeshellarg($this->username) . " 2>/dev/null", $output, $return);

		/**
		 * returns:
		 * Quota name Type      Value    Limit                                                                     %
		User quota STORAGE 9547844 10240000                                                                    93
		User quota MESSAGE   81592        -                                                                     0
		 */

		if ($return != 0) {
			return false;
		}

		if (!isset($output[0])) {
			return false;
		}
		array_shift($output);
		foreach($output as $line) {
			if(preg_match("/STORAGE\s+([0-9]*)/", $line, $matches)) {
				return (int) $matches[1];
			}
		}

		return false;
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
			if (empty($this->quota)) {
				$this->setValidationError('quota', ErrorCode::FORBIDDEN ,
					'You are not allowed to disable mailbox quota');
			}
			if ($this->isNew() || $this->isModified("quota")) {

				$currentQuota = $this->getOldValue("quota") ?? 0;

				$existingQuota = $this->isNew() ? 0 : $currentQuota;

				$sumUsedQuotaOtherwise = $this->domain->getSumUsedQuota() - $existingQuota; // Domain's used quota w/o the current mailbox's quota.
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
	 */
	private function getDomain(): Domain|null
	{
		if(is_null($this->domain) && isset($this->domainId)) {
			$this->domain = Domain::findById($this->domainId);
		}
		return $this->domain;
	}
}