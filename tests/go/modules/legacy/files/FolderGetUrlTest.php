<?php

namespace go\modules\legacy\files;

use go\core\auth\TemporaryState;
use go\core\model\User;
use GO\Base\Exception\AccessDenied;
use GO\Files\Controller\FolderController;
use GO\Files\Model\Folder;
use PHPUnit\Framework\TestCase;

/**
 * files/folder/getURL creates the folder when it doesn't exist. It may only do that where the user may create folders,
 * and it may only return folders that the user may read.
 */
class FolderGetUrlTest extends TestCase
{
	private $authState;
	private bool $ignoreAcl;
	private User $owner;
	private User $other;
	private Folder $ownerFolder;
	private array $cleanupPaths = [];

	protected function setUp(): void
	{
		$this->authState = go()->getAuthState();
		$this->ignoreAcl = \GO::$ignoreAclPermissions;
		\GO::$ignoreAclPermissions = false;

		$this->owner = $this->createUser('urlowner');
		$this->other = $this->createUser('urlother');

		$this->actAs($this->owner->id);
		$this->ownerFolder = Folder::model()->findHomeFolder(\GO\Base\Model\User::model()->findByPk($this->owner->id));

		if(!isset(\GO::session()->values['security_token'])) {
			\GO::session()->values['security_token'] = 'test';
		}
	}

	protected function tearDown(): void
	{
		go()->setAuthState($this->authState);
		\GO::session()->setCurrentUser(User::ID_SUPER_ADMIN);
		\GO::$ignoreAclPermissions = true;

		foreach($this->cleanupPaths as $path) {
			$folder = Folder::model()->findByPath($path);
			if($folder) {
				$folder->delete(true);
			}
		}
		$this->ownerFolder->delete(true);
		$otherHome = Folder::model()->findByPath('users/' . $this->other->username);
		if($otherHome) {
			$otherHome->delete(true);
		}
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

	private function getUrl(string $path): array
	{
		$controller = new FolderController();
		$method = new \ReflectionMethod($controller, 'actionGetURL');
		$method->setAccessible(true);

		return $method->invoke($controller, $path);
	}

	public function testCannotCreateFolderInFolderOfOtherUser(): void
	{
		$this->actAs($this->other->id);

		$path = $this->ownerFolder->path . '/created-by-other';
		try {
			$this->getUrl($path);
			$this->fail('getURL created a folder in the home folder of another user');
		} catch (AccessDenied $e) {
			$this->assertFalse(\GO::$ignoreAclPermissions);
			$this->assertFalse(Folder::model()->findByPath($path));
		}
	}

	public function testCannotCreateTopLevelFolder(): void
	{
		$this->actAs($this->other->id);

		$path = 'users/getUrl-' . uniqid();
		$this->cleanupPaths[] = $path;
		try {
			$this->getUrl($path);
			$this->fail('getURL created a folder in the users folder');
		} catch (AccessDenied $e) {
			$this->assertFalse(Folder::model()->findByPath($path));
		}
	}

	public function testCannotGetUrlOfFolderOfOtherUser(): void
	{
		$this->actAs($this->other->id);

		$this->expectException(AccessDenied::class);
		$this->getUrl($this->ownerFolder->path);
	}

	public function testOwnerCanCreateFolder(): void
	{
		$this->actAs($this->owner->id);

		$response = $this->getUrl('/' . $this->ownerFolder->path . '/created-by-owner/sub/');

		$this->assertTrue($response['success']);
		$folder = Folder::model()->findByPath($this->ownerFolder->path . '/created-by-owner/sub');
		$this->assertNotEmpty($folder);
		$this->assertStringContainsString('#folder/' . $folder->id, $response['url']);
		// a link with the session ID would give the session to anyone who has the link
		$this->assertStringNotContainsString('GOSID', $response['url']);
		$this->assertStringNotContainsString('security_token', $response['url']);
	}
}
