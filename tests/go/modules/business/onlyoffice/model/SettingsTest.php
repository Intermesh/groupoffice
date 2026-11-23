<?php
namespace go\modules\business\onlyoffice\model;

use go\core\auth\TemporaryState;
use go\core\model\User;
use PHPUnit\Framework\TestCase;

/**
 * Secrets in module settings, like the JWT secret of the document server, are write only. They must not be sent to
 * the browser, like in the settings of Module/get, but stay usable on the server.
 */
class SettingsTest extends TestCase
{
	private $authState;
	private string $secret;
	private static bool $installed = false;

	public static function setUpBeforeClass(): void
	{
		if (!go()->getModule('business', 'onlyoffice')) {
			$state = go()->getAuthState();
			go()->setAuthState(new TemporaryState(User::ID_SUPER_ADMIN));
			try {
				self::assertNotFalse(\go\modules\business\onlyoffice\Module::get()->install(), 'Could not install onlyoffice module');
				self::$installed = true;
			} finally {
				go()->setAuthState($state);
			}
		}
	}

	public static function tearDownAfterClass(): void
	{
		if (self::$installed) {
			$state = go()->getAuthState();
			go()->setAuthState(new TemporaryState(User::ID_SUPER_ADMIN));
			\go\modules\business\onlyoffice\Module::get()->uninstall();
			go()->setAuthState($state);
		}
	}

	protected function setUp(): void
	{
		$this->authState = go()->getAuthState();
		go()->setAuthState(new TemporaryState(User::ID_SUPER_ADMIN));

		$this->secret = Settings::get()->documentServerSecret;
		$settings = Settings::get();
		$settings->documentServerSecret = 'jwtSecretForTest';
		$this->assertTrue($settings->save());
	}

	protected function tearDown(): void
	{
		$settings = Settings::get();
		$settings->documentServerSecret = $this->secret;
		$settings->save();

		go()->setAuthState($this->authState);
	}

	public function testSecretIsNotSentToTheClient()
	{
		$settings = Settings::get();
		$this->assertNull($settings->toArray()['documentServerSecret']);
		$this->assertStringNotContainsString('jwtSecretForTest', json_encode($settings));
	}

	public function testSecretCanBeUsedOnTheServer()
	{
		Settings::flushCache();
		go()->getCache()->delete(Settings::class);
		$this->assertSame('jwtSecretForTest', Settings::get()->documentServerSecret);
	}
}
