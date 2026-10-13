<?php

namespace go\modules\community\ldapauthenticator\model;

use GO\Email\Model\Account;
use go\core\auth\TemporaryState;
use go\core\model\User;
use go\modules\community\ldapauthenticator\Module;
use PHPUnit\Framework\TestCase;

/**
 * The LDAP sync doesn't know the user's login password, so it must update the e-mail account
 * passwords from the mapped imapPassword / smtpPassword attributes.
 */
class SyncEmailPasswordsTest extends TestCase
{
	private string $host;
	private User $user;
	private Account $account;

	protected function setUp(): void
	{
		if (!go()->getModule('community', 'ldapauthenticator')) {
			$this->assertNotFalse(Module::get()->install(), 'Could not install ldapauthenticator module');
		}

		$this->host = 'imap-' . uniqid() . '.localhost';

		// the admin user has access to the e-mail module
		$this->user = User::findById(1);
		go()->setAuthState(new TemporaryState($this->user->id));

		$account = new Account();
		$account->user_id = $this->user->id;
		$account->host = $this->host;
		$account->port = 143;
		$account->imap_encryption = '';
		$account->smtp_encryption = '';
		$account->username = 'shared';
		$account->password = 'old-imap';
		$account->smtp_host = $this->host;
		$account->smtp_port = 25;
		$account->smtp_username = 'shared';
		$account->smtp_password = 'old-smtp';
		$account->checkImapConnectionOnSave = false;
		$this->assertTrue($account->save(true), json_encode($account->getValidationErrors()));
		$this->account = $account;
	}

	protected function tearDown(): void
	{
		$account = Account::model()->findByPk($this->account->id);
		if ($account) {
			$account->delete();
		}
	}

	private function server(?string $host): Server
	{
		$server = new Server();
		$server->imapHostname = $host;
		return $server;
	}

	private function reload(): Account
	{
		return Account::model()->findByPk($this->account->id, false, true);
	}

	public function testPasswordsAreUpdated(): void
	{
		(new Authenticator())->syncEmailPasswords(
			$this->server($this->host),
			$this->user,
			['imapPassword' => 'new-imap', 'smtpPassword' => 'new-smtp']
		);

		$account = $this->reload();
		$this->assertEquals('new-imap', $account->decryptPassword());
		$this->assertEquals('new-smtp', $account->decryptSmtpPassword());
	}

	public function testNothingChangesWithoutMappedPasswords(): void
	{
		(new Authenticator())->syncEmailPasswords($this->server($this->host), $this->user, ['email' => 'a@b.c']);

		$account = $this->reload();
		$this->assertEquals('old-imap', $account->decryptPassword());
		$this->assertEquals('old-smtp', $account->decryptSmtpPassword());
	}

	public function testOtherHostIsNotTouched(): void
	{
		(new Authenticator())->syncEmailPasswords(
			$this->server('other-' . $this->host),
			$this->user,
			['imapPassword' => 'new-imap']
		);

		$this->assertEquals('old-imap', $this->reload()->decryptPassword());
	}

	public function testNoAccountIsCreated(): void
	{
		$before = Account::model()->findByAttributes(['user_id' => $this->user->id])->rowCount();

		(new Authenticator())->syncEmailPasswords(
			$this->server('new-' . $this->host),
			$this->user,
			['imapPassword' => 'new-imap']
		);

		$after = Account::model()->findByAttributes(['user_id' => $this->user->id])->rowCount();
		$this->assertEquals($before, $after);
	}
}
