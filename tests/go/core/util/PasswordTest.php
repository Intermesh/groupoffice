<?php
namespace go\core\util;

use PHPUnit\Framework\TestCase;

class PasswordTest extends TestCase
{
	/**
	 * The tests hash with the low cost of tests/config.php. Without the option PHP's default is used.
	 */
	public function testHashUsesConfiguredCost(): void
	{
		$config = go()->getConfig();
		$this->assertSame(4, $config['passwordHashCost']);

		$hash = Password::hash('sdfTgs-Dfgg-$#ddg');
		$this->assertSame(4, password_get_info($hash)['options']['cost']);
		$this->assertTrue(password_verify('sdfTgs-Dfgg-$#ddg', $hash));
		$this->assertFalse(password_verify('wrong', $hash));

		try {
			go()->setConfig(array_merge($config, ['passwordHashCost' => null]));
			$default = password_get_info(password_hash('x', PASSWORD_DEFAULT))['options']['cost'];
			$this->assertSame($default, password_get_info(Password::hash('sdfTgs-Dfgg-$#ddg'))['options']['cost']);
		} finally {
			go()->setConfig($config);
		}
	}
}
