<?php
namespace go\core\jmap;

use go\core\Environment;
use go\core\orm\Mapping;
use go\core\orm\Property;
use go\core\SingletonTrait;
use stdClass;

class Account extends \go\core\orm\Entity {

	protected $id;
	protected $ownerId;

	public $name;

	protected static function defineMapping() : Mapping
	{
		return parent::defineMapping()
			->addTable('email_account', 'a');
	}

	public function isPersonal() {
		return $this->ownerId == go()->getUserId();
	}

	public function getIsReadOnly() {
		return false;
	}

	public function getAccountCapabilities() {

		// only mail in accounts for now
		return [
			"urn:ietf:params:jmap:mail" => [
				"maxMailboxesPerEmail" => 1, // IMAP only support email being in 1 folder
				"maxMailboxDepth" => 10, // sub tree limit
				"maxSizeMailboxName" => 100, // characters for name
				"maxSizeAttachmentsPerEmail" => 10_000_000, // 10MB attachments? fetch from IMAP server
				"emailQuerySortOptions" => [], // supported options for Email/query comparator
				"mayCreateTopLevelMailbox" => true // may create mailbox with parentId = null
			],
			"urn:ietf:params:jmap:submission"=>[
				"maxDelayedSend" => 15,
				"submissionExtensions" => [], // SIZE, DSN, whatever the SMTP server can do.
			],
			"urn:ietf:params:jmap:vacationresponse"=>[]
		];
	}
}