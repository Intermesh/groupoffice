<?php
namespace go\modules\community\davclient\model;

use go\core\exception\Forbidden;
use PHPUnit\Framework\TestCase;

/**
 * The host of a DAV account is entered by the user, so the DAV client may not connect to internal addresses of the
 * server or the local network unless "davclientAllowInternal" is set in config.php.
 */
class HttpClientTest extends TestCase
{
	private $allowInternal;

	protected function setUp(): void
	{
		$this->allowInternal = go()->getConfig()['davclientAllowInternal'];
	}

	protected function tearDown(): void
	{
		$this->setAllowInternal($this->allowInternal);
	}

	private function setAllowInternal(bool $allow): void
	{
		$prop = new \ReflectionProperty(go(), 'config');
		$config = $prop->getValue(go());
		$config['davclientAllowInternal'] = $allow;
		$prop->setValue(go(), $config);
	}

	public static function internalHosts(): array
	{
		return [
			'loopback' => ['http://127.0.0.1:1'],
			'metadata' => ['http://169.254.169.254'],
			'private' => ['http://10.0.0.1:1'],
		];
	}

	#[\PHPUnit\Framework\Attributes\DataProvider('internalHosts')]
	public function testInternalHostIsRefused(string $host): void
	{
		$this->setAllowInternal(false);
		$client = new HttpClient($host, []);
		$this->expectException(Forbidden::class);
		$client->PROPFIND('/', '<propfind xmlns="DAV:"/>');
	}

	public function testInternalHostIsAllowedByConfig(): void
	{
		$this->setAllowInternal(true);
		$client = new HttpClient('http://127.0.0.1:1', []);
		try {
			$client->PROPFIND('/', '<propfind xmlns="DAV:"/>');
			$this->fail("Port 1 should refuse the connection");
		} catch (Forbidden $e) {
			$this->fail("The host may not be refused when davclientAllowInternal is set");
		} catch (\Exception $e) {
			// connection refused
			$this->assertTrue(true);
		}
	}
}
