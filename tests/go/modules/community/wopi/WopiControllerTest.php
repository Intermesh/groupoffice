<?php

namespace go\modules\community\wopi;

use go\core\auth\TemporaryState;
use go\core\exception\Forbidden;
use go\core\http\Exception;
use go\core\http\Request;
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
		$this->setRequestHeaders(null);
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

	/**
	 * Set the request headers the controller reads, null reads them from the request again
	 */
	private function setRequestHeaders(?array $headers): void
	{
		(new \ReflectionProperty(Request::class, 'headers'))->setValue(Request::get(), $headers);
	}

	private function lockAs(Wopi $controller, string $lockId): void
	{
		$this->setRequestHeaders(['x-wopi-lock' => $lockId]);
		$this->call($controller, 'lock', $this->file1->id);
	}

	/**
	 * An editor that crashed never unlocks the file. Its lock must not block a new editing session once it expired.
	 */
	public function testExpiredLockIsReplaced(): void
	{
		$this->file1->lock('crashed-session');
		$this->file1->lock_expires_at = time() - 1;
		$this->assertTrue($this->file1->save());

		$this->lockAs($this->controller($this->writeServiceId, $this->file1), 'new-session');

		$file = File::model()->findByPk($this->file1->id, false, true);
		$this->assertEquals('new-session', $file->lock_id);
		$this->assertGreaterThan(time(), $file->lock_expires_at);
	}

	/**
	 * Also when the expired lock was of another user
	 */
	public function testExpiredLockOfOtherUserIsReplaced(): void
	{
		$this->file1->lock('crashed-session', $this->other->id);
		$this->file1->lock_expires_at = time() - 1;
		$this->file1->ignoreLockChange = true;
		$this->assertTrue($this->file1->save());

		$file = File::model()->findByPk($this->file1->id, false, true);
		$this->lockAs($this->controller($this->writeServiceId, $file), 'new-session');

		$file = File::model()->findByPk($this->file1->id, false, true);
		$this->assertEquals('new-session', $file->lock_id);
		$this->assertEquals($this->user->id, $file->locked_user_id);
	}

	public function testActiveLockIsRefused(): void
	{
		$controller = $this->controller($this->writeServiceId, $this->file1);
		$this->lockAs($controller, 'first-session');

		// Locking again with the same ID refreshes the lock
		$this->lockAs($controller, 'first-session');

		try {
			$this->lockAs($controller, 'second-session');
			$this->fail('A file with an active lock was locked again');
		} catch (Exception $e) {
			$this->assertEquals(409, $e->code);
		}

		$this->assertEquals('first-session', File::model()->findByPk($this->file1->id, false, true)->lock_id);
	}

	/**
	 * UnlockAndRelock: a Lock request with X-WOPI-OldLock replaces the current lock
	 */
	public function testUnlockAndRelock(): void
	{
		$controller = $this->controller($this->writeServiceId, $this->file1);
		$this->lockAs($controller, 'old-lock');

		$this->setRequestHeaders(['x-wopi-lock' => 'new-lock', 'x-wopi-oldlock' => 'old-lock']);
		$this->call($controller, 'lock', $this->file1->id);
		$this->assertEquals('new-lock', File::model()->findByPk($this->file1->id, false, true)->lock_id);

		// The old lock doesn't match anymore
		$this->setRequestHeaders(['x-wopi-lock' => 'other-lock', 'x-wopi-oldlock' => 'old-lock']);
		try {
			$this->call($controller, 'lock', $this->file1->id);
			$this->fail('Relocked with an old lock that doesn\'t match');
		} catch (Exception $e) {
			$this->assertEquals(409, $e->code);
		}
	}

	/**
	 * A lock that the user set in the files module has no expiry time and must keep blocking other editors
	 */
	public function testManualLockDoesNotExpire(): void
	{
		$this->file1->locked_user_id = $this->other->id;
		$this->file1->lock_expires_at = null;
		$this->file1->ignoreLockChange = true;
		$this->assertTrue($this->file1->save());

		$file = File::model()->findByPk($this->file1->id, false, true);
		$this->assertNotEmpty($file->lock_id);
		$this->assertTrue($file->isLocked());

		try {
			$this->lockAs($this->controller($this->writeServiceId, $file), 'wopi-session');
			$this->fail('A manually locked file was locked by WOPI');
		} catch (Exception $e) {
			$this->assertEquals(409, $e->code);
		}

		// Unlocking clears the lock completely
		$file->locked_user_id = 0;
		$file->ignoreLockChange = true;
		$this->assertTrue($file->save());
		$file = File::model()->findByPk($this->file1->id, false, true);
		$this->assertEmpty($file->lock_id);
		$this->assertEmpty($file->lock_expires_at);
		$this->assertFalse($file->isLocked(true));
	}

	/**
	 * The user who locked a file with "Lock" in the menu may edit it, and the lock stays after editing
	 */
	public function testOwnManualLockAllowsEditing(): void
	{
		$this->file1->locked_user_id = $this->user->id;
		$this->file1->lock_expires_at = null;
		$this->assertTrue($this->file1->save());

		$controller = $this->controller($this->writeServiceId, $this->file1);
		$this->lockAs($controller, 'wopi-session');

		$file = File::model()->findByPk($this->file1->id, false, true);
		$this->assertEquals('wopi-session', $file->lock_id);
		$this->assertTrue($file->hasUserLock(), 'The lock must keep having no expiry time');

		// Saving checks the lock ID of the editor
		$this->call($controller, 'checkLock', $file);

		$this->call($controller, 'refreshLock', $file->id);
		$this->assertTrue(File::model()->findByPk($file->id, false, true)->hasUserLock(), 'A refresh must not set an expiry time');

		$this->call($controller, 'unlock', $file->id);
		$file = File::model()->findByPk($file->id, false, true);
		$this->assertTrue($file->hasUserLock(), 'The lock of the user must stay after editing');
		$this->assertEquals($this->user->id, $file->locked_user_id);
		$this->assertNotEquals('wopi-session', $file->lock_id);
		$this->assertTrue($file->isLocked(true));
	}

	/**
	 * Saving an unlocked file through WOPI must not lock it, that lock would have no expiry time
	 */
	public function testCheckLockDoesNotLockUnlockedFile(): void
	{
		$controller = $this->controller($this->writeServiceId, $this->file1);
		$this->setRequestHeaders(['x-wopi-lock' => 'wopi-session']);

		$this->call($controller, 'checkLock', $this->file1, false);
		$this->assertTrue($this->file1->save());

		$this->assertFalse(File::model()->findByPk($this->file1->id, false, true)->isLocked(true));
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

	/**
	 * Collabora rejects a LastModifiedTime with a time zone offset such as +00:00
	 */
	public function testLastModifiedTimeIsUtcRoundTripFormat(): void
	{
		$controller = $this->controller($this->writeServiceId, $this->file1);

		$info = $this->call($controller, 'checkFileInfo', $this->file1->id);

		$this->assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}\.\d{7}Z$/', $info['LastModifiedTime']);
		$this->assertEquals($this->file1->mtime, (new \DateTime($info['LastModifiedTime']))->getTimestamp());
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
