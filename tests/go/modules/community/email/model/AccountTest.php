<?php

namespace go\modules\community\email\model;

use go\core\model\Acl;
use go\modules\community\oauth2client\model\Oauth2Account;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../../legacy/email/fixtures/SharedAccountTrait.php';
require_once __DIR__ . '/../../../business/fixtures/ModuleTablesTrait.php';

/**
 * The API never returns the passwords and OAuth tokens of an e-mail account. Everyone who may read the account, like
 * a colleague it's shared with, would get them.
 */
class AccountTest extends TestCase
{
	use \go\modules\legacy\email\SharedAccountTrait;
	use \go\modules\business\ModuleTablesTrait;

	public static function setUpBeforeClass(): void
	{
		// The tables of the OAuth tokens of e-mail accounts, when the module isn't installed
		self::installModuleTables('community', 'oauth2client', go()->getEnvironment()->getInstallPath() . '/go/modules/community/oauth2client/install/install.sql');
	}

	public static function tearDownAfterClass(): void
	{
		self::uninstallModuleTables();
	}

	protected function setUp(): void
	{
		$this->setUpSharedAccount(Acl::LEVEL_READ);
	}

	protected function tearDown(): void
	{
		if (isset($this->authState)) {
			$this->tearDownSharedAccount();
		}
	}

	public function testPasswordsAreNotReturned(): void
	{
		$this->actAs($this->other->id);
		$account = Account::findById($this->account->id);
		$this->assertNotNull($account, 'the other user may read the account');

		$arr = $account->toArray();
		$this->assertSame($this->account->username, $arr['username']);
		$this->assertArrayNotHasKey('password', $arr);
		$this->assertArrayNotHasKey('smtp_password', $arr);

		$schema = Account::getApiProperties();
		$this->assertNotSame(\go\core\data\Model::PROP_PUBLIC, $schema['password']['access'] ?? null);
	}

	public function testPasswordCanBeSetAndIsEncrypted(): void
	{
		$this->actAs($this->owner->id);
		$account = Account::findById($this->account->id);
		$account->setValues(['password' => 'new-secret', 'smtp_password' => 'smtp-secret']);
		$this->assertTrue($account->save(), $account->getValidationErrorsAsString());

		$stored = go()->getDbConnection()->select('password, smtp_password')->from('em_accounts')->where('id', '=', $this->account->id)->single();
		$this->assertNotSame('new-secret', $stored['password']);
		$this->assertSame('new-secret', \GO\Base\Util\Crypt::decrypt($stored['password']));
		$this->assertSame('smtp-secret', \GO\Base\Util\Crypt::decrypt($stored['smtp_password']));
	}

	public function testOAuthTokensAreNotReturned(): void
	{
		$this->actAs($this->owner->id);
		$oauth = new Oauth2Account(Account::findById($this->account->id));
		$oauth->oauth2ClientId = '1';
		$oauth->token = 'access-token';
		$oauth->refreshToken = 'refresh-token';
		$oauth->expires = time() + 3600;

		$arr = $oauth->toArray();
		$this->assertArrayNotHasKey('token', $arr);
		$this->assertArrayNotHasKey('refreshToken', $arr);
		$this->assertSame('1', $arr['oauth2ClientId']);
		// Still readable in PHP, for connecting to the mail server
		$this->assertSame('access-token', $oauth->token);
	}
}
