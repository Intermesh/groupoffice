<?php

namespace go\modules\community\ldapauthenticator\model;

use go\modules\community\ldapauthenticator\Module;
use PHPUnit\Framework\TestCase;

/**
 * The login name is put in an LDAP filter, so it must be escaped. Otherwise a name like "*" or "admin)(uid=*"
 * changes the filter and finds another user.
 */
class AuthenticationQueryTest extends TestCase
{
	protected function setUp(): void
	{
		if (!go()->getModule('community', 'ldapauthenticator')) {
			$this->assertNotFalse(Module::get()->install(), 'Could not install ldapauthenticator module');
		}
	}

	public static function usernames(): array
	{
		return [
			'plain' => ['john', 'uid=john'],
			'wildcard' => ['*', 'uid=\2a'],
			'filter' => ['admin)(uid=*', 'uid=admin\29\28uid=\2a'],
			'backslash and nul' => ["a\\b\0", 'uid=a\5cb\00'],
		];
	}

	#[\PHPUnit\Framework\Attributes\DataProvider('usernames')]
	public function testUsernameIsEscaped(string $username, string $expected): void
	{
		$server = new Server();
		$server->usernameAttribute = 'uid';
		$server->syncUsersQuery = '';
		$this->assertSame($expected, $server->getAuthenticationQuery($username));

		$server->syncUsersQuery = '(objectClass=person)';
		$this->assertSame('(&(objectClass=person)(' . $expected . '))', $server->getAuthenticationQuery($username));
	}
}
