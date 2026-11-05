<?php
namespace go\modules\legacy\dav;

use go\core\auth\TemporaryState;
use go\core\model\User;
use GO\Base\Exception\AccessDenied;
use GO\Base\Model\Acl;
use GO\Dav\Fs\Directory;
use GO\Dav\Fs\File;
use GO\Files\Model\Folder;
use PHPUnit\Framework\TestCase;
use Sabre\DAV\Exception\Forbidden;
use Sabre\DAV\Exception\NotFound;

/**
 * The permissions of the files in WebDAV. The folder is shared with a reader (read permission) and a writer (write
 * permission). A third user has no access at all.
 */
class FsPermissionsTest extends TestCase
{
	private const ORIGINAL = 'ORIGINAL CONTENT';

	private $authState;
	private bool $ignoreAcl;
	private bool $disableModelCache;

	private User $reader;
	private User $writer;
	private User $nobody;

	private Folder $folder;
	private Folder $restricted;
	private array $acls = [];
	private string $relPath;
	private string $restrictedRelPath;

	protected function setUp(): void
	{
		if (!\GO::modules()->isInstalled('files')) {
			$this->markTestSkipped('The files module is not installed');
		}

		$this->authState = go()->getAuthState();
		$this->ignoreAcl = \GO::$ignoreAclPermissions;
		$this->disableModelCache = \GO::$disableModelCache;

		// Models remember the permission level of the user they were first used by. The users change in this test.
		\GO::$disableModelCache = true;

		$this->reader = $this->newUser();
		$this->writer = $this->newUser();
		$this->nobody = $this->newUser();

		$this->actAs(User::findById(User::ID_SUPER_ADMIN));
		\GO::$ignoreAclPermissions = true;

		$home = Folder::model()->findHomeFolder(\GO::user());
		$this->folder = $home->addFolder('dav-permissions-' . uniqid());
		$this->shareWith($this->folder, [
			$this->reader->getPersonalGroup()->id => Acl::READ_PERMISSION,
			$this->writer->getPersonalGroup()->id => Acl::WRITE_PERMISSION
		]);

		$this->relPath = $this->folder->path;
		$this->createFile($this->folder, 'secret.txt', self::ORIGINAL);

		// a folder with permissions of its own inside the shared folder. Only the owner can read it.
		$this->restricted = $this->folder->addFolder('restricted');
		$this->shareWith($this->restricted, []);
		$this->restrictedRelPath = $this->restricted->path;
		$this->createFile($this->restricted, 'inner.txt', 'INNER CONTENT');

		\GO::$ignoreAclPermissions = false;
	}

	protected function tearDown(): void
	{
		if (!isset($this->folder)) {
			return;
		}

		go()->setAuthState($this->authState);
		\GO::session()->setCurrentUser(User::ID_SUPER_ADMIN);
		\GO::$ignoreAclPermissions = true;

		$this->folder->delete(true);
		foreach ($this->acls as $acl) {
			$acl->delete();
		}

		\GO::$ignoreAclPermissions = $this->ignoreAcl;
		\GO::$disableModelCache = $this->disableModelCache;

		foreach ([$this->reader, $this->writer, $this->nobody] as $user) {
			User::delete(['id' => $user->id]);
		}
	}

	private function newUser(): User
	{
		$name = uniqid('dav');
		$user = new User();
		$user->setValues([
			'username' => $name,
			'displayName' => $name,
			'email' => $name . '@example.com',
			'password' => 'Pa55word!!xyz'
		]);
		$this->assertTrue($user->save(), var_export($user->getValidationErrors(), true));

		return $user;
	}

	/**
	 * @param array $groups [groupId => permission level]
	 */
	private function shareWith(Folder $folder, array $groups): void
	{
		$acl = new Acl();
		$acl->usedIn = 'fs_folders.acl_id';
		$acl->ownedBy = User::ID_SUPER_ADMIN;
		$acl->save();
		foreach ($groups as $groupId => $level) {
			$acl->addGroup($groupId, $level);
		}
		$this->acls[] = $acl;

		$folder->acl_id = $acl->id;
		$folder->save();
	}

	private function createFile(Folder $folder, string $name, string $content): void
	{
		(new \GO\Base\Fs\File($folder->fsFolder->path() . '/' . $name))->putContents($content);
		$folder->addFile($name);
	}

	private function actAs(User $user): void
	{
		go()->setAuthState(new TemporaryState($user->id));
		\GO::session()->setCurrentUser($user->id);
		Folder::model()->clearFolderCache();
	}

	private function stream(string $content)
	{
		$stream = fopen('php://memory', 'r+');
		fwrite($stream, $content);
		rewind($stream);

		return $stream;
	}

	private function contentOnDisk(): string
	{
		return file_get_contents(\GO::config()->file_storage_path . $this->relPath . '/secret.txt');
	}

	private function secret(): File
	{
		return new File($this->relPath . '/secret.txt');
	}

	// ---- changing files takes the write permission

	public function testReaderCannotOverwriteAFile()
	{
		$this->actAs($this->reader);

		try {
			$this->secret()->put($this->stream('OVERWRITTEN BY THE READER'));
			$this->fail('A user with only the read permission could overwrite the file');
		} catch (Forbidden $e) {
			$this->assertStringContainsString('write permission', $e->getMessage());
		}

		$this->assertSame(self::ORIGINAL, $this->contentOnDisk());
	}

	public function testFileModelChecksThePermissionBeforeWriting()
	{
		$this->actAs($this->reader);

		$file = \GO\Files\Model\File::model()->findByPath($this->relPath . '/secret.txt');
		$this->assertNotFalse($file);

		try {
			$file->putContents('OVERWRITTEN BY THE READER');
			$this->fail('Expected the permission check to refuse this');
		} catch (AccessDenied $e) {
			$this->assertTrue(true);
		}

		// it used to be written to disk first and checked afterwards
		$this->assertSame(self::ORIGINAL, $this->contentOnDisk());
	}

	public function testReaderCannotRenameAFile()
	{
		$this->actAs($this->reader);

		try {
			$this->secret()->setName('renamed.txt');
			$this->fail('A user with only the read permission could rename the file');
		} catch (Forbidden $e) {
			$this->assertTrue(true);
		}

		$this->assertFileExists(\GO::config()->file_storage_path . $this->relPath . '/secret.txt');
		$this->assertFileDoesNotExist(\GO::config()->file_storage_path . $this->relPath . '/renamed.txt');
	}

	public function testWriterCanOverwriteAFile()
	{
		$this->actAs($this->writer);

		$this->secret()->put($this->stream('WRITTEN BY THE WRITER'));

		$this->assertSame('WRITTEN BY THE WRITER', $this->contentOnDisk());
	}

	public function testDeletingTakesTheDeletePermission()
	{
		$this->actAs($this->writer);

		try {
			$this->secret()->delete();
			$this->fail('The write permission is not enough to delete');
		} catch (Forbidden $e) {
			$this->assertStringContainsString('delete permission', $e->getMessage());
		}

		$this->assertSame(self::ORIGINAL, $this->contentOnDisk());
	}

	public function testUserWithoutAccessCannotChangeAFile()
	{
		$this->actAs($this->nobody);

		$this->expectException(Forbidden::class);
		$this->secret()->put($this->stream('NOBODY WROTE THIS'));
	}

	// ---- locking is a way of changing a file

	public function testReaderCannotLockAFile()
	{
		$this->actAs($this->reader);

		$this->expectException(Forbidden::class);
		$this->secret()->lock('reader-lock');
	}

	public function testUserWithoutAccessCannotLockAFile()
	{
		$this->actAs($this->nobody);

		try {
			$this->secret()->lock('nobody-lock');
			$this->fail('A user without access could lock the file');
		} catch (Forbidden $e) {
			$this->assertTrue(true);
		}

		$this->actAs($this->writer);
		// it would throw FileLocked if the lock of the user without access was saved
		$this->secret()->lock('writer-lock');
		$this->secret()->unlock();
	}

	public function testReaderCannotUnlockAFile()
	{
		$this->actAs($this->writer);
		$this->secret()->lock('writer-lock');

		$this->actAs($this->reader);
		try {
			$this->secret()->unlock();
			$this->fail('A user with only the read permission could unlock the file');
		} catch (Forbidden $e) {
			$this->assertTrue(true);
		}

		$this->actAs($this->writer);
		$this->secret()->unlock();
	}

	// ---- reading takes the read permission

	public function testReaderCanReadAndList()
	{
		$this->actAs($this->reader);

		$directory = new Directory($this->relPath);

		$this->assertSame(self::ORIGINAL, stream_get_contents($this->secret()->get()));
		$this->assertInstanceOf(File::class, $directory->getChild('secret.txt'));
		$this->assertTrue($directory->childExists('secret.txt'));

		$names = array_map(fn($node) => $node->getName(), $directory->getChildren());
		$this->assertContains('secret.txt', $names);
	}

	public function testUserWithoutAccessCannotReadAFile()
	{
		$this->actAs($this->nobody);

		$this->expectException(Forbidden::class);
		$this->secret()->get();
	}

	public function testUserWithoutAccessCannotListOrOpenAFolder()
	{
		$this->actAs($this->nobody);
		$directory = new Directory($this->relPath);

		try {
			$directory->getChildren();
			$this->fail('A user without access could list the folder');
		} catch (Forbidden $e) {
			$this->assertTrue(true);
		}

		try {
			$directory->getChild('secret.txt');
			$this->fail('A user without access could open a file by name');
		} catch (Forbidden $e) {
			$this->assertTrue(true);
		}

		$this->assertFalse($directory->childExists('secret.txt'));
	}

	public function testFolderWithPermissionsOfItsOwnIsNotReachableByName()
	{
		$this->actAs($this->reader);
		$directory = new Directory($this->relPath);

		// the listing hides it
		$names = array_map(fn($node) => $node->getName(), $directory->getChildren());
		$this->assertNotContains('restricted', $names);

		// and it can't be opened by name either
		try {
			$directory->getChild('restricted');
			$this->fail('A folder the user may not read was handed out by name');
		} catch (NotFound $e) {
			$this->assertTrue(true);
		}
		$this->assertFalse($directory->childExists('restricted'));
	}

	public function testFilesInAFolderWithPermissionsOfItsOwnCannotBeRead()
	{
		$this->actAs($this->reader);

		$inner = new File($this->restrictedRelPath . '/inner.txt');
		try {
			$inner->get();
			$this->fail('A file in a folder the user may not read was returned');
		} catch (Forbidden $e) {
			$this->assertTrue(true);
		}

		$this->expectException(Forbidden::class);
		(new Directory($this->restrictedRelPath))->getChildren();
	}

	public function testOwnerCanDoEverything()
	{
		$this->actAs(User::findById(User::ID_SUPER_ADMIN));

		$directory = new Directory($this->relPath);
		$names = array_map(fn($node) => $node->getName(), $directory->getChildren());
		$this->assertContains('secret.txt', $names);
		$this->assertContains('restricted', $names);

		$this->assertInstanceOf(Directory::class, $directory->getChild('restricted'));
		$this->assertSame('INNER CONTENT', stream_get_contents((new File($this->restrictedRelPath . '/inner.txt'))->get()));

		$this->secret()->put($this->stream('WRITTEN BY THE OWNER'));
		$this->assertSame('WRITTEN BY THE OWNER', $this->contentOnDisk());
	}
}
