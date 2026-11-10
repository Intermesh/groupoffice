<?php

namespace go\modules\legacy\files;

use go\core\auth\TemporaryState;
use go\core\model\User;
use GO\Base\Exception\AccessDenied;
use GO\Base\Model\Acl;
use GO\Files\Controller\TemplateController;
use GO\Files\Model\Folder;
use GO\Files\Model\Template;
use PHPUnit\Framework\TestCase;

/**
 * files/template/createFile writes a template into a folder. It must require CREATE permission on the folder and must
 * never overwrite an existing file.
 */
class TemplateCreateFilePermissionTest extends TestCase
{
	private $authState;
	private bool $ignoreAcl;
	private User $owner;
	private User $reader;
	private Folder $folder;
	private Template $template;

	protected function setUp(): void
	{
		$this->authState = go()->getAuthState();
		$this->ignoreAcl = \GO::$ignoreAclPermissions;

		$this->owner = $this->createUser('tplowner');
		$this->reader = $this->createUser('tplreader');

		$this->actAs($this->owner->id);
		$this->folder = Folder::model()->findHomeFolder(\GO\Base\Model\User::model()->findByPk($this->owner->id));
		$this->assertTrue(
			Acl::model()->findByPk($this->folder->acl_id)->addGroup($this->reader->getPersonalGroup()->id, Acl::READ_PERMISSION)
		);

		$this->template = new Template();
		$this->template->name = uniqid('tpl');
		$this->template->extension = 'txt';
		$this->template->content = 'template content';
		$this->assertTrue($this->template->save(true), var_export($this->template->getValidationErrors(), true));
		$this->assertTrue(
			Acl::model()->findByPk($this->template->acl_id)->addGroup($this->reader->getPersonalGroup()->id, Acl::READ_PERMISSION)
		);
	}

	protected function tearDown(): void
	{
		go()->setAuthState($this->authState);
		\GO::session()->setCurrentUser(User::ID_SUPER_ADMIN);
		\GO::$ignoreAclPermissions = true;

		$this->template->delete(true);
		$this->folder->delete(true);
		User::delete(['id' => [$this->owner->id, $this->reader->id]]);

		\GO::$ignoreAclPermissions = $this->ignoreAcl;
	}

	private function createUser(string $prefix): User
	{
		$name = uniqid($prefix);
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

	private function actAs(int $userId): void
	{
		go()->setAuthState(new TemporaryState($userId));
		\GO::session()->setCurrentUser($userId);
	}

	private function createFile(string $filename): array
	{
		$controller = new TemplateController();
		$method = new \ReflectionMethod($controller, 'actionCreateFile');
		$method->setAccessible(true);

		return $method->invoke($controller, [
			'template_id' => $this->template->id,
			'folder_id' => $this->folder->id,
			'filename' => $filename
		]);
	}

	private function path(string $filename): string
	{
		return $this->folder->fsFolder->path() . '/' . $filename . '.txt';
	}

	public function testReadOnlyUserCannotCreateFile(): void
	{
		$this->actAs($this->reader->id);

		try {
			$this->createFile('new');
			$this->fail('createFile succeeded with READ permission on the folder');
		} catch (AccessDenied $e) {
			$this->assertFileDoesNotExist($this->path('new'));
		}
	}

	public function testReadOnlyUserCannotOverwriteFile(): void
	{
		file_put_contents($this->path('existing'), 'original');
		$this->folder->addFile('existing.txt', true);

		$this->actAs($this->reader->id);

		try {
			$this->createFile('existing');
			$this->fail('createFile succeeded with READ permission on the folder');
		} catch (AccessDenied $e) {
			$this->assertStringEqualsFile($this->path('existing'), 'original');
		}
	}

	public function testExistingFileIsNotOverwritten(): void
	{
		file_put_contents($this->path('existing'), 'original');
		$this->folder->addFile('existing.txt', true);

		try {
			$this->createFile('existing');
			$this->fail('createFile overwrote an existing file');
		} catch (\Exception $e) {
			$this->assertStringEqualsFile($this->path('existing'), 'original');
		}
	}

	public function testOwnerCanCreateFile(): void
	{
		$response = $this->createFile('new');

		$this->assertTrue($response['success']);
		$this->assertStringEqualsFile($this->path('new'), 'template content');
	}
}
