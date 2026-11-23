<?php
namespace go\modules\business\onlyoffice\controller;

use GO\Base\Exception\AccessDenied;
use GO\Base\Model\Acl;
use GO\Files\Model\Folder;
use go\core\auth\TemporaryState;
use go\core\model\User;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * "Save as" of the OnlyOffice editor writes a file in a folder, so it needs create permission on it. The callback of
 * the document server must find its JWT in the configured header.
 *
 * The controller requires a license, which the tests don't have, so it's created without its constructor.
 */
class CallbackControllerTest extends TestCase
{
	private $authState;
	private bool $ignoreAcl;
	private bool $disableModelCache;
	private User $owner;
	private User $reader;
	private Folder $ownerFolder;

	protected function setUp(): void
	{
		$this->authState = go()->getAuthState();
		$this->ignoreAcl = \GO::$ignoreAclPermissions;
		\GO::$ignoreAclPermissions = false;
		// cached models keep the permission level of the user that loaded them
		$this->disableModelCache = \GO::$disableModelCache;
		\GO::$disableModelCache = true;

		$this->owner = $this->createUser('oowner');
		$this->reader = $this->createUser('oreader');

		$this->actAs($this->owner->id);
		$this->ownerFolder = Folder::model()->findHomeFolder(\GO\Base\Model\User::model()->findByPk($this->owner->id));

		\GO::$ignoreAclPermissions = true;
		Acl::model()->findByPk($this->ownerFolder->acl_id)->addGroup($this->reader->getPersonalGroup()->id, Acl::READ_PERMISSION);
		\GO::$ignoreAclPermissions = false;
	}

	protected function tearDown(): void
	{
		$this->actAs(User::ID_SUPER_ADMIN);
		\GO::$ignoreAclPermissions = true;
		$this->ownerFolder->delete(true);
		$readerHome = Folder::model()->findByPath('users/' . $this->reader->username);
		if ($readerHome) {
			$readerHome->delete(true);
		}
		User::delete(['id' => [$this->owner->id, $this->reader->id]]);

		\GO::$ignoreAclPermissions = $this->ignoreAcl;
		\GO::$disableModelCache = $this->disableModelCache;
		go()->setAuthState($this->authState);
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

	private function controller(): CallbackController
	{
		return (new \ReflectionClass(CallbackController::class))->newInstanceWithoutConstructor();
	}

	public function testSaveFileAsNeedsCreatePermissionOnTheFolder(): void
	{
		$this->actAs($this->reader->id);
		try {
			$this->controller()->saveFileAs([
				'folder_id' => $this->ownerFolder->id,
				'filename' => 'test.docx',
				'url' => 'https://docs.example.com/cache/files/test.docx'
			]);
			$this->fail('A file was saved in a folder the user may only read');
		} catch (AccessDenied $e) {
			$this->assertFileDoesNotExist($this->ownerFolder->fsFolder->path() . '/test.docx');
		}
	}

	public static function jwts(): array
	{
		return [
			'body' => [['token' => 'body.jwt'], ['authorization' => 'Bearer header.jwt'], 'Authorization', 'body.jwt'],
			'default header' => [[], ['authorization' => 'Bearer header.jwt'], 'Authorization', 'header.jwt'],
			'configured header' => [[], ['authorizationjwt' => 'Bearer custom.jwt', 'authorization' => 'Basic dXNlcjpwYXNz'], 'AuthorizationJwt', 'custom.jwt'],
			'only other header' => [[], ['authorization' => 'Bearer header.jwt'], 'AuthorizationJwt', null],
			'without bearer' => [[], ['authorization' => 'header.jwt'], 'Authorization', 'header.jwt'],
			'none' => [[], [], 'Authorization', null],
			'empty' => [['token' => ''], ['authorization' => 'Bearer '], 'Authorization', null],
		];
	}

	#[DataProvider('jwts')]
	public function testFindJwt(array $data, array $headers, string $headerName, ?string $expected): void
	{
		$this->assertSame($expected, CallbackController::findJwt($data, $headers, $headerName));
	}
}
