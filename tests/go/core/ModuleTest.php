<?php
namespace go\core;

use go\core\model\Module as ModuleModel;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * The download and page methods of a module may only be used when the module is installed and enabled.
 */
class ModuleTest extends TestCase
{
	public function testInstalledModule(): void
	{
		$this->assertSame(\go\modules\community\notes\Module::class, Module::findClassForRequest('community', 'notes'));
	}

	public function testModuleThatIsNotInstalled(): void
	{
		if (ModuleModel::isInstalled('community', 'apikeys')) {
			$this->markTestSkipped('The apikeys module is installed');
		}
		$this->assertTrue(class_exists(\go\modules\community\apikeys\Module::class));

		$this->assertNull(Module::findClassForRequest('community', 'apikeys'));
	}

	public function testDisabledModule(): void
	{
		$db = go()->getDbConnection();
		$this->assertTrue($db->update('core_module', ['enabled' => false], ['package' => 'community', 'name' => 'notes'])->execute());
		try {
			$this->assertNull(Module::findClassForRequest('community', 'notes'));
		} finally {
			$db->update('core_module', ['enabled' => true], ['package' => 'community', 'name' => 'notes'])->execute();
		}
	}

	public static function invalidModules(): array
	{
		return [
			'namespace separator' => ['community\\notes', 'notes'],
			'path' => ['..', 'notes'],
			'empty' => ['community', ''],
			'does not exist' => ['community', 'doesnotexist'],
		];
	}

	#[DataProvider('invalidModules')]
	public function testInvalidModule(string $package, string $name): void
	{
		$this->assertNull(Module::findClassForRequest($package, $name));
	}
}
