<?php

namespace go\modules\legacy\files;

use go\core\auth\TemporaryState;
use go\core\model\User;
use GO\Base\Exception\AccessDenied;
use GO\Files\Controller\FileController;
use GO\Files\Controller\FolderController;
use GO\Files\Controller\TemplateController;
use GO\Files\Model\File;
use GO\Files\Model\Folder;
use GO\Files\Model\Template;
use PHPUnit\Framework\TestCase;

/**
 * Symbolic links can point anywhere on the server. They must not get into the file storage by decompressing an
 * archive, must not be added by a filesystem sync, and file operations must not read, write or delete through them.
 */
class SymlinkEscapeTest extends TestCase
{
	private $authState;
	private bool $ignoreAcl;
	private string $cwd;
	private User $user;
	private Folder $homeFolder;
	/** A folder outside the file storage */
	private string $outside;
	private string $work;

	protected function setUp(): void
	{
		$this->authState = go()->getAuthState();
		$this->ignoreAcl = \GO::$ignoreAclPermissions;
		$this->cwd = getcwd();

		$name = uniqid('symlink');
		$this->user = new User();
		$this->user->setValues([
			'username' => $name,
			'displayName' => $name,
			'email' => $name . '@example.com',
			'password' => 'Pa55word!!xyz'
		]);
		$this->assertTrue($this->user->save(), var_export($this->user->getValidationErrors(), true));

		$this->actAs($this->user->id);
		$this->homeFolder = Folder::model()->findHomeFolder(\GO\Base\Model\User::model()->findByPk($this->user->id));

		$this->outside = sys_get_temp_dir() . '/go-outside-' . uniqid();
		$this->assertStringStartsNotWith(rtrim(\GO::config()->file_storage_path, '/') . '/', $this->outside);
		mkdir($this->outside . '/sub', 0777, true);
		file_put_contents($this->outside . '/secret.txt', 'outside secret');
		file_put_contents($this->outside . '/sub/secret.txt', 'outside secret');

		$this->work = sys_get_temp_dir() . '/go-archive-' . uniqid();
		mkdir($this->work);
	}

	protected function tearDown(): void
	{
		chdir($this->cwd);
		go()->setAuthState($this->authState);
		\GO::session()->setCurrentUser(User::ID_SUPER_ADMIN);
		\GO::$ignoreAclPermissions = true;

		$this->removeLinks($this->homeFolder->fsFolder->path());
		File::$deleteInDatabaseOnly = false;
		Folder::$deleteInDatabaseOnly = false;
		$this->homeFolder->delete(true);
		User::delete(['id' => [$this->user->id]]);

		@chmod($this->outside, 0777);
		$this->rmTree($this->outside);
		$this->rmTree($this->work);

		\GO::$ignoreAclPermissions = $this->ignoreAcl;
	}

	private function removeLinks(string $dir): void
	{
		foreach (scandir($dir) ?: [] as $name) {
			if ($name === '.' || $name === '..') {
				continue;
			}
			$path = $dir . '/' . $name;
			if (is_link($path)) {
				unlink($path);
			} else if (is_dir($path)) {
				$this->removeLinks($path);
			}
		}
	}

	private function rmTree(string $dir): void
	{
		if (!file_exists($dir) && !is_link($dir)) {
			return;
		}
		if (is_link($dir) || !is_dir($dir)) {
			unlink($dir);
			return;
		}
		foreach (scandir($dir) as $name) {
			if ($name !== '.' && $name !== '..') {
				$this->rmTree($dir . '/' . $name);
			}
		}
		rmdir($dir);
	}

	private function actAs(int $userId): void
	{
		go()->setAuthState(new TemporaryState($userId));
		\GO::session()->setCurrentUser($userId);
	}

	private function invoke(object $controller, string $action, array $params)
	{
		$method = new \ReflectionMethod($controller, $action);
		$method->setAccessible(true);
		return $method->invoke($controller, $params);
	}

	/**
	 * Build an archive from the files in $this->work and add it to the home folder
	 */
	private function addArchive(string $name, string $cmd): File
	{
		exec('cd ' . escapeshellarg($this->work) . ' && ' . $cmd . ' 2>&1', $output, $ret);
		$this->assertSame(0, $ret, implode("\n", $output));
		rename($this->work . '/' . $name, $this->homeFolder->fsFolder->path() . '/' . $name);
		return $this->homeFolder->addFile($name, true);
	}

	private function decompress(File $archive): array
	{
		return $this->invoke(new FolderController(), 'actionDecompress', [
			'working_folder_id' => $this->homeFolder->id,
			'decompress_sources' => json_encode([$archive->path])
		]);
	}

	private function assertOutsideUntouched(): void
	{
		$this->assertStringEqualsFile($this->outside . '/secret.txt', 'outside secret');
		$this->assertStringEqualsFile($this->outside . '/sub/secret.txt', 'outside secret');
	}

	private function assertNoLinksIn(string $dir): void
	{
		foreach (scandir($dir) as $name) {
			if ($name === '.' || $name === '..') {
				continue;
			}
			$path = $dir . '/' . $name;
			$this->assertFalse(is_link($path), "$path is a link");
			if (is_dir($path)) {
				$this->assertNoLinksIn($path);
			}
		}
	}

	public static function linkArchives(): array
	{
		return [
			'folder link' => ['ln -s OUTSIDE dirlink'],
			'file link' => ['ln -s OUTSIDE/secret.txt filelink'],
			// A hard link member can refer to a path through a link extracted earlier, so they're all refused
			'hard link' => ['ln normal.txt hardlink'],
		];
	}

	#[\PHPUnit\Framework\Attributes\DataProvider('linkArchives')]
	public function testTarWithLinksIsRefused(string $create): void
	{
		file_put_contents($this->work . '/normal.txt', 'normal');
		exec('cd ' . escapeshellarg($this->work) . ' && ' . str_replace('OUTSIDE', escapeshellarg($this->outside), $create));
		$archive = $this->addArchive('links.tar', 'tar cf links.tar *');

		try {
			$this->decompress($archive);
			$this->fail('An archive with links was decompressed');
		} catch (\Exception $e) {
			$this->assertStringContainsString('links', $e->getMessage());
		}

		$this->assertNoLinksIn($this->homeFolder->fsFolder->path());
		$this->assertFalse($this->homeFolder->hasFile('normal.txt'));
		$this->assertOutsideUntouched();
	}

	public function testZipWithLinkDoesNotCreateLink(): void
	{
		symlink($this->outside, $this->work . '/dirlink');
		file_put_contents($this->work . '/normal.txt', 'normal');
		// -y stores the link itself
		$archive = $this->addArchive('links.zip', 'zip -y -r links.zip dirlink normal.txt');

		$this->decompress($archive);

		$this->assertNoLinksIn($this->homeFolder->fsFolder->path());
		$this->assertTrue((bool) $this->homeFolder->hasFile('normal.txt'));
		$this->assertFalse((bool) $this->homeFolder->hasFolder('dirlink'));
		$this->assertOutsideUntouched();
	}

	public function testTarIsDecompressedAndMerged(): void
	{
		mkdir($this->work . '/dir');
		file_put_contents($this->work . '/dir/new.txt', 'new');
		file_put_contents($this->work . '/top.txt', 'top');
		$archive = $this->addArchive('normal.tgz', 'tar czf normal.tgz dir top.txt');

		// an existing folder with the same name is merged
		mkdir($this->homeFolder->fsFolder->path() . '/dir');
		file_put_contents($this->homeFolder->fsFolder->path() . '/dir/existing.txt', 'existing');

		$response = $this->decompress($archive);

		$this->assertTrue($response['success']);
		$dir = $this->homeFolder->fsFolder->path() . '/dir';
		$this->assertStringEqualsFile($dir . '/new.txt', 'new');
		$this->assertStringEqualsFile($dir . '/existing.txt', 'existing');
		$this->assertStringEqualsFile($this->homeFolder->fsFolder->path() . '/top.txt', 'top');
		$this->assertTrue((bool) Folder::model()->findByPath($this->homeFolder->path . '/dir')->hasFile('new.txt'));
	}

	public function testSyncSkipsLinks(): void
	{
		symlink($this->outside, $this->homeFolder->fsFolder->path() . '/dirlink');
		symlink($this->outside . '/secret.txt', $this->homeFolder->fsFolder->path() . '/filelink');

		$this->homeFolder->syncFilesystem(true);

		$this->assertFalse((bool) $this->homeFolder->hasFolder('dirlink'));
		$this->assertFalse((bool) $this->homeFolder->hasFile('filelink'));
	}

	/**
	 * A folder that was registered earlier and is now a link to a folder outside the file storage, like an older version
	 * did when decompressing an archive.
	 */
	private function registeredLinkedFolder(): array
	{
		$linkPath = $this->homeFolder->fsFolder->path() . '/linked';
		mkdir($linkPath);
		file_put_contents($linkPath . '/secret.txt', 'inside');
		$this->homeFolder->syncFilesystem(true);
		$folder = $this->homeFolder->hasFolder('linked');
		$file = $folder->hasFile('secret.txt');
		$this->assertNotEmpty($file);

		unlink($linkPath . '/secret.txt');
		rmdir($linkPath);
		symlink($this->outside, $linkPath);

		return [$folder, $file];
	}

	public function testDownloadThroughLinkedFolderIsRefused(): void
	{
		[, $file] = $this->registeredLinkedFolder();

		// The action catches the exception and sends a status header, which can't be done after the test output started
		set_error_handler(fn(int $no, string $str) => str_contains($str, 'headers already sent'), E_WARNING);
		$userAgent = $_SERVER['HTTP_USER_AGENT'] ?? null;
		$_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0 (X11; Linux x86_64; rv:140.0) Gecko/20100101 Firefox/140.0';
		ob_start();
		try {
			$this->invoke(new FileController(), 'actionDownload', ['id' => $file->id]);
		} finally {
			$output = ob_get_clean();
			restore_error_handler();
			$_SERVER['HTTP_USER_AGENT'] = $userAgent;
		}

		$this->assertStringNotContainsString('outside secret', $output);
		$this->assertSame((new AccessDenied())->getMessage(), $output);
	}

	public function testTemplateWriteThroughLinkedFolderIsRefused(): void
	{
		[$folder] = $this->registeredLinkedFolder();

		$template = new Template();
		$template->name = uniqid('tpl');
		$template->extension = 'txt';
		$template->content = 'template content';
		$this->assertTrue($template->save(true));

		try {
			$this->invoke(new TemplateController(), 'actionCreateFile', [
				'template_id' => $template->id,
				'folder_id' => $folder->id,
				'filename' => 'new'
			]);
			$this->fail('A template was written through a link');
		} catch (AccessDenied $e) {
			$this->assertFileDoesNotExist($this->outside . '/new.txt');
		} finally {
			$template->delete(true);
		}
	}

	public function testSaveAttachmentThroughLinkedFolderIsRefused(): void
	{
		if (!\GO::modules()->isInstalled('email')) {
			$this->markTestSkipped('The email module is required');
		}
		[$folder] = $this->registeredLinkedFolder();

		// The outside file is writable, but its folder isn't
		chmod($this->outside, 0555);
		file_put_contents(\GO::config()->tmpdir . 'attachment.txt', 'attacker content');

		try {
			$this->invoke(new \GO\Email\Controller\MessageController(), 'actionSaveAttachment', [
				'folder_id' => $folder->id,
				'filename' => 'secret.txt',
				'tmp_file' => 'attachment.txt'
			]);
			$this->fail('An attachment was saved through a link');
		} catch (AccessDenied $e) {
			$this->assertOutsideUntouched();
		} finally {
			unlink(\GO::config()->tmpdir . 'attachment.txt');
		}
	}

	public function testDeleteThroughLinkedFolderIsRefused(): void
	{
		[, $file] = $this->registeredLinkedFolder();

		try {
			$file->delete();
			$this->fail('A file was deleted through a link');
		} catch (AccessDenied $e) {
			$this->assertOutsideUntouched();
		}
	}

	public function testSyncRemovesRegisteredLinks(): void
	{
		[$folder, $file] = $this->registeredLinkedFolder();

		$this->homeFolder->syncFilesystem(true);

		$this->assertFalse((bool) Folder::model()->findByPk($folder->id, false, true));
		$this->assertFalse((bool) File::model()->findByPk($file->id, false, true));
		$this->assertOutsideUntouched();
	}
}
