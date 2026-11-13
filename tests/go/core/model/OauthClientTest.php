<?php
namespace go\core\model;

use PHPUnit\Framework\TestCase;

class OauthClientTest extends TestCase
{
	public function testSecretIsHashedWithConfiguredCost(): void
	{
		$client = new OauthClient();
		$client->setSecret('client-secret');

		$hash = (new \ReflectionProperty(OauthClient::class, 'secret'))->getValue($client);
		$this->assertSame(4, password_get_info($hash)['options']['cost']);
		$this->assertTrue($client->checkSecret('client-secret'));
		$this->assertFalse($client->checkSecret('wrong'));
	}

	public function testSecretMadeWithTheOldAlgorithmStillWorks(): void
	{
		// Secrets used to be hashed with password_hash($secret, CRYPT_BLOWFISH)
		$client = new OauthClient();
		(new \ReflectionProperty(OauthClient::class, 'secret'))->setValue($client, password_hash('client-secret', CRYPT_BLOWFISH, ['cost' => 4]));

		$this->assertTrue($client->checkSecret('client-secret'));
		$this->assertFalse($client->checkSecret('wrong'));
	}
}
