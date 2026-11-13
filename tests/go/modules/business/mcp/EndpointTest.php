<?php
namespace go\modules\business\mcp;

use go\core\auth\TemporaryState;
use go\core\model\User;
use go\core\util\DateTime;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/McpFixtureTrait.php';

/**
 * The endpoint only serves licensed installations, valid keys of enabled users with access to the module, and no
 * requests from other web pages.
 */
class EndpointTest extends TestCase
{
	use McpFixtureTrait;

	private $authState;
	private static string $token;

	public static function setUpBeforeClass(): void
	{
		self::setUpMcp();
		try {
			self::$token = self::createMcpKey(['Note' => false])->getToken();
		} catch (\Throwable $e) {
			self::tearDownMcp();
			throw $e;
		}
	}

	public static function tearDownAfterClass(): void
	{
		self::tearDownMcp();
	}

	protected function setUp(): void
	{
		$this->authState = go()->getAuthState();
	}

	protected function tearDown(): void
	{
		go()->setAuthState($this->authState);
	}

	/**
	 * An endpoint that is licensed and lets the user use the module, unless told otherwise. Without a license nobody
	 * may use the module in the tests.
	 */
	private function endpoint(bool $licensed = true, bool $mayUseModule = true): Endpoint
	{
		return new class($licensed, $mayUseModule) extends Endpoint {
			public function __construct(private bool $licensed, private bool $mayUse)
			{
			}

			protected function isLicensed(): bool
			{
				return $this->licensed;
			}

			protected function mayUseModule(int $userId): bool
			{
				return $this->mayUse;
			}
		};
	}

	private function initialize(Endpoint $endpoint, ?string $token, array $headers = []): array
	{
		if (isset($token)) {
			$headers['authorization'] = 'Bearer ' . $token;
		}
		return $endpoint->handle('POST', $headers, json_encode([
			'jsonrpc' => '2.0',
			'id' => 1,
			'method' => 'initialize',
			'params' => ['protocolVersion' => '2025-11-25', 'capabilities' => new \stdClass(), 'clientInfo' => ['name' => 'test', 'version' => '1']]
		]));
	}

	public function testValidKeyIsServedAsItsUser(): void
	{
		$response = $this->initialize($this->endpoint(), self::$token);

		$this->assertEquals(200, $response['status']);
		$this->assertEquals('2025-11-25', $response['body']['result']['protocolVersion']);
		$this->assertEquals(self::$mcpUser->id, go()->getAuthState()->getUserId());
	}

	public function testUnlicensedModuleIsRefused(): void
	{
		// The real check: there is no license in the tests
		$response = $this->initialize(new Endpoint(), self::$token);
		$this->assertEquals(403, $response['status']);

		$response = $this->initialize($this->endpoint(false), self::$token);
		$this->assertEquals(403, $response['status']);
		$this->assertNotEquals(self::$mcpUser->id, go()->getAuthState()->getUserId());
	}

	public function testMissingOrInvalidKeyIsUnauthorized(): void
	{
		foreach ([null, 'invalid', substr(self::$token, 0, -2) . 'ff', 'gomcp_1_' . str_repeat('a', 64)] as $token) {
			$response = $this->initialize($this->endpoint(), $token);
			$this->assertEquals(401, $response['status'], 'Token: ' . var_export($token, true));
			$this->assertStringStartsWith('Bearer', $response['headers']['WWW-Authenticate']);
			$this->assertArrayNotHasKey('result', $response['body']);
		}
	}

	public function testExpiredKeyIsUnauthorized(): void
	{
		$token = self::createMcpKey(['Note' => false], ['expiresAt' => new DateTime('-1 minute')])->getToken();

		$this->assertEquals(401, $this->initialize($this->endpoint(), $token)['status']);
	}

	public function testKeyOfDisabledUserIsUnauthorized(): void
	{
		go()->setAuthState(new TemporaryState(User::ID_SUPER_ADMIN));
		$user = User::findById(self::$mcpUser->id);
		$user->enabled = false;
		$this->assertTrue($user->save(), $user->getValidationErrorsAsString());

		try {
			$this->assertEquals(401, $this->initialize($this->endpoint(), self::$token)['status']);
		} finally {
			go()->setAuthState(new TemporaryState(User::ID_SUPER_ADMIN));
			$user = User::findById(self::$mcpUser->id);
			$user->enabled = true;
			$user->save();
		}
	}

	public function testUserWithoutModuleAccessIsRefused(): void
	{
		$response = $this->initialize($this->endpoint(true, false), self::$token);

		$this->assertEquals(403, $response['status']);
		$this->assertNotEquals(self::$mcpUser->id, go()->getAuthState()->getUserId());
	}

	public function testRequestFromOtherWebPageIsRefused(): void
	{
		$response = $this->initialize($this->endpoint(), self::$token, ['origin' => 'https://evil.example.com']);

		$this->assertEquals(403, $response['status']);
	}

	public function testOnlyPostIsServed(): void
	{
		$response = $this->endpoint()->handle('GET', ['authorization' => 'Bearer ' . self::$token], '');

		$this->assertEquals(405, $response['status']);
	}

	public function testInvalidJsonIsAParseError(): void
	{
		$response = $this->endpoint()->handle('POST', ['authorization' => 'Bearer ' . self::$token], '{invalid');

		$this->assertEquals(400, $response['status']);
		$this->assertEquals(Server::PARSE_ERROR, $response['body']['error']['code']);
	}
}
