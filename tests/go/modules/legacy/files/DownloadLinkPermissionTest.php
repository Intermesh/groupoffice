<?php

namespace go\modules\legacy\files;

use go\core\auth\TemporaryState;
use go\core\model\User;
use GO\Base\Exception\AccessDenied;
use GO\Base\Model\Acl;
use GO\Files\Controller\FileController;
use GO\Files\Model\File;
use GO\Files\Model\Folder;
use PHPUnit\Framework\TestCase;

/**
 * Anyone who may read a file may create a download link for it or get the existing one. Deleting the file when the link
 * expires requires delete permission.
 */
class DownloadLinkPermissionTest extends TestCase
{
	private $authState;
	private bool $ignoreAcl;
	private bool $disableModelCache;
	private User $owner;
	private User $reader;
	private Folder $ownerFolder;
	private File $file;

	protected function setUp(): void
	{
		$this->authState = go()->getAuthState();
		$this->ignoreAcl = \GO::$ignoreAclPermissions;
		\GO::$ignoreAclPermissions = false;
		// cached models keep the permission level of the user that loaded them
		$this->disableModelCache = \GO::$disableModelCache;
		\GO::$disableModelCache = true;

		$this->owner = $this->createUser('linkowner');
		$this->reader = $this->createUser('linkreader');

		$this->actAs($this->owner->id);
		$this->ownerFolder = Folder::model()->findHomeFolder(\GO\Base\Model\User::model()->findByPk($this->owner->id));
		file_put_contents($this->ownerFolder->fsFolder->path() . '/shared.txt', 'content');
		$this->file = $this->ownerFolder->addFile('shared.txt');

		\GO::$ignoreAclPermissions = true;
		Acl::model()->findByPk($this->ownerFolder->acl_id)->addGroup($this->reader->getPersonalGroup()->id, Acl::READ_PERMISSION);
		\GO::$ignoreAclPermissions = false;
	}

	protected function tearDown(): void
	{
		go()->setAuthState($this->authState);
		\GO::session()->setCurrentUser(User::ID_SUPER_ADMIN);
		\GO::$ignoreAclPermissions = true;

		$this->ownerFolder->delete(true);
		$readerHome = Folder::model()->findByPath('users/' . $this->reader->username);
		if ($readerHome) {
			$readerHome->delete(true);
		}
		User::delete(['id' => [$this->owner->id, $this->reader->id]]);

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

	private function createDownloadLink(int $expireTime, int $deleteWhenExpired = 0): array
	{
		$controller = new FileController();
		$method = new \ReflectionMethod($controller, 'actionCreateDownloadLink');
		$method->setAccessible(true);

		return $method->invoke($controller, [
			'id' => $this->file->id,
			'expire_time' => $expireTime,
			'delete_when_expired' => $deleteWhenExpired
		]);
	}

	private function emailDownloadLink(int $expireTime, int $deleteWhenExpired = 0): array
	{
		$controller = new FileController();
		$method = new \ReflectionMethod($controller, 'actionEmailDownloadLink');
		$method->setAccessible(true);

		return $method->invoke($controller, [
			'ids' => json_encode([$this->file->id]),
			'expire_time' => $expireTime,
			'delete_when_expired' => $deleteWhenExpired,
			'content_type' => 'html',
			'template_id' => 0
		]);
	}

	private function reloadFile(): File
	{
		return File::model()->findByPk($this->file->id, false, true, true);
	}

	public function testReaderCanCreateDownloadLink(): void
	{
		$this->actAs($this->reader->id);
		$response = $this->createDownloadLink(time() + 5 * 86400);

		$randomCode = $this->reloadFile()->random_code;
		$this->assertNotEmpty($randomCode);
		$this->assertStringContainsString($randomCode, $response['url']);
	}

	public function testReaderCanEmailDownloadLink(): void
	{
		$this->actAs($this->reader->id);
		$response = $this->emailDownloadLink(time() + 5 * 86400);

		$randomCode = $this->reloadFile()->random_code;
		$this->assertNotEmpty($randomCode);
		$this->assertStringContainsString($randomCode, $response['data']['htmlbody']);
	}

	public function testReaderCannotDeleteFileWhenLinkExpires(): void
	{
		$this->actAs($this->reader->id);
		try {
			$this->createDownloadLink(time() + 5 * 86400, 1);
			$this->fail('A user with read permission made the file delete itself when the link expires');
		} catch (AccessDenied $e) {
			$this->assertFalse((bool) $this->reloadFile()->delete_when_expired);
		}

		try {
			$this->emailDownloadLink(time() + 5 * 86400, 1);
			$this->fail('A user with read permission made the file delete itself when the link expires');
		} catch (AccessDenied $e) {
			$this->assertFalse((bool) $this->reloadFile()->delete_when_expired);
		}
	}

	public function testOwnerCanDeleteFileWhenLinkExpires(): void
	{
		$this->actAs($this->owner->id);
		$this->createDownloadLink(time() + 5 * 86400, 1);

		$this->assertTrue((bool) $this->reloadFile()->delete_when_expired);
	}

	public function testUserWithoutAccessCannotCreateDownloadLink(): void
	{
		$stranger = $this->createUser('linkstranger');
		try {
			$this->actAs($stranger->id);
			$this->createDownloadLink(time() + 5 * 86400);
			$this->fail('A user without access created a download link');
		} catch (AccessDenied $e) {
			$this->assertEmpty($this->reloadFile()->random_code);
		} finally {
			go()->setAuthState(new TemporaryState(User::ID_SUPER_ADMIN));
			\GO::session()->setCurrentUser(User::ID_SUPER_ADMIN);
			\GO::$ignoreAclPermissions = true;
			$home = Folder::model()->findByPath('users/' . $stranger->username);
			if ($home) {
				$home->delete(true);
			}
			User::delete(['id' => $stranger->id]);
			\GO::$ignoreAclPermissions = false;
		}
	}

	public function testFileNameIsEscaped(): void
	{
		$this->actAs($this->owner->id);
		$this->file->name = '<img src=x onerror=alert(1)>.txt';
		$this->assertTrue($this->file->save());

		$response = $this->emailDownloadLink(time() + 5 * 86400);

		$this->assertStringNotContainsString('<img', $response['data']['htmlbody']);
		$this->assertStringContainsString('&lt;img src=x onerror=alert(1)&gt;.txt', $response['data']['htmlbody']);
	}
}
