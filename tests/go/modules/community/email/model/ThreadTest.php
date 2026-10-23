<?php

namespace go\modules\community\email\model;

use go\core\orm\EntityType;
use go\modules\community\email\Module;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class ThreadTest extends TestCase {

	private static int $accountId;
	private static int $otherAccountId;
	private static int $uid = 0;

	public static function setUpBeforeClass(): void {
		$module = Module::get();
		if (!$module->isInstalled()) {
			self::assertNotFalse($module->install(), "Could not install email module");
		}
		self::$accountId = self::createAccount('thread@intermesh.localhost');
		self::$otherAccountId = self::createAccount('other@intermesh.localhost');
	}

	/**
	 * Raw insert because saving an EmailAccount connects to the IMAP server
	 */
	private static function createAccount(string $email): int {
		$db = go()->getDbConnection();
		$aclId = $db->selectSingleValue('id')->from('core_acl')->orderBy(['id' => 'ASC'])->single();
		$db->insert('email_account', [
			'name' => $email,
			'email' => $email,
			'mtaDsn' => '',
			'mdaDsn' => '',
			'mdaCapabilities' => '[]',
			'createdAt' => '2026-10-01 00:00:00',
			'aclId' => $aclId
		])->execute();
		return (int)$db->getPDO()->lastInsertId();
	}

	/**
	 * Index a message like ImapBackend does: resolve the thread first, then store the email and its ids
	 */
	private function deliver(string $messageId, array $references = [], array $inReplyTo = [], string $subject = 'Hello', string $receivedAt = '2026-10-01 10:00:00', ?int $accountId = null): int {
		$accountId ??= self::$accountId;

		$email = new Email();
		$email->accountId = $accountId;
		$email->messageId = [$messageId];
		$email->references = $references;
		$email->inReplyTo = $inReplyTo;
		$email->subject = $subject;
		$email->receivedAt = new \DateTime($receivedAt);

		$threadId = Thread::byMessage($email);

		$db = go()->getDbConnection();
		$db->insert('email_email', [
			'uid' => ++self::$uid,
			'threadId' => $threadId,
			'subject' => $subject,
			'receivedAt' => $receivedAt,
			'accountId' => $accountId
		])->execute();
		$id = (int)$db->getPDO()->lastInsertId();

		foreach (['messageId' => [$messageId], 'references' => $references, 'inReplyTo' => $inReplyTo] as $type => $ids) {
			foreach ($ids as $value) {
				$db->insert('email_id', ['fk' => $id, 'type' => $type, 'messageId' => $value])->execute();
			}
		}
		return $id;
	}

	private function threadOf(string $messageId): int {
		return (int)go()->getDbConnection()
			->selectSingleValue('e.threadId')
			->from('email_email', 'e')
			->join('email_id', 'i', 'i.fk = e.id')
			->where(['i.type' => 'messageId', 'i.messageId' => $messageId])
			->single();
	}

	private function emailIdOf(string $messageId): int {
		return (int)go()->getDbConnection()
			->selectSingleValue('fk')
			->from('email_id')
			->where(['type' => 'messageId', 'messageId' => $messageId])
			->single();
	}

	private function threadExists(int $threadId): bool {
		return (bool)go()->getDbConnection()
			->selectSingleValue('id')
			->from('email_thread')
			->where(['id' => $threadId])
			->single();
	}

	public static function subjectProvider(): array {
		return [
			['Hello', 'hello', false],
			['Re: Hello', 'hello', true],
			['RE: re: Hello', 'hello', true],
			['Fwd: Re: Hello', 'hello', true],
			['Re[2]: Hello', 'hello', true],
			['Re(3): Hello', 'hello', true],
			['[list] Re: Hello', 'hello', true],
			['Re: [list] Hello', 'hello', true],
			['[list] Hello', 'hello', false],
			['Hello (fwd)', 'hello', true],
			['[Fwd: Re: Hello]', 'hello', true],
			['AW: Antw: Odp: SV: Hello', 'hello', true],
			['PŘ: Hello', 'hello', true],
			['Re:   Hello    world  ', 'hello world', true],
			['Hello [SEC=OFFICIAL]', 'hello', false],
			['Report: status', 'report: status', false],
			['Resume: status', 'resume: status', false],
			['[urgent]', '[urgent]', false],
			['', '', false],
		];
	}

	#[DataProvider('subjectProvider')]
	public function testNormalizeSubject(string $subject, string $expected, bool $expectedIsReply) {
		$this->assertSame($expected, Thread::normalizeSubject($subject, $isReply));
		$this->assertSame($expectedIsReply, $isReply);
	}

	public function testReplyWithChangedSubjectJoinsParent() {
		$this->deliver('a1@test', [], [], 'Quarterly numbers');
		$this->deliver('a2@test', ['a1@test'], ['a1@test'], 'Re: Quarterly numbers (corrected)');

		$this->assertSame($this->threadOf('a1@test'), $this->threadOf('a2@test'));
	}

	public function testReferencesWithoutInReplyTo() {
		$this->deliver('b1@test', [], [], 'Topic');
		$this->deliver('b2@test', ['b1@test'], [], 'Re: Topic');

		$this->assertSame($this->threadOf('b1@test'), $this->threadOf('b2@test'));
	}

	public function testAngleBracketsAreIgnored() {
		$this->deliver('c1@test', [], [], 'Brackets');
		$this->deliver('c2@test', ['<c1@test>'], [], 'Re: Brackets');

		$this->assertSame($this->threadOf('c1@test'), $this->threadOf('c2@test'));
	}

	public function testReplyBeforeOriginal() {
		$this->deliver('d2@test', ['d1@test'], ['d1@test'], 'Re: Late original');
		$this->deliver('d1@test', [], [], 'Late original');

		$this->assertSame($this->threadOf('d2@test'), $this->threadOf('d1@test'));
	}

	public function testSiblingsOfMissingParent() {
		$this->deliver('e2@test', ['e1@test'], ['e1@test'], 'Re: Missing');
		$this->deliver('e3@test', ['e1@test'], ['e1@test'], 'Re: Missing');

		$this->assertSame($this->threadOf('e2@test'), $this->threadOf('e3@test'));
	}

	public function testSameSubjectWithUnrelatedReferencesStaysSeparate() {
		$this->deliver('f1@test', [], [], 'Weekly report');
		$this->deliver('f2@test', ['zz@elsewhere'], ['zz@elsewhere'], 'Re: Weekly report');

		$this->assertNotSame($this->threadOf('f1@test'), $this->threadOf('f2@test'));
	}

	public function testBridgingMessageMergesThreads() {
		// g2 and g4 arrive before the message that connects them
		$this->deliver('g2@test', ['g1@test'], ['g1@test'], 'Re: Bridge');
		$this->deliver('g4@test', ['g3@test'], ['g3@test'], 'Re: Bridge');
		$keep = $this->threadOf('g2@test');
		$merged = $this->threadOf('g4@test');
		$this->assertNotSame($keep, $merged);
		$oldId = $this->emailIdOf('g4@test');

		$db = go()->getDbConnection();
		$db->insert('email_mailbox', ['accountId' => self::$accountId, 'name' => 'INBOX'])->execute();
		$mailboxId = (int)$db->getPDO()->lastInsertId();
		$db->insert('email_map', ['fk' => $oldId, 'mailboxId' => $mailboxId])->execute();
		EntityType::push();
		$stateBefore = Email::getState();
		$threadStateBefore = Thread::getState();

		$this->deliver('g3@test', ['g1@test'], ['g1@test'], 'Re: Bridge');

		$this->assertSame($keep, $this->threadOf('g2@test'));
		$this->assertSame($keep, $this->threadOf('g3@test'));
		$this->assertSame($keep, $this->threadOf('g4@test'));
		$this->assertFalse($this->threadExists($merged));

		// threadId is immutable: the moved email is reinserted with a new id and keeps its data
		$newId = $this->emailIdOf('g4@test');
		$this->assertNotSame($oldId, $newId);
		$this->assertEmpty($db->selectSingleValue('id')->from('email_email')->where(['id' => $oldId])->single());
		$this->assertSame($mailboxId, (int)$db->selectSingleValue('mailboxId')->from('email_map')->where(['fk' => $newId])->single());
		$this->assertSame(['g3@test'], $db->selectSingleValue('messageId')->from('email_id')
			->where(['fk' => $newId, 'type' => 'references'])->all());

		// clients learn about the destroyed and created email and the destroyed thread
		EntityType::push();
		$changes = Email::getChanges($stateBefore, 100);
		$this->assertContains($oldId, array_map('intval', $changes['removed']));
		$this->assertContains($newId, array_map('intval', $changes['changed']));
		$threadChanges = Thread::getChanges($threadStateBefore, 100);
		$this->assertContains($merged, array_map('intval', $threadChanges['removed']));
		$this->assertContains($keep, array_map('intval', $threadChanges['changed']));
	}

	public function testSubjectFallbackForReplyWithoutReferences() {
		$this->deliver('h1@test', [], [], 'Lunch on friday', '2026-10-01 10:00:00');
		$this->deliver('h2@test', [], [], 'RE: Lunch on Friday', '2026-10-02 10:00:00');

		$this->assertSame($this->threadOf('h1@test'), $this->threadOf('h2@test'));
	}

	public function testSubjectFallbackOnlyForReplies() {
		$this->deliver('i1@test', [], [], 'Newsletter', '2026-10-01 10:00:00');
		$this->deliver('i2@test', [], [], 'Newsletter', '2026-10-02 10:00:00');

		$this->assertNotSame($this->threadOf('i1@test'), $this->threadOf('i2@test'));
	}

	public function testSubjectFallbackIgnoresOldThreads() {
		$this->deliver('j1@test', [], [], 'Old topic', '2026-01-01 10:00:00');
		$this->deliver('j2@test', [], [], 'Re: Old topic', '2026-10-01 10:00:00');

		$this->assertNotSame($this->threadOf('j1@test'), $this->threadOf('j2@test'));
	}

	public function testAccountsAreSeparated() {
		$this->deliver('k1@test', [], [], 'Shared');
		$this->deliver('k2@test', ['k1@test'], ['k1@test'], 'Re: Shared', '2026-10-01 10:00:00', self::$otherAccountId);

		$this->assertNotSame($this->threadOf('k1@test'), $this->threadOf('k2@test'));
	}

	public function testDeleteEmpty() {
		$id = $this->deliver('l1@test', [], [], 'Vanishing');
		$threadId = $this->threadOf('l1@test');

		go()->getDbConnection()->delete('email_email', ['id' => $id])->execute();
		Thread::deleteEmpty(self::$accountId);

		$this->assertFalse($this->threadExists($threadId));
	}
}
