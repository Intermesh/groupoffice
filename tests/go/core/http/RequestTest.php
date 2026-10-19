<?php
namespace go\core\http;

use GO\Base\Util\Http;
use PHPUnit\Framework\TestCase;

class RequestTest extends TestCase
{
	private array $server;
	private array $config;

	protected function setUp(): void
	{
		$this->server = $_SERVER;
		$this->config = go()->getConfig();
		unset($_SERVER['HTTP_X_FORWARDED_FOR'], $_SERVER['HTTP_CLIENT_IP']);
	}

	protected function tearDown(): void
	{
		$_SERVER = $this->server;
		go()->setConfig($this->config);
	}

	private function request(string $remoteAddr, ?string $forwardedFor = null, array|string $trustedProxies = [], ?string $clientIp = null): ?string
	{
		$_SERVER['REMOTE_ADDR'] = $remoteAddr;
		if ($forwardedFor !== null) {
			$_SERVER['HTTP_X_FORWARDED_FOR'] = $forwardedFor;
		}
		if ($clientIp !== null) {
			$_SERVER['HTTP_CLIENT_IP'] = $clientIp;
		}
		go()->setConfig(array_merge($this->config, ['trustedProxies' => $trustedProxies]));

		return Request::get()->getRemoteIpAddress();
	}

	public function testUsesConnectionAddress()
	{
		$this->assertSame('203.0.113.9', $this->request('203.0.113.9'));
	}

	public function testForwardedHeadersAreIgnoredByDefault()
	{
		// the attacker picks a different address for each request to get around throttling
		$this->assertSame('203.0.113.9', $this->request('203.0.113.9', '1.2.3.4'));
		$this->assertSame('203.0.113.9', $this->request('203.0.113.9', '5.6.7.8, 9.9.9.9'));
		$this->assertSame('203.0.113.9', $this->request('203.0.113.9', '1.2.3.4', clientIp: '8.8.8.8'));
	}

	public function testForwardedHeaderIgnoredWhenConnectionIsNotATrustedProxy()
	{
		$this->assertSame('203.0.113.9', $this->request('203.0.113.9', '1.2.3.4', ['10.0.0.0/8']));
	}

	public function testClientIpHeaderIsNeverUsed()
	{
		$this->assertSame('203.0.113.9', $this->request('203.0.113.9', null, ['203.0.113.0/24'], '8.8.8.8'));
		$this->assertSame('198.51.100.7', $this->request('10.0.0.1', '198.51.100.7', ['10.0.0.0/8'], '8.8.8.8'));
	}

	public function testTrustedProxyForwardsClient()
	{
		$this->assertSame('198.51.100.7', $this->request('10.0.0.1', '198.51.100.7', ['10.0.0.0/8']));
	}

	public function testClientCannotPrependAddressesBehindTrustedProxy()
	{
		// the proxy appends the address it saw: the client's 198.51.100.7. What's before that is client supplied.
		$this->assertSame('198.51.100.7', $this->request('10.0.0.1', '1.2.3.4, 198.51.100.7', ['10.0.0.0/8']));
	}

	public function testChainOfTrustedProxies()
	{
		$this->assertSame(
			'198.51.100.7',
			$this->request('10.0.0.1', '198.51.100.7, 10.0.0.2', ['10.0.0.0/8'])
		);
		$this->assertSame(
			'198.51.100.7',
			$this->request('10.0.0.1', '1.2.3.4, 198.51.100.7, 10.0.0.2', ['10.0.0.0/8'])
		);
	}

	public function testUntrustedProxyInChainStopsTheWalk()
	{
		// 203.0.113.50 is not ours so what it claims about the client isn't trusted
		$this->assertSame(
			'203.0.113.50',
			$this->request('10.0.0.1', '1.2.3.4, 203.0.113.50, 10.0.0.2', ['10.0.0.0/8'])
		);
	}

	public function testInvalidForwardedAddressIsIgnored()
	{
		$this->assertSame('10.0.0.1', $this->request('10.0.0.1', 'not-an-ip', ['10.0.0.0/8']));
		$this->assertSame('10.0.0.1', $this->request('10.0.0.1', '198.51.100.7, garbage', ['10.0.0.0/8']));
	}

	public function testTrustedProxiesCanBeACommaSeparatedString()
	{
		$this->assertSame('198.51.100.7', $this->request('192.168.1.1', '198.51.100.7', '10.0.0.0/8, 192.168.0.0/16'));
	}

	public function testIpv6()
	{
		$this->assertSame('2001:db8::5', $this->request('fd00::1', '2001:db8::5', ['fd00::/8']));
		$this->assertSame('fd00::1', $this->request('fd00::1', '2001:db8::5', ['10.0.0.0/8']));
	}

	public function testNoConnectionAddress()
	{
		unset($_SERVER['REMOTE_ADDR']);
		go()->setConfig(array_merge($this->config, ['trustedProxies' => ['10.0.0.0/8']]));
		$_SERVER['HTTP_X_FORWARDED_FOR'] = '198.51.100.7';

		$this->assertNull(Request::get()->getRemoteIpAddress());
	}

	public function testLegacyClientIpUsesTheSameLogic()
	{
		$this->assertSame('203.0.113.9', $this->request('203.0.113.9', '1.2.3.4'));
		$this->assertSame('203.0.113.9', Http::getClientIp());

		$this->assertSame('198.51.100.7', $this->request('10.0.0.1', '1.2.3.4, 198.51.100.7', ['10.0.0.0/8']));
		$this->assertSame('198.51.100.7', Http::getClientIp());
	}
}
