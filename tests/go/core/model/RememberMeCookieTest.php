<?php
namespace go\core\model;

use go\core\http\Response;
use go\core\Singleton;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionProperty;

/**
 * Records the cookies instead of sending them
 */
class CookieRecordingResponse extends Response
{
	public array $cookies = [];

	public function setCookie(string $name, string $value, array $options = []): Response
	{
		$this->cookies[] = [$name, $value, $options];

		return $this;
	}
}

class RememberMeCookieTest extends TestCase
{
	private array $server;
	private array $instances;
	private CookieRecordingResponse $response;

	protected function setUp(): void
	{
		$this->server = $_SERVER;
		unset($_SERVER['HTTPS'], $_SERVER['HTTP_X_FORWARDED_PROTO']);

		$property = new ReflectionProperty(Singleton::class, 'instances');
		$this->instances = $property->getValue();

		$this->response = (new ReflectionClass(CookieRecordingResponse::class))->newInstanceWithoutConstructor();
		$instances = $this->instances;
		$instances[Response::class] = $this->response;
		$property->setValue(null, $instances);
	}

	protected function tearDown(): void
	{
		$_SERVER = $this->server;
		(new ReflectionProperty(Singleton::class, 'instances'))->setValue(null, $this->instances);
	}

	private function setCookieOptions(): array
	{
		(new RememberMe())->setCookie();

		$this->assertCount(1, $this->response->cookies);
		[$name, $value, $options] = $this->response->cookies[0];
		$this->assertSame('goRememberMe', $name);
		$this->assertNotSame('', $value);

		return $options;
	}

	private function unsetCookieOptions(): array
	{
		RememberMe::unsetCookie();

		$this->assertCount(1, $this->response->cookies);
		[$name, $value, $options] = $this->response->cookies[0];
		$this->assertSame('goRememberMe', $name);
		$this->assertSame('', $value);

		return $options;
	}

	public function testCookieIsSecureOverHttps()
	{
		$_SERVER['HTTPS'] = 'on';

		$this->assertTrue($this->setCookieOptions()['secure']);
	}

	public function testCookieIsSecureBehindTlsTerminatingProxy()
	{
		$_SERVER['HTTP_X_FORWARDED_PROTO'] = 'https';

		$this->assertTrue($this->setCookieOptions()['secure']);
	}

	public function testCookieIsNotSecureOverPlainHttp()
	{
		// can't be secure or the browser would never send it back on a plain http development setup
		$this->assertFalse($this->setCookieOptions()['secure']);
	}

	public function testUnsetCookieHasSameFlagsAsSetCookie()
	{
		// a browser only replaces a cookie when the attributes match. A Secure cookie can't be removed by a non Secure one.
		$_SERVER['HTTPS'] = 'on';

		$unset = $this->unsetCookieOptions();
		$this->assertTrue($unset['secure']);
		$this->assertLessThan(time(), $unset['expires']);

		$this->response->cookies = [];
		$set = $this->setCookieOptions();

		foreach (['secure', 'path', 'domain', 'samesite', 'httpOnly'] as $key) {
			$this->assertSame($set[$key], $unset[$key], $key);
		}
	}

	public function testCookieIsHttpOnlyAndSameSite()
	{
		$options = $this->setCookieOptions();

		$this->assertTrue($options['httpOnly']);
		$this->assertSame('Lax', $options['samesite']);
		$this->assertSame('/', $options['path']);
		$this->assertGreaterThan(time(), $options['expires']);
		$this->assertIsInt($options['expires']);
	}
}
