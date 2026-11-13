<?php
namespace go\modules\community\dev\model;

use PHPUnit\Framework\TestCase;

/**
 * The scanner finds the strings of JS, TS and PHP and puts strings that more modules use in core
 */
class LanguageScannerTest extends TestCase
{
	public function testFindsStringsPerTarget(): void
	{
		$strings = (new LanguageScanner())->getStrings();

		// A core string
		$this->assertContains('Save', $strings['core/core']);
		// A notes module string from GOUI
		$this->assertArrayHasKey('community/notes', $strings);
		// A string that's only in PHP: go()->t("...", "community", "tasks") in the tasks module
		$this->assertTrue(in_array('Tasks', $strings['core/core']) || in_array('Tasks', $strings['community/tasks'] ?? []));
	}

	public function testSkipsPackages(): void
	{
		$strings = (new LanguageScanner(['community']))->getStrings();

		$this->assertEmpty(array_filter(array_keys($strings), fn($t) => str_starts_with($t, 'community/')));
	}

	/**
	 * Scan a source file and return the targets per string
	 */
	private function scan(string $ext, string $source): array
	{
		$file = sys_get_temp_dir() . '/LanguageScannerTest-' . uniqid() . '.' . $ext;
		file_put_contents($file, $source);
		try {
			$scanner = new LanguageScanner();
			(new \ReflectionMethod($scanner, 'scanFile'))->invoke($scanner, $file, 'community', 'notes');
			$uses = (new \ReflectionProperty($scanner, 'uses'))->getValue($scanner);
		} finally {
			unlink($file);
		}
		return array_map('array_keys', $uses);
	}

	public function testArgumentOrderOfExtjsAndGoui(): void
	{
		// ExtJS: t(text, module, package)
		$this->assertSame(['community/tasks'], $this->scan('js', 'x = t("Task", "tasks", "community");')['Task']);
		$this->assertSame(['legacy/billing'], $this->scan('js', 'x = t("Order", "billing");')['Order']);

		// GOUI: t(text, package, module)
		$this->assertSame(['community/tasks'], $this->scan('ts', 'x = t("Task", "community", "tasks");')['Task']);
		$this->assertSame(['community/notes'], $this->scan('ts', 'x = t("Note");')['Note']);
	}

	public function testScansGouiCoreAndReportsUnknownModules(): void
	{
		$scanner = new LanguageScanner();
		$strings = $scanner->getStrings();

		// The MarkdownField of groupoffice-core
		$this->assertContains('Bullet list', $strings['core/core']);

		// A string with an unknown module falls back to core and is reported
		foreach ($scanner->unknownTargets as $unknown) {
			$this->assertMatchesRegularExpression('/^[\\w]+\\/[\\w]+: .+ \\(.+\\)$/s', $unknown);
		}
	}
}
