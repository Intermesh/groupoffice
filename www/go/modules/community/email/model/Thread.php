<?php

namespace go\modules\community\email\model;

use go\core\jmap\Entity;
use go\core\orm\Mapping;
use go\core\orm\Relation;

class Thread extends Entity {

	public ?int $id;

	public ?int $accountId;
	protected ?string $subjectHash;
	public ?array $emailIds;

	const RE_REGEX = '/^[ \t]*[A-Za-z0-9]+:/g';
	const LISTNAME_REGEX = '/^[ \t]*\[[^]]+\]/g';
	const SECURITY_REGEX = '/[\[(SEC|DLM)=[^]]+\][ \t]*$/g';
	const FWD_RE_REGEX = "/([\[(] *)?(RE?S?|FYI|RIF|I|FS|VB|RV|ENC|ODP|PD|YNT|ILT|SV|VS|VL|AW|WG|ΑΠ|ΣΧΕΤ|ΠΡΘ|FWD?) *([-:;)\]][ :;\])-]*|$)|]+ *$/im";

	static protected function defineMapping(): Mapping {
		return parent::defineMapping()
			->addTable("email_thread")
			->add('emailIds', Relation::scalar('emailIds','email_email')->keys(['threadId' => 'id']));
	}

	static public function normalizeSubject($subject) {
		$stripped = preg_replace(self::FWD_RE_REGEX, '', $subject);
		return trim($stripped);
	}

	public function setSubject($val) {
		$this->subjectHash = sha1(self::normalizeSubject($val));
	}

	/**
	 * Find with thread this email belongs to or create a new thread.
	 *
	 * @param Email $message
	 * @return int
	 * @throws \Exception
	 */
	static public function byMessage(Email $message) {
		$in = (array)$message->messageId +
			(array)$message->references +
			(array)$message->inReplyTo;
		$subjectHash = sha1(self::normalizeSubject($message->subject));
		if (!empty($in)) {
			$thread = Email::find()
				->select(['id' => 'threadId'])->distinct()
				->join('email_id','ids', 'ids.fk = e.id')
				->join('email_thread', 'thr', 'threadId = thr.id','LEFT')
				->where(['thr.subjectHash' => $subjectHash])
				->andWhere('ids.messageId', '=', array_keys($in))->single();
		}
		if (empty($thread)) {
			$thread = new self();
			$thread->subjectHash = $subjectHash;
			$thread->accountId = $message->accountId;
			$thread->save();
		}
		return $thread->id;
	}

}