<?php

namespace go\modules\community\email\model;



use go\core\acl\model\AclItemEntity;
use go\core\db\Criteria;
use go\core\jmap\Entity;
use go\core\orm\Filters;
use go\core\orm\Mapping;

class Mailbox extends AclItemEntity {

	public $id;
	/** User-visible name for the Mailbox, e.g., “Inbox”. */
	public ?string $name;
	/** The Mailbox id for the parent of this Mailbox, or null if this Mailbox is at the top leve */
	public ?int $parentId;

	public ?int $accountId;
	/** Identifies Mailboxes that have a particular common purpose */
	public ?string $role;
	/** unsigned Defines the sort order of Mailboxes when presented in the client’s UI, so it is consistent between devices */
	public ?int $sortOrder;

	protected $modNotCountSeq = 0;
	protected $emailHighestModSeq = 0;
	protected $emailListLowModSeq = 0;
	static $specialFlags = ['\\HasChildren', '\\HasNoChildren', '\\NoSelect', '\\NoInferiors'];

	private $delimiter;

	// IMAP sync properties
	protected ?string $highestUID;
	protected ?string $uid; // uidvalidity


	protected static function defineMapping(): Mapping
	{
		return parent::defineMapping()
			->addTable('email_mailbox', 'box')
			->addUserTable('email_mailbox_user', 'mu', ['id' => 'mailboxId']);
	}

	protected static function defineFilters(): Filters
	{
		return parent::defineFilters()
			->add('parentId', function(Criteria $criteria, $value) {
				$criteria->where('parentId', '=', $value);
			})->add('accountId', function(Criteria $criteria, $value) {
				$criteria->where('accountId', '=', $value);
			});
	}

	public function qresync() {
		return [
			$this->uid,			// UIDVALIDITY
			$this->emailHighestModSeq		// last known modseq
			// optional set of known UIDs
			// optional list of known sequence ranges
		];
	}

	public function myRights() {
		$imap = [
			'mayReadItems' => 'lr', // l read
			'mayAddItems' => 'i', // insert
			'mayRemoveItems' => 'te', // truncate
			'maySetSeen' => 's', // seen
			'maySetKeywords' => 'w', // write
			'mayCreateChild' => 'k', // subfolders
			'mayRename' => 'x', // change folder name
			'mayDelete' => 'x',
			'maySubmit' => 'p' // append items
		];
	}

	public function setDelimiter($val) {
		$this->delimiter = $val;
	}

	public function setHighestUID($val) {
		$this->highestUID = $val;
	}

	public function imapSyncProps($uidNext, $highestModSeq) {

		$this->highestUID = $uidNext-1;
		$this->emailHighestModSeq = $highestModSeq;
		return $this;
	}

	public function highestUID() {
		return $this->highestUID;
	}
	public function highestModSeq() {
		return $this->emailHighestModSeq;
	}
	public function uidNext() {
		return $this->highestUID+1;
	}
	public function setUid($uid) {
		$this->uid = $uid;
	}
	public function uid() {
		return $this->uid;
	}

	public function getTotalEmails() {
		$query = Email::find()
			->selectSingleValue('count(id)')
			->join('email_map', 'm', 'e.id = m.fk', 'LEFT')
			->where(['mailboxId' => $this->id]);

		return (int) $query->single();
	}

	public function addMessage($message) {
		//insert intio message map
	}


	protected static function aclEntityClass(): string
	{
		return EmailAccount::class;
	}

	protected static function aclEntityKeys(): array
	{
		return ['accountId'=>'id'];
	}
}
