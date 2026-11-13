<?php
namespace go\core\auth;

use go\core\model\User;
use go\core\util\DateTime;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * The "recover" action of api/auth.php passes the "hash" of the JSON body to Authenticate::recovery(). JSON can also
 * contain numbers, booleans and arrays. MySQL compares a number with a varchar column numerically, so a number must
 * never find a user by the first digits of the hash.
 */
class RecoveryHashTest extends TestCase
{
	private User $user;
	private string $hash;

	protected function setUp(): void
	{
		$name = uniqid('recoveryhash');
		$this->user = new User();
		$this->user->setValues([
			'username' => $name,
			'displayName' => $name,
			'email' => $name . '@example.com',
			'password' => 'Pa55word!!xyz'
		]);
		$this->assertTrue($this->user->save(), var_export($this->user->getValidationErrors(), true));

		// a hash that starts with digits, like many generated ones do
		$this->hash = '12ab' . bin2hex(random_bytes(18));
		go()->getDbConnection()->update('core_user', [
			'recoveryHash' => $this->hash,
			'recoverySendAt' => new DateTime()
		], ['id' => $this->user->id])->execute();
	}

	protected function tearDown(): void
	{
		User::delete(['id' => $this->user->id]);
	}

	public static function invalidHashes(): array
	{
		return [
			'int' => [12],
			'true' => [true],
			'array' => [[12]],
			'float' => [12.0],
			'prefix' => ['12'],
			'uppercase' => ['12ABCDEF0123456789ABCDEF0123456789ABCDEF'],
			'too long' => ['12' . str_repeat('a', 39)],
		];
	}

	#[DataProvider('invalidHashes')]
	public function testInvalidHashFindsNoUser(mixed $hash): void
	{
		$this->assertFalse((new Authenticate())->recovery($hash));
	}

	public function testOtherHashFindsNoUser(): void
	{
		$this->assertFalse((new Authenticate())->recovery(bin2hex(random_bytes(20))));
	}

	public function testCorrectHashFindsUser(): void
	{
		$user = (new Authenticate())->recovery($this->hash);
		$this->assertInstanceOf(User::class, $user);
		$this->assertEquals($this->user->id, $user->id);
	}

	public function testExpiredHashFindsNoUser(): void
	{
		go()->getDbConnection()->update('core_user', [
			'recoverySendAt' => new DateTime('-2 hours')
		], ['id' => $this->user->id])->execute();

		$this->assertFalse((new Authenticate())->recovery($this->hash));
	}
}
