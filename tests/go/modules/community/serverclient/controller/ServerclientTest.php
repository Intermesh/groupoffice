<?php
namespace go\modules\community\serverclient\controller;

use go\core\auth\TemporaryState;
use go\core\exception\Forbidden;
use go\core\jmap\exception\InvalidArguments;
use go\core\model\User;
use PHPUnit\Framework\TestCase;

/**
 * A user may create their own mailbox, but only on the domains of the "serverclient_domains" config option
 */
class ServerclientTest extends TestCase
{
	private $authState;
	private User $user;

	protected function setUp(): void
	{
		$this->authState = go()->getAuthState();
		go()->setAuthState(new TemporaryState(User::ID_SUPER_ADMIN));

		$name = uniqid('mailboxuser');
		$this->user = new User();
		$this->user->setValues([
			'username' => $name,
			'displayName' => $name,
			'email' => $name . '@example.com',
			'password' => 'Pa55word!!xyz'
		]);
		$this->assertTrue($this->user->save(), $this->user->getValidationErrorsAsString());
	}

	protected function tearDown(): void
	{
		go()->setAuthState(new TemporaryState(User::ID_SUPER_ADMIN));
		User::delete(['id' => $this->user->id]);
		go()->setAuthState($this->authState);
	}

	/**
	 * The constructor checks access to the module, which isn't installed in the tests
	 */
	private function setMailbox(array $params): void
	{
		go()->setAuthState(new TemporaryState($this->user->id));
		(new \ReflectionClass(Serverclient::class))->newInstanceWithoutConstructor()->setMailbox($params + ['password' => 'secret']);
	}

	public function testDomainThatIsNotConfigured(): void
	{
		$this->expectException(InvalidArguments::class);
		$this->setMailbox(['userId' => $this->user->id, 'domains' => ['attacker.example']]);
	}

	public function testMailboxOfOtherUser(): void
	{
		$this->expectException(Forbidden::class);
		$this->setMailbox(['userId' => User::ID_SUPER_ADMIN, 'domains' => []]);
	}
}
