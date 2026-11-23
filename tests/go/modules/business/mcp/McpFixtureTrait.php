<?php
namespace go\modules\business\mcp;

use go\core\auth\TemporaryState;
use go\core\model\User;
use go\core\orm\EntityType;
use go\modules\business\mcp\model\McpKey;
use go\modules\business\ModuleTablesTrait;

require_once __DIR__ . '/../fixtures/ModuleTablesTrait.php';

/**
 * Module tables, a user and keys for the MCP tests
 *
 * Call setUpMcp() in setUpBeforeClass() and tearDownMcp() in tearDownAfterClass().
 */
trait McpFixtureTrait
{
	use ModuleTablesTrait;

	private static User $mcpUser;

	/** @var McpKey[] Keys created with createMcpKey(), deleted by tearDownMcp() */
	private static array $mcpKeys = [];

	private static function setUpMcp(): void
	{
		$authState = go()->getAuthState();
		go()->setAuthState(new TemporaryState(User::ID_SUPER_ADMIN));
		try {
			self::installModuleTables('business', 'mcp', go()->getEnvironment()->getInstallPath() . '/go/modules/business/mcp/install/install.sql');
			self::$mcpUser = self::createMcpUser('mcpuser');
		} catch (\Throwable $e) {
			self::tearDownMcp();
			throw $e;
		} finally {
			go()->setAuthState($authState);
		}
	}

	private static function tearDownMcp(): void
	{
		$authState = go()->getAuthState();
		go()->setAuthState(new TemporaryState(User::ID_SUPER_ADMIN));
		if (!empty(self::$mcpKeys)) {
			McpKey::delete(['id' => array_map(fn($k) => $k->id, self::$mcpKeys)]);
			self::$mcpKeys = [];
		}
		if (isset(self::$mcpUser)) {
			User::delete(['id' => self::$mcpUser->id]);
		}
		self::uninstallModuleTables();
		go()->setAuthState($authState);
	}

	private static function createMcpUser(string $prefix): User
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
	 * Create a key of the MCP user
	 *
	 * @param array<string, bool> $entities Entity name => may write
	 * @param array $values Other values of the key
	 * @return McpKey The saved key. Its token is available with getToken().
	 */
	private static function createMcpKey(array $entities, array $values = []): McpKey
	{
		$authState = go()->getAuthState();
		go()->setAuthState(new TemporaryState(self::$mcpUser->id));
		try {
			$key = new McpKey();
			$key->name = uniqid('key');
			$key->setValues($values);
			$list = [];
			foreach ($entities as $name => $mayWrite) {
				$list[] = ['entityTypeId' => (string) EntityType::findByName($name)->getId(), 'mayWrite' => $mayWrite];
			}
			$key->setValues(['entities' => $list]);
			self::assertTrue($key->save(), $key->getValidationErrorsAsString());
			self::$mcpKeys[] = $key;
			return $key;
		} finally {
			go()->setAuthState($authState);
		}
	}
}
