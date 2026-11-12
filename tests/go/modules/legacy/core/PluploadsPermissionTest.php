<?php

namespace go\modules\legacy\core;

use go\core\auth\TemporaryState;
use go\core\model\User;
use GO\Base\Exception\AccessDenied;
use GO\Core\Controller\CoreController;
use GO\Files\Model\Folder;
use PHPUnit\Framework\TestCase;

/**
 * core/pluploads with "addFileStorageFiles" returns metadata of files in the file storage. It must only do that for
 * files the user may read, and must not reveal which files exist.
 */
class PluploadsPermissionTest extends TestCase
{
	private $authState;
	private bool $ignoreAcl;
	private User $owner;
	private User $other;
	private Folder $ownerFolder;
	private string $filePath;

	protected function setUp(): void
	{
		if(!\GO::modules()->isInstalled('files')) {
			$this->markTestSkipped('The files module is required');
		}

		$this->authState = go()->getAuthState();
		$this->ignoreAcl = \GO::$ignoreAclPermissions;

		$this->owner = $this->createUser('plowner');
		$this->other = $this->createUser('plother');

		$this->actAs($this->owner->id);
		$this->ownerFolder = Folder::model()->findHomeFolder(\GO\Base\Model\User::model()->findByPk($this->owner->id));
		file_put_contents($this->ownerFolder->fsFolder->path() . '/private.eml', 'private');
		$this->filePath = $this->ownerFolder->addFile('private.eml', true)->path;

		unset(\GO::session()->values['files']['uploadqueue']);
	}

	protected function tearDown(): void
	{
		unset(\GO::session()->values['files']['uploadqueue']);

		go()->setAuthState($this->authState);
		\GO::session()->setCurrentUser(User::ID_SUPER_ADMIN);
		\GO::$ignoreAclPermissions = true;

		$this->ownerFolder->delete(true);
		User::delete(['id' => [$this->owner->id, $this->other->id]]);

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

	private function pluploads(array $paths): array
	{
		$controller = new CoreController();
		$method = new \ReflectionMethod($controller, 'actionPluploads');
		$method->setAccessible(true);
		return $method->invoke($controller, ['addFileStorageFiles' => json_encode($paths)]);
	}

	public function testOtherUserGetsNoMetadata(): void
	{
		$this->actAs($this->other->id);

		try {
			$this->pluploads([$this->filePath]);
			$this->fail('pluploads returned metadata of a file the user may not read');
		} catch (AccessDenied $e) {
			$this->assertEmpty(\GO::session()->values['files']['uploadqueue'] ?? []);
		}
	}

	public function testMissingFileGivesSameErrorAsUnreadableFile(): void
	{
		$this->actAs($this->other->id);

		$this->expectException(AccessDenied::class);
		$this->pluploads([$this->ownerFolder->path . '/does-not-exist.eml']);
	}

	public function testOwnerGetsMetadata(): void
	{
		$this->actAs($this->owner->id);

		$response = $this->pluploads([$this->filePath]);

		$this->assertSame(1, $response['total']);
		$this->assertSame('private.eml', $response['results'][0]['name']);
		$this->assertSame($this->filePath, $response['results'][0]['tmp_file']);
		$this->assertTrue($response['results'][0]['from_file_storage']);
	}
}
