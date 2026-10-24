<?php

namespace go\modules\community\email\model;

use go\core\acl\model\AclItemEntity;
use go\core\db\Query;
use go\core\orm\EntityType;
use go\core\orm\Mapping;
use go\core\orm\Relation;
use PDO;

/**
 * A conversation of emails (JMAP Mail, RFC 8621 section 3)
 *
 * Emails are grouped by their Message-ID, In-Reply-To and References headers like the REFERENCES
 * algorithm of RFC 5256 (JWZ threading). The base subject is only used as a fallback for replies that
 * carry no reference headers at all.
 */
class Thread extends AclItemEntity {

	public ?int $id;

	public ?int $accountId;
	protected ?string $subjectHash;
	public ?array $emailIds;

	/** Reply and forward prefixes (RFC 5256 subj-refwd plus common localised variants) */
	const PREFIX_REGEX = '/^(?:\[[^\[\]]*\][ \t]*)*(?:re|res|rif|r|aw|antw|sv|vs|vl|odp|ynt|ilt|απ|σχετ|fwd?|fs|wg|vb|rv|enc|tr|i|pd|př|doorst|ΠΡΘ|fyi)[ \t]*(?:\[[^\[\]]*\]|\(\d+\)|\^\d+)?[ \t]*[:：][ \t]*/iu';
	/** A leading mailing list tag like "[listname]" (RFC 5256 subj-blob) */
	const BLOB_REGEX = '/^\[[^\[\]]*\][ \t]*(?=\S)/u';
	/** Trailing "(fwd)" (RFC 5256 subj-trailer) */
	const TRAILER_REGEX = '/[ \t]*\(fwd\)[ \t]*$/iu';
	/** Trailing security classification marker like "[SEC=UNCLASSIFIED]" */
	const SECURITY_REGEX = '/[ \t]*\[(?:SEC|DLM)=[^\[\]]+\][ \t]*$/iu';
	/** Subject wrapped like "[Fwd: subject]" (RFC 5256 subj-fwd) */
	const FWD_WRAP_REGEX = '/^\[fwd:[ \t]*(.*)\]$/iu';

	/** Replies without reference headers only join a thread with the same subject that was active within this many days */
	const SUBJECT_FALLBACK_DAYS = 30;

	static protected function defineMapping(): Mapping {
		return parent::defineMapping()
			->addTable("email_thread")
			->add('emailIds', Relation::scalar('emailIds','email_email')->keys(['threadId' => 'id']));
	}

	/**
	 * Extract the base subject (RFC 5256 section 2.1)
	 *
	 * @param string|null $subject
	 * @param bool|null $isReplyOrForward Set to true when a reply or forward indicator was removed
	 * @return string lowercase base subject
	 */
	static public function normalizeSubject(?string $subject, ?bool &$isReplyOrForward = false): string {
		$isReplyOrForward = false;
		$s = trim(preg_replace('/\s+/u', ' ', (string) $subject));

		do {
			$unwrapped = false;
			// strip trailers
			do {
				$before = $s;
				$s = preg_replace(self::SECURITY_REGEX, '', $s);
				$s = preg_replace(self::TRAILER_REGEX, '', $s, -1, $count);
				if ($count) {
					$isReplyOrForward = true;
				}
				$s = trim($s);
			} while ($s !== $before);

			// strip leaders and list tags
			do {
				$before = $s;
				$s = preg_replace(self::PREFIX_REGEX, '', $s, 1, $count);
				if ($count) {
					$isReplyOrForward = true;
				}
				$s = preg_replace(self::BLOB_REGEX, '', $s, 1);
				$s = trim($s);
			} while ($s !== $before);

			if (preg_match(self::FWD_WRAP_REGEX, $s, $matches)) {
				$s = trim($matches[1]);
				$isReplyOrForward = true;
				$unwrapped = true;
			}
		} while ($unwrapped);

		return mb_strtolower($s);
	}

	static public function hashSubject(?string $subject): string {
		return sha1(self::normalizeSubject($subject));
	}

	public function setSubject($val) {
		$this->subjectHash = self::hashSubject($val);
	}

	/**
	 * Find the thread this email belongs to or create a new one.
	 *
	 * The email is linked to all existing emails of the account that share a Message-ID, In-Reply-To or References
	 * id with it. This covers the parent, the replies that arrived before it and siblings that reference the same
	 * (possibly missing) ancestor. When the email bridges multiple threads they are merged into the oldest one.
	 *
	 * Must be called before the email is saved.
	 *
	 * @param Email $message
	 * @return int the thread id
	 * @throws \Exception
	 */
	static public function byMessage(Email $message): int {
		$references = self::cleanIds(array_merge($message->references ?? [], $message->inReplyTo ?? []));
		$ids = self::cleanIds(array_merge($message->messageId ?? [], $references));

		$threadIds = empty($ids) ? [] : go()->getDbConnection()
			->selectSingleValue('DISTINCT e.threadId')
			->from('email_id', 'i')
			->join('email_email', 'e', 'e.id = i.fk')
			->where('e.accountId', '=', $message->accountId)
			->andWhere('i.messageId', 'IN', $ids)
			->orderBy(['e.threadId' => 'ASC'])
			->all();

		$subjectHash = sha1(self::normalizeSubject($message->subject, $isReplyOrForward));

		if (empty($threadIds) && empty($references) && $isReplyOrForward) {
			$threadId = self::findBySubject($message, $subjectHash);
			if ($threadId) {
				$threadIds = [$threadId];
			}
		}

		if (empty($threadIds)) {
			$thread = new self();
			$thread->subjectHash = $subjectHash;
			$thread->accountId = $message->accountId;
			if (!$thread->save()) {
				throw new \Exception("Could not save thread: " . var_export($thread->getValidationErrors(), true));
			}
			return $thread->id;
		}

		$threadId = (int)array_shift($threadIds);
		if (!empty($threadIds)) {
			self::mergeThreads($threadId, array_map('intval', $threadIds));
		} else {
			// emailIds of the thread will change when the message is saved
			self::entityType()->changes([[$threadId, self::accountAclId($message->accountId), false]]);
		}
		return $threadId;
	}

	private static function accountAclId(int $accountId): ?int {
		$aclId = go()->getDbConnection()->selectSingleValue('aclId')->from('email_account')->where(['id' => $accountId])->single();
		return $aclId ? (int)$aclId : null;
	}

	/**
	 * Find the thread with the same base subject that was active closest before the message
	 */
	private static function findBySubject(Email $message, string $subjectHash): ?int {
		// receivedAt is set by the server, sentAt by the sender
		$date = self::toDateTime($message->receivedAt ?? $message->sentAt) ?? new \DateTime();
		$since = (clone $date)->modify('-' . self::SUBJECT_FALLBACK_DAYS . ' days');
		$until = (clone $date)->modify('+1 day');

		$threadId = go()->getDbConnection()
			->selectSingleValue('t.id')
			->from('email_thread', 't')
			->join('email_email', 'e', 'e.threadId = t.id')
			->where('t.accountId', '=', $message->accountId)
			->andWhere('t.subjectHash', '=', $subjectHash)
			->andWhere('e.receivedAt', '>=', $since->format('Y-m-d H:i:s'))
			->andWhere('e.receivedAt', '<=', $until->format('Y-m-d H:i:s'))
			->orderBy(['e.receivedAt' => 'DESC'])
			->single();

		return $threadId ? (int)$threadId : null;
	}

	private static function toDateTime($value): ?\DateTime {
		if ($value instanceof \DateTimeInterface) {
			return \DateTime::createFromInterface($value);
		}
		if (is_string($value) && $value !== '') {
			try {
				return new \DateTime($value);
			} catch (\Exception $e) {
				return null;
			}
		}
		return null;
	}

	private static function cleanIds(array $ids): array {
		$ids = array_map(fn($id) => trim((string)$id, " \t<>"), $ids);
		return array_values(array_unique(array_filter($ids, fn($id) => $id !== '')));
	}

	/**
	 * Merge threads into the target thread.
	 *
	 * The threadId of an Email is immutable in JMAP. RFC 8621 section 3 requires the server to delete and
	 * reinsert the Emails that change thread with a new id, so clients see them destroyed and created.
	 *
	 * @param int $targetId the thread that stays
	 * @param int[] $sourceIds the threads that are merged into the target and destroyed
	 * @throws \Exception
	 */
	public static function mergeThreads(int $targetId, array $sourceIds): void {
		$sourceIds = array_values(array_diff($sourceIds, [$targetId]));
		if (empty($sourceIds)) {
			return;
		}

		$db = go()->getDbConnection();
		$db->beginTransaction();
		try {
			$emails = $db->select('e.id, a.aclId')
				->from('email_email', 'e')
				->join('email_account', 'a', 'a.id = e.accountId')
				->where('e.threadId', 'IN', $sourceIds)
				->fetchMode(PDO::FETCH_ASSOC)
				->all();

			$columns = array_values(array_diff(
				go()->getDatabase()->getTable('email_email')->getColumnNames(), ['id', 'threadId']
			));
			$quoted = implode(', ', array_map(fn($c) => '`' . $c . '`', $columns));

			$emailChanges = [];
			foreach ($emails as $email) {
				$oldId = (int)$email['id'];
				$db->getPDO()
					->prepare("INSERT INTO `email_email` (`threadId`, $quoted) SELECT :threadId, $quoted FROM `email_email` WHERE `id` = :id")
					->execute(['threadId' => $targetId, 'id' => $oldId]);
				$newId = (int)$db->getPDO()->lastInsertId();

				foreach (['email_map' => 'fk', 'email_id' => 'fk', 'email_address' => 'fk', 'email_submission' => 'emailId'] as $table => $column) {
					$db->update($table, [$column => $newId], [$column => $oldId])->execute();
				}
				$db->delete('email_email', ['id' => $oldId])->execute();

				$emailChanges[] = [$oldId, $email['aclId'], true];
				$emailChanges[] = [$newId, $email['aclId'], false];
			}

			// submissions follow their email; clients are told they changed
			$submissions = $db->select('s.id, a.aclId')
				->from('email_submission', 's')
				->join('email_account', 'a', 'a.id = s.accountId')
				->where('s.threadId', 'IN', $sourceIds)
				->fetchMode(PDO::FETCH_ASSOC)
				->all();
			$db->update('email_submission', ['threadId' => $targetId], (new Query)->where('threadId', 'IN', $sourceIds))->execute();
			if (!empty($submissions)) {
				// by name: the EmailSubmission model is still work in progress
				EntityType::findByName('EmailSubmission')
					?->changes(array_map(fn($s) => [(int)$s['id'], $s['aclId'], false], $submissions));
			}
			$db->delete('email_thread', (new Query)->where('id', 'IN', $sourceIds))->execute();

			Email::entityType()->changes($emailChanges);
			$aclId = $db->selectSingleValue('a.aclId')->from('email_thread', 't')
				->join('email_account', 'a', 'a.id = t.accountId')->where(['t.id' => $targetId])->single();
			$threadChanges = [[$targetId, $aclId, false]];
			foreach ($sourceIds as $sourceId) {
				$threadChanges[] = [$sourceId, $aclId, true];
			}
			self::entityType()->changes($threadChanges);

			$db->commit();
		} catch (\Throwable $e) {
			$db->rollBack();
			throw $e;
		}
	}

	/**
	 * Remove threads of the account that no longer contain any email (after emails vanished from the server)
	 */
	public static function deleteEmpty(int $accountId): void {
		$db = go()->getDbConnection();
		$ids = $db->selectSingleValue('t.id')
			->from('email_thread', 't')
			->join('email_email', 'e', 'e.threadId = t.id', 'LEFT')
			->join('email_submission', 's', 's.threadId = t.id', 'LEFT')
			->where('t.accountId', '=', $accountId)
			->andWhere('e.id IS NULL')
			->andWhere('s.id IS NULL')
			->all();

		if (empty($ids)) {
			return;
		}
		$db->delete('email_thread', (new Query)->where('id', 'IN', $ids))->execute();
		$aclId = self::accountAclId($accountId);
		self::entityType()->changes(array_map(fn($id) => [$id, $aclId, true], $ids));
	}

	protected static function aclEntityClass(): string {
		return EmailAccount::class;
	}

	protected static function aclEntityKeys(): array {
		return ['accountId' => 'id'];
	}
}
