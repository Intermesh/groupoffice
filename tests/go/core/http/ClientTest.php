<?php

namespace go\core\http;

use go\core\exception\Forbidden;
use go\core\fs\File;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * A client that treats the host name "public.test" as a public address pointing at the local test server. All other
 * addresses are checked like in the real client.
 */
class PublicTestHostClient extends Client
{
	const PUBLIC_HOST = 'public.test';

	// Linux routes all of 127.0.0.0/8 to the loopback interface, so the test server is reachable on this address too
	const PUBLIC_HOST_IP = '127.0.0.2';

	protected function resolveHost(string $host): array
	{
		return $host === self::PUBLIC_HOST ? [self::PUBLIC_HOST_IP] : parent::resolveHost($host);
	}

	protected function isAllowedAddress(string $ip): bool
	{
		return $ip === self::PUBLIC_HOST_IP || parent::isAllowedAddress($ip);
	}
}

/**
 * Resolves every host name to the public test address, like DNS of an attacker or a search domain could do. curl
 * reads some of these names as IP addresses, so it must not connect to what it reads itself.
 */
class AnyNameResolvesPublicClient extends PublicTestHostClient
{
	protected function resolveHost(string $host): array
	{
		return filter_var($host, FILTER_VALIDATE_IP) ? parent::resolveHost($host) : [self::PUBLIC_HOST_IP];
	}
}

/**
 * When globalRangeOnly is enabled, the client may only reach global addresses, also when it's redirected.
 */
class ClientTest extends TestCase
{
	private static $server;
	private static int $port;
	private static string $docRoot;

	public static function setUpBeforeClass(): void
	{
		self::$docRoot = sys_get_temp_dir() . '/go-client-test-' . uniqid();
		mkdir(self::$docRoot);
		file_put_contents(self::$docRoot . '/router.php', <<<'PHP'
<?php
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
if ($path === '/redirect') {
	header('Location: ' . $_GET['to'], true, 302);
	echo 'redirect body';
	return;
}
if ($path === '/large') {
	echo str_repeat('x', 100000);
	return;
}
echo 'content of ' . $path;
PHP
		);

		$socket = stream_socket_server('tcp://127.0.0.1:0');
		self::$port = (int) substr(strrchr(stream_socket_get_name($socket, false), ':'), 1);
		fclose($socket);

		self::$server = proc_open(
			[PHP_BINARY, '-S', '0.0.0.0:' . self::$port, self::$docRoot . '/router.php'],
			[['pipe', 'r'], ['file', '/dev/null', 'w'], ['file', '/dev/null', 'w']],
			$pipes
		);

		for ($i = 0; $i < 50; $i++) {
			$c = @fsockopen('127.0.0.1', self::$port);
			if ($c) {
				fclose($c);
				return;
			}
			usleep(100000);
		}
		self::fail('Test HTTP server did not start');
	}

	public static function tearDownAfterClass(): void
	{
		proc_terminate(self::$server);
		proc_close(self::$server);
		unlink(self::$docRoot . '/router.php');
		rmdir(self::$docRoot);
	}

	private function url(string $host, string $path): string
	{
		return 'http://' . $host . ':' . self::$port . $path;
	}

	private function client(): Client
	{
		$client = new PublicTestHostClient();
		$client->globalRangeOnly = true;
		return $client;
	}

	public static function internalUrls(): array
	{
		return [
			'loopback' => ['http://127.0.0.1/'],
			'loopback v6' => ['http://[::1]/'],
			'private' => ['http://10.0.0.1/'],
			'metadata' => ['http://169.254.169.254/latest/meta-data/'],
			'localhost' => ['http://localhost/'],
			'IPv4 mapped' => ['http://[::ffff:127.0.0.1]/'],
			'NAT64' => ['http://[64:ff9b::7f00:1]/'],
			'other scheme' => ['ftp://example.com/'],
		];
	}

	#[DataProvider('internalUrls')]
	public function testInternalUrlIsRefused(string $url)
	{
		$this->expectException(Forbidden::class);
		$this->client()->get($url);
	}

	public static function hostsCurlReadsAsInternal(): array
	{
		return [
			'percent encoded' => ['%31%32%37.0.0.1'],
			'decimal' => ['2130706433'],
			'hexadecimal' => ['0x7f.0.0.1'],
			'short' => ['127.1'],
		];
	}

	#[DataProvider('hostsCurlReadsAsInternal')]
	public function testHostThatCurlReadsAsInternalAddressIsRefused(string $host)
	{
		$client = new AnyNameResolvesPublicClient();
		$client->globalRangeOnly = true;

		try {
			$response = $client->get($this->url($host, '/secret'));
			$this->fail('Requested ' . $host . ': ' . $response['body']);
		} catch (Forbidden $e) {
			$this->addToAssertionCount(1);
		}
	}

	public function testBackslashInUrlIsRefused()
	{
		$this->expectException(Forbidden::class);
		$this->client()->get('http://' . PublicTestHostClient::PUBLIC_HOST . '\\@127.0.0.1:' . self::$port . '/secret');
	}

	public function testPublicUrlIsRequested()
	{
		$response = $this->client()->get($this->url(PublicTestHostClient::PUBLIC_HOST, '/page'));

		$this->assertSame(200, $response['status']);
		$this->assertSame('content of /page', $response['body']);
	}

	public function testRedirectToPublicUrlIsFollowed()
	{
		$to = $this->url(PublicTestHostClient::PUBLIC_HOST, '/target');
		$response = $this->client()->get($this->url(PublicTestHostClient::PUBLIC_HOST, '/redirect?to=' . urlencode($to)));

		$this->assertSame(200, $response['status']);
		$this->assertSame('content of /target', $response['body']);
	}

	public function testRelativeRedirectIsFollowed()
	{
		$response = $this->client()->get($this->url(PublicTestHostClient::PUBLIC_HOST, '/redirect?to=' . urlencode('/target')));

		$this->assertSame('content of /target', $response['body']);
	}

	public function testRedirectToInternalUrlIsRefused()
	{
		$to = $this->url('127.0.0.1', '/secret');

		$this->expectException(Forbidden::class);
		$this->client()->get($this->url(PublicTestHostClient::PUBLIC_HOST, '/redirect?to=' . urlencode($to)));
	}

	public function testDownloadRedirectToInternalUrlIsRefused()
	{
		$to = $this->url('127.0.0.1', '/secret');
		$file = File::tempFile('txt');

		try {
			$this->client()->download($this->url(PublicTestHostClient::PUBLIC_HOST, '/redirect?to=' . urlencode($to)), $file);
			$this->fail('Download followed a redirect to an internal address');
		} catch (Forbidden $e) {
			$this->assertStringNotContainsString('content of /secret', (string) @file_get_contents($file->getPath()));
		} finally {
			$file->delete();
		}
	}

	public function testDownloadFollowsRedirectWithoutRedirectBody()
	{
		$to = $this->url(PublicTestHostClient::PUBLIC_HOST, '/target');
		$file = File::tempFile('txt');

		try {
			$this->client()->download($this->url(PublicTestHostClient::PUBLIC_HOST, '/redirect?to=' . urlencode($to)), $file);
			$this->assertStringEqualsFile($file->getPath(), 'content of /target');
		} finally {
			$file->delete();
		}
	}

	public function testTooManyRedirects()
	{
		$url = $this->url(PublicTestHostClient::PUBLIC_HOST, '/target');
		for ($i = 0; $i < 3; $i++) {
			$url = $this->url(PublicTestHostClient::PUBLIC_HOST, '/redirect?to=' . urlencode($url));
		}
		$client = $this->client();
		$client->maxRedirects = 2;

		$this->expectExceptionMessage('Too many redirects');
		$client->get($url);
	}

	public function testMaxResponseSize()
	{
		$client = $this->client();
		$client->maxResponseSize = 1000;

		$this->expectException(\Exception::class);
		$client->get($this->url(PublicTestHostClient::PUBLIC_HOST, '/large'));
	}

	public function testRedirectsAreFollowedWithoutGlobalRangeOnly()
	{
		// Internal services like WOPI with wopiAllowInternal keep working
		$client = new Client();
		$to = $this->url('127.0.0.1', '/target');
		$response = $client->get($this->url('127.0.0.1', '/redirect?to=' . urlencode($to)));

		$this->assertSame('content of /target', $response['body']);
	}
}
