<?php

namespace go\modules\community\wopi;

use go\core\auth\TemporaryState;
use go\core\exception\Forbidden;
use go\core\model\User;
use go\modules\community\wopi\controller\Wopi;
use go\modules\community\wopi\model\Service;
use go\modules\community\wopi\model\Token;
use GO\Base\Model\Acl;
use GO\Files\Model\File;
use GO\Files\Model\Folder;
use PHPUnit\Framework\TestCase;

/**
 * The WOPI controller must refuse files other than the one the token was issued for, and must refuse writes when the
 * service is read-only for the user.
 *
 * The constructor authenticates with the request, so the controller is created without it and the token and service
 * are injected.
 */
class WopiControllerTest extends TestCase
{
	private $authState;
	private User $user;
	private User $other;
	private Folder $folder;
	private Folder $otherFolder;
	private File $file1;
	private File $file2;
	private int $writeServiceId;
	private int $readServiceId;

	protected function setUp(): void
	{
		$this->authState = go()->getAuthState();

		$this->user = $this->createUser('wopiuser');
		$this->other = $this->createUser('wopiother');

		$this->actAs($this->other->id);
		$this->otherFolder = Folder::model()->findHomeFolder(\GO\Base\Model\User::model()->findByPk($this->other->id));
		$this->assertTrue(
			Acl::model()->findByPk($this->otherFolder->acl_id)->addGroup($this->user->getPersonalGroup()->id, Acl::READ_PERMISSION)
		);

		$this->actAs($this->user->id);
		$this->folder = Folder::model()->findHomeFolder(\GO\Base\Model\User::model()->findByPk($this->user->id));
		$this->file1 = $this->createFile('one.docx');
		$this->file2 = $this->createFile('two.docx');

		// Saving a Service runs WOPI discovery over HTTP, so insert the rows directly. The user owns the ACL of their
		// home folder, so that service is writable. The other user's folder ACL gives read only.
		$this->writeServiceId = $this->createService($this->folder->acl_id);
		$this->readServiceId = $this->createService($this->otherFolder->acl_id);
	}

	protected function tearDown(): void
	{
		go()->setAuthState($this->authState);
		\GO::session()->setCurrentUser(User::ID_SUPER_ADMIN);

		go()->getDbConnection()->delete('wopi_service', ['id' => [$this->writeServiceId, $this->readServiceId]])->execute();
		$this->folder->delete(true);
		$this->otherFolder->delete(true);
		User::delete(['id' => [$this->user->id, $this->other->id]]);
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

	private function createFile(string $name): File
	{
		file_put_contents($this->folder->fsFolder->path() . '/' . $name, 'test');
		$file = $this->folder->addFile($name, true);
		$this->assertInstanceOf(File::class, $file);
		return $file;
	}

	private function createService(int $aclId): int
	{
		go()->getDbConnection()->insert('wopi_service', [
			'name' => 'Test',
			'url' => 'https://wopi.example.com',
			'aclId' => $aclId,
			'type' => uniqid('test')
		])->execute();
		return (int) go()->getDbConnection()->getPDO()->lastInsertId();
	}

	private function controller(int $serviceId, File $file): Wopi
	{
		$ref = new \ReflectionClass(Wopi::class);
		$controller = $ref->newInstanceWithoutConstructor();
		$ref->getProperty('token')->setValue($controller, Token::get($serviceId, $this->user->id, $file->id));
		$ref->getProperty('service')->setValue($controller, Service::findById($serviceId));
		return $controller;
	}

	private function call(Wopi $controller, string $method, ...$args)
	{
		return (new \ReflectionMethod($controller, $method))->invoke($controller, ...$args);
	}

	public function testOtherFileIsRefused(): void
	{
		$controller = $this->controller($this->writeServiceId, $this->file1);

		$this->assertEquals($this->file1->id, $this->call($controller, 'findFile', $this->file1->id)->id);

		$this->expectException(Forbidden::class);
		$this->call($controller, 'findFile', $this->file2->id);
	}

	public function testWritableService(): void
	{
		$controller = $this->controller($this->writeServiceId, $this->file1);

		$this->assertTrue($this->call($controller, 'canWrite', $this->file1));
		$this->call($controller, 'requireWrite', $this->file1);
		$this->assertTrue($this->call($controller, 'checkFileInfo', $this->file1->id)['UserCanWrite']);
	}

	public function testReadOnlyServiceRefusesWrites(): void
	{
		$controller = $this->controller($this->readServiceId, $this->file1);

		// The user may write the file, but not through this service
		$this->assertTrue($this->file1->checkPermissionLevel(Acl::WRITE_PERMISSION));
		$this->assertFalse($this->call($controller, 'canWrite', $this->file1));
		$this->assertFalse($this->call($controller, 'checkFileInfo', $this->file1->id)['UserCanWrite']);

		foreach(['putFile', 'renameFile', 'delete', 'putRelative'] as $method) {
			try {
				$this->call($controller, $method, $this->file1->id);
				$this->fail($method . ' succeeded through a read-only service');
			} catch (Forbidden $e) {
				$this->assertTrue(true);
			}
		}

		$this->assertNotNull(File::model()->findByPk($this->file1->id), 'File must not be deleted');
	}
}
