<?php
namespace go\modules\legacy\core;

use go\core\auth\TemporaryState;
use go\core\model\User;
use GO\Base\Exception\AccessDenied;
use GO\Base\Model\Acl;
use GO\Core\Controller\CoreController;
use GO\Files\Model\Folder;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

/**
 * A thumbnail shows the content of a file, so it takes the permission to read that file.
 */
class ThumbPermissionTest extends TestCase
{
	private $authState;
	private bool $ignoreAcl;
	private bool $disableModelCache;

	private User $reader;
	private User $nobody;

	private Folder $folder;
	private ?\GO\Base\Model\Acl $acl = null;
	private string $trackedPath;
	private string $untrackedPath;

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
		$this->nobody = $this->newUser();

		$this->actAs(User::findById(User::ID_SUPER_ADMIN));
		\GO::$ignoreAclPermissions = true;

		$home = Folder::model()->findHomeFolder(\GO::user());
		$this->folder = $home->addFolder('thumb-permissions-' . uniqid());

		$this->acl = new Acl();
		$this->acl->usedIn = 'fs_folders.acl_id';
		$this->acl->ownedBy = User::ID_SUPER_ADMIN;
		$this->acl->save();
		$this->acl->addGroup($this->reader->getPersonalGroup()->id, Acl::READ_PERMISSION);
		$this->folder->acl_id = $this->acl->id;
		$this->folder->save();

		// a file in the files module
		(new \GO\Base\Fs\File($this->folder->fsFolder->path() . '/photo.jpg'))->putContents('not really an image');
		$this->folder->addFile('photo.jpg');
		$this->trackedPath = $this->folder->path . '/photo.jpg';

		// a file in the same place on disk that the files module doesn't know about
		(new \GO\Base\Fs\File($this->folder->fsFolder->path() . '/untracked.jpg'))->putContents('not really an image');
		$this->untrackedPath = $this->folder->path . '/untracked.jpg';

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
		$this->acl?->delete();

		\GO::$ignoreAclPermissions = $this->ignoreAcl;
		\GO::$disableModelCache = $this->disableModelCache;

		foreach ([$this->reader, $this->nobody] as $user) {
			User::delete(['id' => $user->id]);
		}
	}

	private function newUser(): User
	{
		$name = uniqid('thumb');
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

	private function actAs(User $user): void
	{
		go()->setAuthState(new TemporaryState($user->id));
		\GO::session()->setCurrentUser($user->id);
		Folder::model()->clearFolderCache();
	}

	/**
	 * @throws AccessDenied
	 */
	private function check(string $path): void
	{
		$method = new ReflectionMethod(CoreController::class, 'checkThumbFilePermission');
		$method->invoke(new CoreController(), $path);
	}

	/**
	 * The action itself, for the cases where it refuses. It does that before it sends any output.
	 *
	 * @throws AccessDenied
	 */
	private function thumbAction(string $path): void
	{
		$method = new ReflectionMethod(CoreController::class, 'actionThumb');
		$method->invoke(new CoreController(), ['src' => $path, 'lw' => 100, 'ph' => 100]);
	}

	public function testThumbActionRefusesAUserWithoutAccess()
	{
		$this->actAs($this->nobody);

		ob_start();
		try {
			$this->thumbAction($this->trackedPath);
			$this->fail('The thumbnail of a file the user may not read was served');
		} catch (AccessDenied $e) {
			$this->assertTrue(true);
		} finally {
			$output = ob_get_clean();
		}

		$this->assertSame('', $output, 'nothing may be sent');
	}

	public function testThumbActionRefusesAFileThatIsNotInTheFilesModule()
	{
		$this->actAs($this->reader);

		ob_start();
		try {
			$this->thumbAction($this->untrackedPath);
			$this->fail('The thumbnail of a file without permissions was served');
		} catch (AccessDenied $e) {
			$this->assertTrue(true);
		} finally {
			ob_end_clean();
		}
	}

	public function testUserWhoMayReadTheFileGetsAThumbnail()
	{
		$this->actAs($this->reader);

		$this->check($this->trackedPath);
		$this->assertTrue(true);
	}

	public function testOwnerGetsAThumbnail()
	{
		$this->actAs(User::findById(User::ID_SUPER_ADMIN));

		$this->check($this->trackedPath);
		$this->assertTrue(true);
	}

	public function testUserWithoutAccessDoesNot()
	{
		$this->actAs($this->nobody);

		$this->expectException(AccessDenied::class);
		$this->check($this->trackedPath);
	}

	public function testFileThatIsNotInTheFilesModuleIsRefused()
	{
		// there are no permissions to check for it
		$this->actAs($this->reader);

		$this->expectException(AccessDenied::class);
		$this->check($this->untrackedPath);
	}

	public function testMissingFileIsRefusedInTheSameWayAsAForbiddenOne()
	{
		$this->actAs($this->nobody);

		$messages = [];
		foreach ([$this->trackedPath, $this->folder->path . '/does-not-exist.jpg'] as $path) {
			try {
				$this->check($path);
				$this->fail('Expected the thumbnail to be refused');
			} catch (AccessDenied $e) {
				$messages[] = get_class($e) . ': ' . $e->getMessage();
			}
		}

		// otherwise it tells which paths exist
		$this->assertSame($messages[0], $messages[1]);
	}
}
