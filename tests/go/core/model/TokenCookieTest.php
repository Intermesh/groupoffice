<?php
namespace go\core\model;

use go\core\http\Request;
use go\core\http\Response;
use go\core\Singleton;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionProperty;

require_once __DIR__ . '/CookieRecordingResponse.php';

class TokenCookieTest extends TestCase
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

	private function setCookie(): array
	{
		$token = new Token();
		$token->accessToken = 'test-access-token';
		$token->setCookie();

		return $this->response->cookies;
	}

	/**
	 * Returns the cookie with a value or the removal cookie without a domain
	 */
	private function hostOnlyCookie(array $cookies): array
	{
		$hostOnly = array_values(array_filter($cookies, fn($c) => !isset($c[2]['domain'])));
		$this->assertCount(1, $hostOnly);

		return $hostOnly[0];
	}

	public function testCookieIsHostOnly()
	{
		// A domain attribute is rejected for localhost and differs from the address bar behind a proxy. The browser
		// then keeps sending an old cookie which results in a CSRF token mismatch.
		[$name, $value, $options] = $this->hostOnlyCookie($this->setCookie());

		$this->assertSame('accessToken', $name);
		$this->assertSame('test-access-token', $value);
		$this->assertSame(0, $options['expires']);
		$this->assertTrue($options['httpOnly']);
		$this->assertSame('Lax', $options['samesite']);
		$this->assertSame('/', $options['path']);
		$this->assertFalse($options['secure']);
	}

	public function testCookieIsSecureOverHttps()
	{
		$_SERVER['HTTPS'] = 'on';

		[, , $options] = $this->hostOnlyCookie($this->setCookie());
		$this->assertTrue($options['secure']);
	}

	public function testLegacyDomainCookieIsRemovedOnSet()
	{
		// Older versions set the cookie with a domain. The browser would send both and PHP uses the old one.
		$cookies = $this->setCookie();
		$this->assertCount(2, $cookies);

		[$name, $value, $options] = $cookies[0];
		$this->assertSame('accessToken', $name);
		$this->assertSame('', $value);
		$this->assertSame(Request::get()->getHost(), $options['domain']);
		$this->assertLessThan(time(), $options['expires']);
	}

	public function testUnsetCookieRemovesBothVariants()
	{
		Token::unsetCookie();

		$this->assertCount(2, $this->response->cookies);
		foreach ($this->response->cookies as [$name, $value, $options]) {
			$this->assertSame('accessToken', $name);
			$this->assertSame('', $value);
			$this->assertLessThan(time(), $options['expires']);
		}

		[, , $unset] = $this->hostOnlyCookie($this->response->cookies);

		$this->response->cookies = [];
		[, , $set] = $this->hostOnlyCookie($this->setCookie());

		// a browser only replaces a cookie when the attributes match
		foreach (['secure', 'path', 'samesite', 'httpOnly'] as $key) {
			$this->assertSame($set[$key], $unset[$key], $key);
		}
	}
}
