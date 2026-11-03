<?php
namespace GO\Base\Fs;

use PHPUnit\Framework\TestCase;

/**
 * GO::init() used to set umask(0), which made files created without an explicit chmod (go\core\fs, z-push state)
 * world-writable. Now the system umask is used and file_create_mode / folder_create_mode are applied explicitly by the
 * legacy file system classes that the files module uses.
 */
class UmaskTest extends TestCase
{
	private int $umask;
	private string $folderMode;

	protected function setUp(): void
	{
		$this->umask = umask();
		$this->folderMode = \GO::config()->folder_create_mode;
	}

	protected function tearDown(): void
	{
		umask($this->umask);
		\GO::config()->folder_create_mode = $this->folderMode;

		date_default_timezone_set("UTC");
	}

	/**
	 * GO::init() throws when it is called twice in one process
	 */
	private static function init(): void
	{
		$initialized = new \ReflectionProperty(\GO::class, 'initialized');
		if (!$initialized->getValue()) {
			\GO::init();
		}
	}

	public function testInitKeepsSystemUmask(): void
	{
		umask(0022);
		self::init();
		$this->assertSame(0022, umask());
	}

	public function testFolderCreateModeIsAppliedDespiteUmask(): void
	{
		umask(0022);
		\GO::config()->folder_create_mode = '0775';

		$folder = new Folder(\GO::config()->getTempFolder()->path() . '/' . uniqid('umasktest'));
		$folder->create();
		clearstatcache();
		$perms = fileperms($folder->path()) & 0777;
		rmdir($folder->path());

		$this->assertSame(0775, $perms, sprintf('Folder was created with mode %o', $perms));
	}

}
