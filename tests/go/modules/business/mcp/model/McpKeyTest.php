<?php
namespace go\modules\business\mcp\model;

use go\core\auth\TemporaryState;
use go\core\model\AppPassword;
use go\core\model\OauthClient;
use go\core\model\Token;
use go\core\model\User;
use go\core\orm\EntityType;
use go\core\util\DateTime;
use go\core\validate\ErrorCode;
use go\modules\business\mcp\controller\McpKey as McpKeyController;
use go\modules\business\ModuleTablesTrait;
use go\modules\community\notes\model\Note;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../fixtures/ModuleTablesTrait.php';

/**
 * An MCP key gives an AI client access to the picked entities of its owner. The token is shown once, isn't a core
 * token, and entities that create credentials can never be picked.
 */
class McpKeyTest extends TestCase
{
	use ModuleTablesTrait;

	private $authState;
	private static User $owner;
	private static User $other;

	public static function setUpBeforeClass(): void
	{
		$authState = go()->getAuthState();
		go()->setAuthState(new TemporaryState(User::ID_SUPER_ADMIN));
		try {
			self::installModuleTables('business', 'mcp', go()->getEnvironment()->getInstallPath() . '/go/modules/business/mcp/install/install.sql');
			self::$owner = self::createUser('mcpowner');
			self::$other = self::createUser('mcpother');
		} catch (\Throwable $e) {
			self::tearDownAfterClass();
			throw $e;
		} finally {
			go()->setAuthState($authState);
		}
	}

	public static function tearDownAfterClass(): void
	{
		$authState = go()->getAuthState();
		go()->setAuthState(new TemporaryState(User::ID_SUPER_ADMIN));
		$userIds = [];
		foreach (['owner', 'other'] as $prop) {
			if (isset(self::$$prop)) {
				$userIds[] = self::$$prop->id;
			}
		}
		if ($userIds) {
			User::delete(['id' => $userIds]);
		}
		self::uninstallModuleTables();
		go()->setAuthState($authState);
	}

	protected function setUp(): void
	{
		$this->authState = go()->getAuthState();
	}

	protected function tearDown(): void
	{
		go()->setAuthState(new TemporaryState(User::ID_SUPER_ADMIN));
		McpKey::delete(['userId' => [self::$owner->id, self::$other->id]]);
		go()->setAuthState($this->authState);
	}

	private static function createUser(string $prefix): User
	{
		$name = uniqid($prefix);
		$user = new User();
		$user->setValues([
			'username' => $name,
			'displayName' => $name,
			'email' => $name . '@example.com',
			'password' => 'Pa55word!!xyz'
		]);
		self::assertTrue($user->save(), $user->getValidationErrorsAsString());
		return $user;
	}

	/**
	 * Without a license the module is only available to administrators, so the constructor is skipped as if the user may
	 * use the module.
	 */
	private function controller(): McpKeyController
	{
		return (new \ReflectionClass(McpKeyController::class))->newInstanceWithoutConstructor();
	}

	private function createKey(array $entityClasses = [Note::class], array $values = []): McpKey
	{
		go()->setAuthState(new TemporaryState(self::$owner->id));
		$key = new McpKey();
		$key->name = 'Test key';
		$key->setValues($values);
		$entities = [];
		foreach ($entityClasses as $cls) {
			$entities[] = ['entityTypeId' => (string) $cls::entityType()->getId()];
		}
		$key->setValues(['entities' => $entities]);
		$key->save();
		return $key;
	}

	public function testTokenIsReturnedOnceAndFindsTheKey(): void
	{
		$key = $this->createKey();
		$this->assertFalse($key->hasValidationErrors(), $key->getValidationErrorsAsString());
		$this->assertEquals(self::$owner->id, $key->userId);

		$token = $key->getToken();
		$this->assertMatchesRegularExpression('/^gomcp_' . $key->id . '_[a-f0-9]{64}$/', $token);

		$this->assertEquals($key->id, McpKey::findByToken($token)->id);
		$this->assertNull(McpKey::findById($key->id)->getToken(), 'The token is returned after it was created');
		$this->assertNull(McpKey::findByToken(substr($token, 0, -1) . (str_ends_with($token, '0') ? '1' : '0')), 'A wrong secret found the key');
		$this->assertNull(McpKey::findByToken('gomcp_' . $key->id . '_short'));
	}

	public function testTokenIsNotACoreToken(): void
	{
		// jmap\State only accepts core tokens, so an MCP key doesn't work on the JMAP API
		$token = $this->createKey()->getToken();
		$this->assertEmpty(Token::find()->where(['accessToken' => $token])->single());
	}

	public function testKeyExpires(): void
	{
		$key = $this->createKey();
		$this->assertEqualsWithDelta((new DateTime('+' . McpKey::DEFAULT_LIFETIME_DAYS . ' days'))->getTimestamp(), $key->expiresAt->getTimestamp(), 60);

		$expired = $this->createKey([Note::class], ['expiresAt' => new DateTime('-1 minute')]);
		$this->assertNull(McpKey::findByToken($expired->getToken()));
	}

	public function testPickedEntities(): void
	{
		$key = $this->createKey([Note::class]);

		$this->assertNotNull($key->findEntity('Note'));
		$this->assertNull($key->findEntity('Contact'));
		$this->assertNull($key->findEntity('DoesNotExist'));
	}

	public function testCredentialEntitiesCanNotBePicked(): void
	{
		$classes = [AppPassword::class, OauthClient::class, McpKey::class];
		if (class_exists('go\\modules\\community\\apikeys\\model\\Key') && EntityType::existsByName('Key')) {
			$classes[] = 'go\\modules\\community\\apikeys\\model\\Key';
		}

		foreach ($classes as $cls) {
			$key = $this->createKey([$cls]);
			$this->assertEquals(ErrorCode::INVALID_INPUT, $key->getValidationError('entities')['code'] ?? null, $cls . ' could be picked');
			$this->assertTrue($key->isNew());
		}

		go()->setAuthState(new TemporaryState(self::$owner->id));
		$names = array_column($this->controller()->entities([])['list'], 'name');
		$this->assertContains('Note', $names);
		foreach (['AppPassword', 'OauthClient', 'McpKey'] as $name) {
			$this->assertNotContains($name, $names);
		}
	}

	public function testTokenIsReturnedOnceThroughJmap(): void
	{
		go()->setAuthState(new TemporaryState(self::$owner->id));
		$created = $this->controller()->set(['create' => ['k' => [
			'name' => 'Through JMAP',
			'entities' => [['entityTypeId' => (string) Note::entityType()->getId()]]
		]]])['created']['k'] ?? null;

		$this->assertNotNull($created);
		$this->assertMatchesRegularExpression('/^gomcp_\d+_[a-f0-9]{64}$/', $created['token']);
		$this->assertArrayNotHasKey('secretHash', $created);

		$got = $this->controller()->get(['ids' => [$created['id']]])['list'][0];
		$this->assertEmpty($got['token'] ?? null);
		$this->assertArrayNotHasKey('secretHash', $got);
	}

	public function testExpiryDateIsRequired(): void
	{
		$key = $this->createKey();

		go()->setAuthState(new TemporaryState(self::$owner->id));
		$response = $this->controller()->set(['update' => [$key->id => ['expiresAt' => null]]]);

		$this->assertEquals('invalidProperties', $response['notUpdated'][$key->id]['type'] ?? null, json_encode($response));
		$this->assertContains('expiresAt', $response['notUpdated'][$key->id]['properties']);
	}

	public function testUserCanNotCreateAKeyForSomeoneElse(): void
	{
		$key = $this->createKey([Note::class], ['userId' => self::$other->id]);

		$this->assertEquals(ErrorCode::FORBIDDEN, $key->getValidationError('userId')['code'] ?? null);
	}

	public function testOtherUserCanNotReadTheKey(): void
	{
		$key = $this->createKey();

		go()->setAuthState(new TemporaryState(self::$other->id));
		$response = $this->controller()->get(['ids' => [$key->id]]);
		$this->assertEquals([$key->id], $response['notFound']);

		$ids = $this->controller()->query([])['ids'];
		$this->assertNotContains($key->id, $ids);
	}
}
