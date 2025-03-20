<?php

namespace go\modules\community\email;



use go\core\jmap\Entity;

class Mailbox extends Entity {

	const mayReadItems = 1;
	const mayAddItems = 2;
	const mayRemoveItems = 4;
	const mayCreateChild = 8;
	const mayRename = 16;

	/** User-visible name for the Mailbox, e.g., “Inbox”. */
	public ?string $name;
	/** The Mailbox id for the parent of this Mailbox, or null if this Mailbox is at the top leve */
	public ?int $parentId;
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
	protected ?string $uid;
	protected ?string $highestmodseq;

	public function __construct()
	{
		if (isset($this->role)) {
			$this->role = Role::get($this->role);
		}
	}

	public function qresync() {
		return [
			$this->uidvalidity,			// UIDVALIDITY
			$this->highestmodseq		// last known modseq
			// optional set of known UIDs
			// optional list of known sequence ranges
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
			->select('count(id)')
			->leftJoin('email_map m', 't.id = m.emailId')
			->where(['mailboxId' => $this->id]);

		return (int) $query->scalar();
	}

	public function addMessage($message) {
		//insert intio message map
	}

	public function setFlags($flags) {
		if (strtoupper($this->name) === 'INBOX') {
			$this->role = Role::Inbox;
			return;
		}
		//$this->flags = $flags;
		foreach ($flags as $flag) {
			if (isset(Role::$map[strtolower($flag)])) {
				$role = Role::$map[strtolower($flag)];
				$this->role = Role::fromString($role);
				break;
			}
		}
	}

}
