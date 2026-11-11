<?php

namespace go\modules\legacy\files;

use go\core\auth\TemporaryState;
use go\core\model\User;
use GO\Base\Exception\AccessDenied;
use GO\Base\Exception\NotFound;
use GO\Files\Controller\FileController;
use GO\Files\Controller\FolderController;
use GO\Files\Model\File;
use GO\Files\Model\Folder;
use PHPUnit\Framework\TestCase;

/**
 * files/folder/images, files/file/handlers and files/folder/checkModelFolder must check that the user may read the
 * file or item.
 */
class FileEndpointPermissionTest extends TestCase
{
	private $authState;
	private bool $ignoreAcl;
	private bool $disableModelCache;
	private User $owner;
	private User $other;
	private Folder $ownerFolder;
	private File $image;

	protected function setUp(): void
	{
		$this->authState = go()->getAuthState();
		$this->ignoreAcl = \GO::$ignoreAclPermissions;
		\GO::$ignoreAclPermissions = false;
		// cached models keep the permission level of the user that loaded them
		$this->disableModelCache = \GO::$disableModelCache;
		\GO::$disableModelCache = true;

		$this->owner = $this->createUser('endpointowner');
		$this->other = $this->createUser('endpointother');

		$this->actAs($this->owner->id);
		$this->ownerFolder = Folder::model()->findHomeFolder(\GO\Base\Model\User::model()->findByPk($this->owner->id));
		// a 1x1 transparent gif
		file_put_contents($this->ownerFolder->fsFolder->path() . '/private.gif', base64_decode('R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7'));
		$this->image = $this->ownerFolder->addFile('private.gif');
	}

	protected function tearDown(): void
	{
		go()->setAuthState($this->authState);
		\GO::session()->setCurrentUser(User::ID_SUPER_ADMIN);
		\GO::$ignoreAclPermissions = true;

		$this->ownerFolder->delete(true);
		User::delete(['id' => [$this->owner->id, $this->other->id]]);

		\GO::$ignoreAclPermissions = $this->ignoreAcl;
		\GO::$disableModelCache = $this->disableModelCache;
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

	private function invoke(object $controller, string $method, array $params): mixed
	{
		$method = new \ReflectionMethod($controller, $method);
		$method->setAccessible(true);

		return $method->invoke($controller, $params);
	}

	private function images(array $params): array
	{
		return $this->invoke(new FolderController(), 'actionImages', $params + ['thumbParams' => '{}']);
	}

	public function testOwnerCanListImages(): void
	{
		$this->actAs($this->owner->id);

		$response = $this->images(['path' => $this->image->path]);

		$this->assertEquals('private.gif', $response['images'][0]['name']);
	}

	public static function imageParams(): array
	{
		return [
			'by path' => ['path'],
			'by id' => ['id'],
		];
	}

	#[\PHPUnit\Framework\Attributes\DataProvider('imageParams')]
	public function testOtherUserCannotListImages(string $param): void
	{
		$this->actAs($this->other->id);

		$this->expectException(AccessDenied::class);
		$this->images([$param => $param == 'id' ? $this->image->id : $this->image->path]);
	}

	#[\PHPUnit\Framework\Attributes\DataProvider('imageParams')]
	public function testOtherUserCannotGetHandlers(string $param): void
	{
		$this->actAs($this->other->id);

		$this->expectException(AccessDenied::class);
		$this->invoke(new FileController(), 'actionHandlers', [$param => $param == 'id' ? $this->image->id : $this->image->path]);
	}

	public function testOwnerCanGetHandlers(): void
	{
		$this->actAs($this->owner->id);

		$response = $this->invoke(new FileController(), 'actionHandlers', ['id' => $this->image->id]);

		$this->assertNotEmpty($response['results']);
	}

	public static function modelsWithoutFiles(): array
	{
		return [
			'folder' => ['Folder'],
			'file' => ['File'],
		];
	}

	#[\PHPUnit\Framework\Attributes\DataProvider('modelsWithoutFiles')]
	public function testCheckModelFolderOnlyAcceptsModelsWithFiles(string $model): void
	{
		$this->actAs($this->other->id);

		$this->expectException(NotFound::class);
		$this->invoke(new FolderController(), 'actionCheckModelFolder', [
			'model' => $model,
			'id' => $model == 'Folder' ? $this->ownerFolder->id : $this->image->id
		]);
	}
}
