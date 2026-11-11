<?php

namespace go\modules\legacy\files;

use go\core\auth\TemporaryState;
use go\core\model\User;
use GO\Base\Exception\AccessDenied;
use GO\Base\Model\Acl;
use GO\Files\Controller\FileController;
use GO\Files\Model\Folder;
use PHPUnit\Framework\TestCase;

/**
 * files/file/convert adds the converted file to the folder of the file, so it needs CREATE permission on that folder.
 * The format is used as file extension, so it may only contain letters and digits.
 */
class ConvertPermissionTest extends TestCase
{
	private $authState;
	private bool $ignoreAcl;
	private User $owner;
	private User $reader;
	private Folder $folder;
	private \GO\Files\Model\File $file;

	protected function setUp(): void
	{
		$this->authState = go()->getAuthState();
		$this->ignoreAcl = \GO::$ignoreAclPermissions;

		$this->owner = $this->createUser('convowner');
		$this->reader = $this->createUser('convreader');

		$this->actAs($this->owner->id);
		$this->folder = Folder::model()->findHomeFolder(\GO\Base\Model\User::model()->findByPk($this->owner->id));
		$this->assertTrue(
			Acl::model()->findByPk($this->folder->acl_id)->addGroup($this->reader->getPersonalGroup()->id, Acl::READ_PERMISSION)
		);

		file_put_contents($this->folder->fsFolder->path() . '/document.txt', 'content');
		$this->file = $this->folder->addFile('document.txt', true);
	}

	protected function tearDown(): void
	{
		go()->setAuthState($this->authState);
		\GO::session()->setCurrentUser(User::ID_SUPER_ADMIN);
		\GO::$ignoreAclPermissions = true;

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

	public function testReadOnlyUserCannotConvertIntoFolder(): void
	{
		$this->actAs($this->reader->id);

		$this->expectException(AccessDenied::class);
		(new FileController())->actionConvert(['id' => $this->file->id, 'format' => 'pdf']);
	}

	public function testOwnerPassesPermissionCheck(): void
	{
		if (go()->getModule('business', 'fileconverter')) {
			$this->markTestSkipped('The converter module would convert the file');
		}

		// Without the converter module the conversion itself fails after the permission check
		$this->expectException(\go\core\exception\NotFound::class);
		$this->expectExceptionMessage('Converter module is not available');
		(new FileController())->actionConvert(['id' => $this->file->id, 'format' => 'pdf']);
	}

	public static function formats(): array
	{
		return [
			'path' => ['../../x.php', 'xphp'],
			'empty after cleaning' => ['../', 'pdf'],
			'missing' => [null, 'pdf'],
			'normal' => ['docx', 'docx'],
		];
	}

	#[\PHPUnit\Framework\Attributes\DataProvider('formats')]
	public function testFormatOnlyHasLettersAndDigits(?string $format, string $expected): void
	{
		$method = new \ReflectionMethod(FileController::class, 'convertFormat');
		$this->assertSame($expected, $method->invoke(null, ['format' => $format]));
	}
}
