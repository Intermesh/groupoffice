<?php
namespace go\modules\business\projects3\dav;

use go\modules\business\projects3\model\ProjectBook;
use PHPUnit\Framework\TestCase;
use Sabre\DAV\Exception\Forbidden;

/**
 * The project folders in WebDAV are generated from the project books. They all point at the folder with the files of
 * all projects, so renaming one with a WebDAV MOVE would rename that folder on disk for everyone.
 */
class DirectoryRenameTest extends TestCase
{
	private string $dataPath;

	protected function setUp(): void
	{
		$this->dataPath = go()->getDataFolder()->getFolder('projects3/Project3')->getPath();
	}

	private function book(): ProjectBook
	{
		// Only passed to the directories. The projects3 module needs a license, so its tables may not exist.
		return (new \ReflectionClass(ProjectBook::class))->newInstanceWithoutConstructor();
	}

	public static function directories(): array
	{
		return [
			'projects' => ['root'],
			'book' => ['book'],
			'year' => ['year'],
		];
	}

	#[\PHPUnit\Framework\Attributes\DataProvider('directories')]
	public function testCannotBeRenamed(string $type): void
	{
		$dir = match ($type) {
			'root' => new ProjectRootDirectory(),
			'book' => new ProjectBookDirectory($this->book()),
			'year' => new YearDirectory($this->book(), 2024),
		};

		try {
			$dir->setName('renamed');
			$this->fail('The ' . $type . ' folder was renamed');
		} catch (Forbidden $e) {
			$this->assertDirectoryDoesNotExist(dirname($this->dataPath) . '/renamed');
		}
	}
}
