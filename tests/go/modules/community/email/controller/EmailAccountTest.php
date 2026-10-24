<?php

namespace go\modules\community\email\controller;

use go\core\auth\TemporaryState;
use go\core\exception\Forbidden;
use go\core\jmap\exception\InvalidArguments;
use go\core\model\User;
use go\modules\community\email\model\Mailbox;
use go\modules\community\email\Module;
use PHPUnit\Framework\TestCase;

class EmailAccountTest extends TestCase {

	private static int $accountId;
	private static int $otherAccountId;
	private static int $otherMailboxId;
	private static int $aclId;
	private static int $readerAclId;
	private static int $userId;

	public static function setUpBeforeClass(): void {
		$module = Module::get();
		if (!$module->isInstalled()) {
			self::assertNotFalse($module->install(), "Could not install email module");
		}

		$user = new User();
		$user->username = 'emailaccounttest';
		$user->setPassword('sdfTgs-Dfgg-$#ddg');
		$user->displayName = 'Email account test';
		$user->email = $user->recoveryEmail = 'emailaccounttest@intermesh.localhost';
		self::assertTrue($user->save(), var_export($user->getValidationErrors(), true));
		self::$userId = (int)$user->id;

		$db = go()->getDbConnection();
		// readable by admins only
		self::$aclId = self::createAcl(1);
		// readable by the test user too
		self::$readerAclId = self::createAcl(1);
		$userGroupId = (int)$db->selectSingleValue('id')->from('core_group')->where(['isUserGroupFor' => self::$userId])->single();
		$db->insert('core_acl_group', ['aclId' => self::$readerAclId, 'groupId' => $userGroupId, 'level' => 10])->execute();

		self::$accountId = self::createAccount('ctrl@intermesh.localhost', self::$aclId);
		self::$otherAccountId = self::createAccount('ctrl-other@intermesh.localhost', self::$readerAclId);
		$db->insert('email_mailbox', ['accountId' => self::$otherAccountId, 'name' => 'INBOX', 'role' => 'inbox'])->execute();
		self::$otherMailboxId = (int)$db->getPDO()->lastInsertId();
	}

	private static function createAcl(int $adminGroupId): int {
		$db = go()->getDbConnection();
		$db->insert('core_acl', ['ownedBy' => 1, 'usedIn' => 'email_account.aclId'])->execute();
		$aclId = (int)$db->getPDO()->lastInsertId();
		$db->insert('core_acl_group', ['aclId' => $aclId, 'groupId' => $adminGroupId, 'level' => 50])->execute();
		return $aclId;
	}

	public static function tearDownAfterClass(): void {
		go()->setAuthState(new TemporaryState(1));
		$db = go()->getDbConnection();
		$db->delete('email_mailbox', ['id' => self::$otherMailboxId])->execute();
		$db->delete('email_account', ['id' => [self::$accountId, self::$otherAccountId]])->execute();
		$db->delete('core_acl', ['id' => [self::$aclId, self::$readerAclId]])->execute();
		User::delete(['id' => self::$userId]);
	}

	protected function tearDown(): void {
		go()->setAuthState(new TemporaryState(1));
	}

	/**
	 * Raw insert because saving an EmailAccount connects to the IMAP server
	 */
	private static function createAccount(string $email, int $aclId): int {
		go()->getDbConnection()->insert('email_account', [
			'name' => $email,
			'email' => $email,
			'mtaDsn' => '',
			// not IMAP, so connect() fails without a network call
			'mdaDsn' => 'pop3:host=localhost',
			'mdaCapabilities' => '[]',
			'createdAt' => '2026-10-01 00:00:00',
			'aclId' => $aclId
		])->execute();
		return (int)go()->getDbConnection()->getPDO()->lastInsertId();
	}

	public function testUpdateWithoutAccountId() {
		$this->expectException(InvalidArguments::class);
		(new EmailAccount())->update(['mailboxId' => self::$otherMailboxId]);
	}

	public function testFillWithoutAccountId() {
		$this->expectException(InvalidArguments::class);
		(new EmailAccount())->fill([]);
	}

	public function testUpdateDeniedForUserWithoutAccess() {
		go()->setAuthState(new TemporaryState(self::$userId));
		$this->expectException(Forbidden::class);
		(new EmailAccount())->update(['accountId' => self::$accountId]);
	}

	public function testFillDeniedForUserWithoutAccess() {
		go()->setAuthState(new TemporaryState(self::$userId));
		$this->expectException(Forbidden::class);
		(new EmailAccount())->fill(['accountId' => self::$accountId]);
	}

	public function testUpdateRejectsMailboxOfOtherAccount() {
		$this->expectException(InvalidArguments::class);
		(new EmailAccount())->update(['accountId' => self::$accountId, 'mailboxId' => self::$otherMailboxId]);
	}

	public function testUpdateAllowedForReader() {
		go()->setAuthState(new TemporaryState(self::$userId));
		// passes the guards and only fails connecting, because the test account has no IMAP backend
		$this->expectExceptionMessage('Unsupported MDA type');
		(new EmailAccount())->update(['accountId' => self::$otherAccountId, 'mailboxId' => self::$otherMailboxId]);
	}

	public function testFillAllowedForReader() {
		go()->setAuthState(new TemporaryState(self::$userId));
		$this->expectExceptionMessage('Unsupported MDA type');
		(new EmailAccount())->fill(['accountId' => self::$otherAccountId]);
	}

	public function testRoleFilter() {
		$find = fn(string $role) => array_map('intval', Mailbox::find()->selectSingleValue('box.id')
			->where('box.accountId', '=', self::$otherAccountId)->filter(['role' => $role])->all());
		$this->assertSame([self::$otherMailboxId], $find('inbox'));
		$this->assertSame([], $find('sent'));
	}
}
