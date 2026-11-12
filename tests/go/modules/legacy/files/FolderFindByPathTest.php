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
}
