<?php
namespace go\core\auth;

use go\core\exception\Forbidden;
use go\core\model\Group;
use go\core\model\Module as ModuleModel;
use go\core\model\User;
use go\modules\community\oauth2client\Module;
use PHPUnit\Framework\TestCase;

/**
 * Logins with an OpenID Connect or OAuth2 provider find the user by the subject ("sub") at the provider. The e-mail
 * address may only link an existing user when that can't give somebody else access to the account.
 */
class ExternalUserLinkTest extends TestCase
{
	private static bool $installedByTest = false;
	private $authState;
	private bool $allowRegistration;
	private int $clientId;
	private string $prefix;
	private array $userIds = [];

	public static function setUpBeforeClass(): void
	{
		if (!ModuleModel::findByName('community', 'oauth2client')) {
			go()->setAuthState(new TemporaryState(User::ID_SUPER_ADMIN));
			self::assertNotFalse(Module::get()->install(), 'Could not install the oauth2client module');
			self::$installedByTest = true;
		}
	}

	public static function tearDownAfterClass(): void
	{
		if (self::$installedByTest) {
			go()->setAuthState(new TemporaryState(User::ID_SUPER_ADMIN));
			// Module::uninstall() switches off change tracking and history without switching them on again
			$trackChanges = \go\core\jmap\Entity::$trackChanges;
			$history = \go\modules\community\history\Module::$enabled;
			Module::get()->uninstall();
			\go\core\jmap\Entity::$trackChanges = $trackChanges;
			\go\modules\community\history\Module::$enabled = $history;
			self::$installedByTest = false;
		}
	}

	protected function setUp(): void
	{
		$this->authState = go()->getAuthState();
		go()->setAuthState(new TemporaryState(User::ID_SUPER_ADMIN));
		$this->allowRegistration = go()->getSettings()->allowRegistration;
		go()->getSettings()->allowRegistration = true;

		$this->prefix = uniqid('extlink');
		// The OIDC module isn't on this branch, so the OAuth2 client tables are used
		go()->getDbConnection()->insert('oauth2client_oauth2client', [
			'name' => $this->prefix,
			'defaultClientId' => 1,
			'clientId' => $this->prefix,
			'clientSecret' => 'secret',
			'projectId' => '',
			'openId' => true
		])->execute();
		$this->clientId = (int) go()->getDbConnection()->getPDO()->lastInsertId();
	}

	protected function tearDown(): void
	{
		go()->getSettings()->allowRegistration = $this->allowRegistration;

		$this->userIds = array_merge($this->userIds, go()->getDbConnection()
			->selectSingleValue('userId')
			->from('oauth2client_openid_user')
			->where(['clientId' => $this->clientId])
			->all());
		if (!empty($this->userIds)) {
			User::delete(['id' => array_unique($this->userIds)]);
		}
		go()->getDbConnection()->delete('oauth2client_oauth2client', ['id' => $this->clientId])->execute();
		go()->setAuthState($this->authState);
	}

	private function link(): ExternalUserLink
	{
		return new ExternalUserLink('oauth2client_openid_user', $this->clientId);
	}

	private function email(string $name): string
	{
		return $this->prefix . $name . '@example.com';
	}

	private function newUser(string $name, bool $withPassword, bool $admin = false): User
	{
		$user = new User();
		$user->setValues([
			'username' => $this->prefix . $name,
			'displayName' => $this->prefix . $name,
			'email' => $this->email($name),
		]);
		if ($withPassword) {
			$user->setPassword('Pa55word!!xyz');
		}
		if ($admin) {
			$user->groups[] = Group::ID_ADMINS;
		}
		$this->assertTrue($user->save(), var_export($user->getValidationErrors(), true));
		$this->userIds[] = $user->id;

		return $user;
	}

	private function subOf(int $userId): ?string
	{
		return go()->getDbConnection()
			->selectSingleValue('sub')
			->from('oauth2client_openid_user')
			->where(['clientId' => $this->clientId, 'userId' => $userId])
			->single();
	}

	private function assertRefused(array $claims): void
	{
		try {
			$this->link()->findOrCreateUser($claims);
			$this->fail('The login was not refused');
		} catch (Forbidden $e) {
			$this->assertNotEmpty($e->getMessage());
		}
	}

	public function testNewUserIsCreatedAndFoundBySubject(): void
	{
		$user = $this->link()->findOrCreateUser(['sub' => 'new-1', 'email' => $this->email('new'), 'name' => 'New']);
		$this->userIds[] = $user->id;

		$this->assertSame($this->email('new'), $user->email);
		$this->assertSame('new-1', $this->subOf($user->id));

		// the address changed at the provider: still the same user
		$again = $this->link()->findOrCreateUser(['sub' => 'new-1', 'email' => $this->email('changed')]);
		$this->assertEquals($user->id, $again->id);
	}

	public function testSubjectIsCaseSensitive(): void
	{
		$user = $this->link()->findOrCreateUser(['sub' => 'CaseSub', 'email' => $this->email('case'), 'email_verified' => true]);
		$this->userIds[] = $user->id;

		// another subject with the same address may not take over the user
		$this->assertRefused(['sub' => 'casesub', 'email' => $this->email('case'), 'email_verified' => true]);
	}

	public function testUserWithPasswordIsNotLinkedByEmail(): void
	{
		$user = $this->newUser('pw', true);

		$this->assertRefused(['sub' => 'pw-1', 'email' => $user->email, 'email_verified' => true]);
		$this->assertNull($this->subOf($user->id));
	}

	public function testUserWithPasswordIsNotLinkedByUsername(): void
	{
		$user = $this->newUser('pwname', true);
		$user->username = $this->email('pwname-login');
		$this->assertTrue($user->save());

		$this->assertRefused(['sub' => 'pwname-1', 'email' => $user->username, 'email_verified' => true]);
	}

	public function testAdminIsNotLinkedByEmail(): void
	{
		$admin = $this->newUser('admin', false, true);

		$this->assertRefused(['sub' => 'admin-1', 'email' => $admin->email, 'email_verified' => true]);
	}

	public function testUnverifiedEmailDoesNotLinkUser(): void
	{
		$user = $this->newUser('unverified', false);

		// a missing claim isn't trusted
		$this->assertRefused(['sub' => 'unverified-1', 'email' => $user->email]);
		$this->assertRefused(['sub' => 'unverified-1', 'email' => $user->email, 'email_verified' => 'yes please']);
		$this->assertRefused(['sub' => 'unverified-1', 'email' => $user->email, 'email_verified' => false]);
	}

	public function testUnverifiedEmailDoesNotCreateUser(): void
	{
		$this->assertRefused(['sub' => 'unverified-2', 'email' => $this->email('unverified2'), 'email_verified' => false]);
		$this->assertNull(User::find(['id'])->where(['email' => $this->email('unverified2')])->single());
	}

	public function testUserWithoutPasswordIsLinkedByVerifiedEmail(): void
	{
		$user = $this->newUser('nopw', false);

		$found = $this->link()->findOrCreateUser(['sub' => 'nopw-1', 'email' => $user->email, 'email_verified' => true]);

		$this->assertEquals($user->id, $found->id);
		$this->assertSame('nopw-1', $this->subOf($user->id));
	}

	public function testExistingLinkWithoutSubjectGetsSubject(): void
	{
		// linked by an older version or by an administrator
		$user = $this->newUser('old', true);
		go()->getDbConnection()->insert('oauth2client_openid_user', ['userId' => $user->id, 'clientId' => $this->clientId])->execute();

		$found = $this->link()->findOrCreateUser(['sub' => 'old-1', 'email' => $user->email]);

		$this->assertEquals($user->id, $found->id);
		$this->assertSame('old-1', $this->subOf($user->id));

		// from now on it's the subject that counts
		$this->assertRefused(['sub' => 'old-2', 'email' => $user->email, 'email_verified' => true]);
	}

	public function testMissingSubjectIsRefused(): void
	{
		$this->assertRefused(['email' => $this->email('nosub'), 'email_verified' => true]);
		$this->assertRefused(['sub' => ['x'], 'email' => $this->email('nosub'), 'email_verified' => true]);
		$this->assertRefused(['sub' => '', 'email' => $this->email('nosub'), 'email_verified' => true]);
	}
}
