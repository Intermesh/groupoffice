<?php

namespace GO\Email\Model;

use go\core\auth\TemporaryState;
use PHPUnit\Framework\TestCase;

/**
 * Logging in with IMAP / ActiveSync sets the same passwords on every login. That must not re-encrypt
 * them, and password values must never end up in the history log.
 */
class AccountTest extends TestCase
{
	private Account $account;

	protected function setUp(): void
	{
		go()->setAuthState(new TemporaryState(1));

		$host = 'imap-' . uniqid() . '.localhost';
		$account = new Account();
		$account->user_id = 1;
		$account->host = $host;
		$account->port = 143;
		$account->imap_encryption = '';
		$account->smtp_encryption = '';
		$account->username = 'test';
		$account->password = 'imap-secret';
		$account->smtp_host = $host;
		$account->smtp_port = 25;
		$account->smtp_username = 'test';
		$account->smtp_password = 'smtp-secret';
		$account->checkImapConnectionOnSave = false;
		$this->assertTrue($account->save(true), json_encode($account->getValidationErrors()));
		$this->account = $account;
	}

	protected function tearDown(): void
	{
		$account = Account::model()->findByPk($this->account->id, false, true);
		if ($account) {
			$account->delete();
		}
	}

	private function reload(): Account
	{
		$account = Account::model()->findByPk($this->account->id, false, true);
		$account->checkImapConnectionOnSave = false;
		return $account;
	}

	public function testSamePasswordIsNotReEncrypted(): void
	{
		$account = $this->reload();
		$password = $account->password;
		$smtpPassword = $account->smtp_password;

		$account->password = 'imap-secret';
		$account->smtp_password = 'smtp-secret';
		$this->assertTrue($account->save(true));

		$account = $this->reload();
		$this->assertSame($password, $account->password);
		$this->assertSame($smtpPassword, $account->smtp_password);
		$this->assertSame('imap-secret', $account->decryptPassword());
		$this->assertSame('smtp-secret', $account->decryptSmtpPassword());
	}

	public function testChangedPasswordIsSaved(): void
	{
		$account = $this->reload();
		$account->password = 'new-imap';
		$account->smtp_password = 'new-smtp';
		$this->assertTrue($account->save(true));

		$account = $this->reload();
		$this->assertSame('new-imap', $account->decryptPassword());
		$this->assertSame('new-smtp', $account->decryptSmtpPassword());
	}

	public function testPasswordsAreMaskedInLog(): void
	{
		$account = $this->reload();
		$account->password = 'new-imap';
		$account->smtp_password = 'new-smtp';

		$log = $account->getLogJSON('update');
		$this->assertSame(['***', '***'], $log['password']);
		$this->assertSame(['***', '***'], $log['smtp_password']);
	}
}
