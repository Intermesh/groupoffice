<?php
namespace go\core\dav\davacl;

use go\core\auth\TemporaryState;
use go\core\model\Acl;
use go\core\model\Group;
use go\core\model\User;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * The principal search used by DAV clients for recipient suggestions. The searcher may only find users that are
 * shared with them and only when the search term matches.
 */
class PrincipalBackendTest extends TestCase
{
	private const DISPLAYNAME = '{DAV:}displayname';
	private const EMAIL = '{http://sabredav.org/ns}email-address';

	private $authState;

	private User $searcher;
	private User $visible;
	private User $hidden;

	protected function setUp(): void
	{
		$this->authState = go()->getAuthState();

		$this->searcher = $this->newUser();
		$this->visible = $this->newUser();
		$this->hidden = $this->newUser();

		// share the visible user with the searcher
		$group = $this->visible->getPersonalGroup();
		$group->setAcl([
			$group->id => Acl::LEVEL_READ,
			$this->searcher->getPersonalGroup()->id => Acl::LEVEL_READ
		]);
		$this->assertTrue($group->save(), $group->getValidationErrorsAsString());

		// by default all users can see each other via the "Everyone" group. The hidden user is not shared.
		$group = $this->hidden->getPersonalGroup();
		$group->setAcl([$group->id => Acl::LEVEL_READ, Group::ID_EVERYONE => null]);
		$this->assertTrue($group->save(), $group->getValidationErrorsAsString());

		go()->setAuthState(new TemporaryState($this->searcher->id));
	}

	protected function tearDown(): void
	{
		go()->setAuthState($this->authState);

		foreach ([$this->searcher ?? null, $this->visible ?? null, $this->hidden ?? null] as $user) {
			if ($user) {
				User::delete(['id' => $user->id]);
			}
		}
	}

	private function newUser(): User
	{
		$name = uniqid('principal');
		$user = new User();
		$user->setValues([
			'username' => $name,
			'displayName' => $name,
			'email' => $name . '@example.com',
			'password' => 'Pa55word!!xyz'
		]);
		$this->assertTrue($user->save(), var_export($user->getValidationErrors(), true));

		return $user;
	}

	private function search(array $properties, string $test): array
	{
		return (new PrincipalBackend())->searchPrincipals('principals', $properties, $test);
	}

	public static function anyOfAllOfProvider(): array
	{
		return [['anyof'], ['allof']];
	}

	#[DataProvider('anyOfAllOfProvider')]
	public function testFindsSharedUser(string $test)
	{
		$name = $this->visible->username;
		$result = $this->search([self::DISPLAYNAME => $name, self::EMAIL => $name], $test);

		$this->assertEquals(['principals/' . $name], $result);
	}

	/**
	 * eM Client sends an "anyof" search. Any term returned all visible users.
	 */
	#[DataProvider('anyOfAllOfProvider')]
	public function testNonMatchingTermFindsNothing(string $test)
	{
		$result = $this->search([self::DISPLAYNAME => 'xyz123-no-match', self::EMAIL => 'xyz123-no-match'], $test);

		$this->assertEquals([], $result);
	}

	/**
	 * The search terms were OR'ed with the ACL condition, so users that are not shared were found by name.
	 */
	#[DataProvider('anyOfAllOfProvider')]
	public function testDoesNotFindUserWithoutPermission(string $test)
	{
		$name = $this->hidden->username;
		$result = $this->search([self::DISPLAYNAME => $name, self::EMAIL => $name], $test);

		$this->assertEquals([], $result);
	}
}
