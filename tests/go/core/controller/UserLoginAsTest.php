<?php
namespace go\core\controller;

use go\core\exception\NotFound;
use go\core\jmap\State;
use go\core\model\Group;
use go\core\model\Token;
use go\core\model\User as UserModel;
use PHPUnit\Framework\TestCase;

/**
 * An administrator can log in as another user. The session must get new tokens, so the tokens of the administrator's
 * session can't be used for the other user.
 */
class UserLoginAsTest extends TestCase
{
	private $authState;
	private array $session;
	private array $userIds = [];
	private UserModel $admin;
	private UserModel $target;
	private Token $token;

	protected function setUp(): void
	{
		$this->authState = go()->getAuthState();
		$this->session = $_SESSION['GO_SESSION'] ?? [];

		$this->admin = $this->newUser('loginasadmin', true);
		$this->target = $this->newUser('loginastarget', false);

		$this->token = new Token();
		$this->token->userId = $this->admin->id;
		$this->assertTrue($this->token->setAuthenticated(false));

		$state = new State();
		$state->setToken($this->token);
		go()->setAuthState($state);
	}

	protected function tearDown(): void
	{
		go()->setAuthState($this->authState);
		$_SESSION['GO_SESSION'] = $this->session;
		UserModel::delete(['id' => $this->userIds]);
	}

	private function newUser(string $prefix, bool $admin): UserModel
	{
		$name = uniqid($prefix);
		$user = new UserModel();
		$user->setValues([
			'username' => $name,
			'displayName' => $name,
			'email' => $name . '@example.com',
			'password' => 'Pa55word!!xyz'
		]);
		if ($admin) {
			$user->groups[] = Group::ID_ADMINS;
		}
		$this->assertTrue($user->save(), var_export($user->getValidationErrors(), true));
		$this->userIds[] = $user->id;
		return $user;
	}

	public function testUnknownUserIsNotFound(): void
	{
		$this->expectException(NotFound::class);
		(new User())->loginAs(['userId' => 999999999]);
	}

	public function testTokensChange(): void
	{
		$accessToken = $this->token->accessToken;
		$csrfToken = $this->token->CSRFToken;

		(new User())->loginAs(['userId' => $this->target->id]);

		$this->assertNotEquals($accessToken, $this->token->accessToken);
		$this->assertNotEquals($csrfToken, $this->token->CSRFToken);
		$this->assertEquals($this->target->id, $this->token->userId);

		$this->assertNull(Token::find()->where(['accessToken' => $accessToken])->single(), 'the old access token must not work anymore');
		$this->assertEquals($this->target->id, Token::find()->where(['accessToken' => $this->token->accessToken])->single()->userId);
	}

	public function testAdministratorIsRecordedInHistory(): void
	{
		if (!\go\core\model\Module::isInstalled('community', 'history')) {
			$this->markTestSkipped('The history module is not installed');
		}

		(new User())->loginAs(['userId' => $this->target->id]);

		$createdBy = go()->getDbConnection()
			->selectSingleValue('createdBy')
			->from('history_log_entry')
			->where('description', 'LIKE', $this->target->username . ' [%' . $this->admin->username . '%')
			->single();
		$this->assertEquals($this->admin->id, $createdBy);
	}
}
