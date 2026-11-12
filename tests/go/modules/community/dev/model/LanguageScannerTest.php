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
}
