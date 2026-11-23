<?php
namespace go\modules\community\dev\model;

use PHPUnit\Framework\TestCase;

/**
 * Translations are added to and changed in language files without reformatting them
 */
class LanguageFileTest extends TestCase
{
	private string $path;

	protected function setUp(): void
	{
		$this->path = go()->getTmpFolder()->getFile(uniqid('lang') . '/it.php')->getPath();
	}

	protected function tearDown(): void
	{
		if (is_file($this->path)) {
			unlink($this->path);
		}
		@rmdir(dirname($this->path));
	}

	private function write(string $content): void
	{
		if (!is_dir(dirname($this->path))) {
			mkdir(dirname($this->path), 0775, true);
		}
		file_put_contents($this->path, $content);
	}

	private function update(array $translations): array
	{
		$file = new LanguageFile($this->path);
		$file->set($translations);
		$this->assertTrue($file->save());
		return require $this->path;
	}

	public function testAddsToArraySyntaxWithoutTrailingComma(): void
	{
		$this->write("<?php\nreturn array (\n  'Save' => 'Salva'\n);\n");

		$data = $this->update(['Cancel' => 'Annulla']);

		$this->assertEquals(['Save' => 'Salva', 'Cancel' => 'Annulla'], $data);
		$this->assertStringContainsString("  'Save' => 'Salva',\n  'Cancel' => 'Annulla',\n);", file_get_contents($this->path));
	}

	public function testAddsToShortSyntaxWithTabs(): void
	{
		$this->write("<?php\n\nreturn [\n\t'Save' => 'Salva',\n];");

		$data = $this->update(['Cancel' => 'Annulla']);

		$this->assertEquals(['Save' => 'Salva', 'Cancel' => 'Annulla'], $data);
		$this->assertStringContainsString("\t'Cancel' => 'Annulla',\n];", file_get_contents($this->path));
	}

	public function testChangesExistingEntryInPlace(): void
	{
		$this->write("<?php\nreturn array (\n  'Book' => 'Prenotazione',\n  'Save' => 'Salva',\n);\n");

		$data = $this->update(['Book' => 'Registro']);

		$this->assertEquals(['Book' => 'Registro', 'Save' => 'Salva'], $data);
		$this->assertStringStartsWith("<?php\nreturn array (\n  'Book' => 'Registro',\n", file_get_contents($this->path));
	}

	/**
	 * The country "CC" (Cocos Islands) must not be changed when the abbreviation "CC" (carbon copy) is set
	 */
	public function testLeavesNestedEntriesAlone(): void
	{
		$this->write("<?php\nreturn array (\n  'countries' => array (\n    'CC' => 'Isole Cocos',\n  ),\n);\n");

		$data = $this->update(['CC' => 'CC']);

		$this->assertEquals('Isole Cocos', $data['countries']['CC']);
		$this->assertEquals('CC', $data['CC']);
	}

	public function testAddsMissingItemsToExistingArray(): void
	{
		$this->write("<?php\nreturn array (\n  'countries' => array (\n    'CC' => 'Isole Cocos',\n    'XX' => 'Paese (con) [parentesi]'\n  ),\n  'short_months' => [1 => 'Gen'],\n  'Save' => 'Salva',\n);\n");

		$data = $this->update([
			'countries' => ['CC' => 'Cocos', 'TP' => 'Timor Est'],
			'short_months' => [1 => 'Jan', 5 => 'Mag'],
			'dateTypes' => ['birthday' => 'Compleanno']
		]);

		// Existing items stay, missing ones are added
		$this->assertEquals(['CC' => 'Isole Cocos', 'XX' => 'Paese (con) [parentesi]', 'TP' => 'Timor Est'], $data['countries']);
		$this->assertEquals([1 => 'Gen', 5 => 'Mag'], $data['short_months']);
		$this->assertEquals('Salva', $data['Save']);
		$this->assertStringContainsString("    'TP' => 'Timor Est',\n  ),", file_get_contents($this->path));

		// A new array is indented like the rest of the file
		$this->assertEquals(['birthday' => 'Compleanno'], $data['dateTypes']);
		$this->assertStringContainsString("  'dateTypes' => array (\n    'birthday' => 'Compleanno',\n  ),\n", file_get_contents($this->path));
	}

	public function testQuotesAndCreatesNewFile(): void
	{
		$data = $this->update(["Don't" => "Non c'è", 'Back\\slash' => 'a\\b']);

		$this->assertEquals(["Don't" => "Non c'è", 'Back\\slash' => 'a\\b'], $data);
	}

	public function testHasSameTokens(): void
	{
		$this->assertTrue(LanguageFile::hasSameTokens('{count} items of %s', '%s: {count} voci'));
		$this->assertFalse(LanguageFile::hasSameTokens('{count} items', '{numero} voci'));
		$this->assertFalse(LanguageFile::hasSameTokens('<b>Bold</b>', 'Grassetto'));
	}
}
