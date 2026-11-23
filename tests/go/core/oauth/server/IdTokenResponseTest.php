<?php
namespace go\core\oauth\server;

use PHPUnit\Framework\TestCase;

/**
 * The issuer of the OpenID Connect server must not come from the "Host" header, which anyone can choose.
 */
class IdTokenResponseTest extends TestCase
{
	private array $server;
	private ?string $url;

	protected function setUp(): void
	{
		$this->server = $_SERVER;
		$this->url = go()->getSettings()->URL;
	}

	protected function tearDown(): void
	{
		$_SERVER = $this->server;
		go()->getSettings()->URL = $this->url;
	}

	public function testIssuerComesFromSettings(): void
	{
		go()->getSettings()->URL = 'https://groupoffice.example.com/groupoffice/';
		$_SERVER['HTTP_HOST'] = 'evil.example.net';

		$this->assertSame('https://groupoffice.example.com', IdTokenResponse::issuer());
	}

	public function testIssuerKeepsPort(): void
	{
		go()->getSettings()->URL = 'https://localhost:8443/';

		$this->assertSame('https://localhost:8443', IdTokenResponse::issuer());
	}

	/**
	 * MCP clients fetch the server metadata from the issuer, so an https issuer on an http installation can't be reached
	 */
	public function testIssuerKeepsHttpScheme(): void
	{
		go()->getSettings()->URL = 'http://localhost:8080/';
		$_SERVER['HTTPS'] = 'on';

		$this->assertSame('http://localhost:8080', IdTokenResponse::issuer());
	}
}
