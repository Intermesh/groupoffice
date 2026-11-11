<?php

namespace go\modules\legacy\files;

use GO\Files\Model\Folder;
use PHPUnit\Framework\TestCase;

/**
 * Folder::findByPath() ignores permissions while it looks up the folders. It must always switch that back, also when
 * the folder doesn't exist. Otherwise permissions are ignored for the rest of the request.
 */
class FolderFindByPathTest extends TestCase
{
	private bool $ignoreAcl;

	protected function setUp(): void
	{
		$this->ignoreAcl = \GO::$ignoreAclPermissions;
		\GO::$ignoreAclPermissions = false;
	}

	protected function tearDown(): void
	{
		\GO::$ignoreAclPermissions = $this->ignoreAcl;
	}

	public static function missingPaths(): array
	{
		return [
			'missing top level folder' => ['does-not-exist-' . uniqid() . '/sub', false],
			'missing sub folder' => ['users/does-not-exist-' . uniqid(), false],
			'missing sub folder, return last found' => ['users/does-not-exist-' . uniqid(), true],
		];
	}

	#[\PHPUnit\Framework\Attributes\DataProvider('missingPaths')]
	public function testPermissionsAreNotIgnoredAfterMissingPath(string $path, bool $returnLastFound): void
	{
		Folder::model()->findByPath($path, false, [], $returnLastFound);

		$this->assertFalse(\GO::$ignoreAclPermissions);
	}

	public function testPermissionsAreNotIgnoredAfterExistingPath(): void
	{
		$this->assertNotEmpty(Folder::model()->findByPath('users'));

		$this->assertFalse(\GO::$ignoreAclPermissions);
	}

	/**
	 * Creating a folder fails when the name is the decomposed (NFD) spelling of an existing folder, because the name is
	 * normalized to NFC before saving. Permissions must still be checked after that exception.
	 */
	public function testPermissionsAreNotIgnoredAfterFailingAutoCreate(): void
	{
		\GO::$ignoreAclPermissions = true;
		$parent = Folder::model()->findByPath('users/findbypath-' . uniqid(), true);
		$existing = $parent->addFolder("\u{00E9}t\u{00E9}");
		\GO::$ignoreAclPermissions = false;

		$error = null;
		try {
			Folder::model()->findByPath($parent->path . "/e\u{0301}te\u{0301}/sub", true);
		} catch (\Exception $e) {
			$error = $e;
		}
		$ignoredAfterwards = \GO::$ignoreAclPermissions;

		\GO::$ignoreAclPermissions = true;
		$parent->delete(true);
		\GO::$ignoreAclPermissions = false;

		$this->assertNotEmpty($existing->id);
		$this->assertNotNull($error, 'Creating a folder with the NFD spelling of an existing folder name did not fail');
		$this->assertStringContainsString(\GO::t("The folder already exists", "files"), $error->getMessage());
		$this->assertFalse($ignoredAfterwards);
	}
}
