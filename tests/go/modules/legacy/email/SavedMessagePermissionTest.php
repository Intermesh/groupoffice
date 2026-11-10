<?php

namespace go\modules\legacy\email;

use go\core\auth\TemporaryState;
use go\core\model\User;
use GO\Base\Exception\AccessDenied;
use GO\Email\Controller\MessageController;
use GO\Email\Model\SavedMessage;
use GO\Files\Model\Folder;
use PHPUnit\Framework\TestCase;

/**
 * Email actions that open a saved MIME message by path (openDraft, reply, forward, saveAllAttachments) must only read
 * files in the file storage that the user may read.
 */
class SavedMessagePermissionTest extends TestCase
{
	private $authState;
	private bool $ignoreAcl;
	private User $owner;
	private User $other;
	private Folder $ownerFolder;
	private Folder $otherFolder;
	private string $emlPath;

	const MIME = "From: owner@example.com\r\n" .
		"To: someone@example.com\r\n" .
		"Subject: Private subject\r\n" .
		"MIME-Version: 1.0\r\n" .
		"Content-Type: multipart/mixed; boundary=\"b1\"\r\n" .
		"\r\n" .
		"--b1\r\n" .
		"Content-Type: text/plain; charset=utf-8\r\n" .
		"\r\n" .
		"Private body\r\n" .
		"--b1\r\n" .
		"Content-Type: text/plain; name=\"secret.txt\"\r\n" .
		"Content-Disposition: attachment; filename=\"secret.txt\"\r\n" .
		"\r\n" .
		"Private attachment\r\n" .
		"--b1--\r\n";

	protected function setUp(): void
	{
		if(!\GO::modules()->isInstalled('email') || !\GO::modules()->isInstalled('files')) {
			$this->markTestSkipped('The email and files modules are required');
		}

		$this->authState = go()->getAuthState();
		$this->ignoreAcl = \GO::$ignoreAclPermissions;

		$this->owner = $this->createUser('emlowner');
		$this->other = $this->createUser('emlother');

		$this->actAs($this->owner->id);
		$this->ownerFolder = Folder::model()->findHomeFolder(\GO\Base\Model\User::model()->findByPk($this->owner->id));
		file_put_contents($this->ownerFolder->fsFolder->path() . '/private.eml', self::MIME);
		$this->emlPath = $this->ownerFolder->addFile('private.eml', true)->path;

		$this->actAs($this->other->id);
		$this->otherFolder = Folder::model()->findHomeFolder(\GO\Base\Model\User::model()->findByPk($this->other->id));
	}

	protected function tearDown(): void
	{
		go()->setAuthState($this->authState);
		\GO::session()->setCurrentUser(User::ID_SUPER_ADMIN);
		\GO::$ignoreAclPermissions = true;

		$this->ownerFolder->delete(true);
		$this->otherFolder->delete(true);
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

	private function invoke(string $action, array $args)
	{
		$controller = new MessageController();
		$method = new \ReflectionMethod($controller, $action);
		$method->setAccessible(true);
		return $method->invokeArgs($controller, $args);
	}

	public function testOtherUserCannotReadSavedMessage(): void
	{
		$this->actAs($this->other->id);

		$this->expectException(AccessDenied::class);
		SavedMessage::model()->createFromReadableMimeFile($this->emlPath);
	}

	public function testMissingFileGivesSameErrorAsUnreadableFile(): void
	{
		$this->actAs($this->other->id);

		$this->expectException(AccessDenied::class);
		SavedMessage::model()->createFromReadableMimeFile($this->ownerFolder->path . '/does-not-exist.eml');
	}

	public function testOtherUserCannotOpenSavedMessageAsDraft(): void
	{
		$this->actAs($this->other->id);

		$this->expectException(AccessDenied::class);
		$this->invoke('actionOpenDraft', [['path' => $this->emlPath, 'content_type' => 'plain']]);
	}

	public function testOtherUserCannotReplyToSavedMessage(): void
	{
		$this->actAs($this->other->id);

		// is_tmp_file comes from the client too
		$this->expectException(AccessDenied::class);
		$this->invoke('actionReply', [['path' => $this->emlPath, 'is_tmp_file' => 'false', 'content_type' => 'plain']]);
	}

	public function testOtherUserCannotForwardSavedMessage(): void
	{
		$this->actAs($this->other->id);

		$this->expectException(AccessDenied::class);
		$this->invoke('actionForward', [['path' => $this->emlPath, 'content_type' => 'plain']]);
	}

	public function testOtherUserCannotSaveAttachmentsOfSavedMessage(): void
	{
		$this->actAs($this->other->id);

		try {
			$this->invoke('actionSaveAllAttachments', [$this->otherFolder->id, 0, '', 0, $this->emlPath]);
			$this->fail('saveAllAttachments read a saved message the user may not read');
		} catch (AccessDenied $e) {
			$this->assertFileDoesNotExist($this->otherFolder->fsFolder->path() . '/secret.txt');
		}
	}

	public function testOwnerCanReadSavedMessage(): void
	{
		$this->actAs($this->owner->id);

		$message = SavedMessage::model()->createFromReadableMimeFile($this->emlPath);

		$this->assertSame('Private subject', $message->subject);
	}

	public function testOwnerCanSaveAttachmentsOfSavedMessage(): void
	{
		$this->actAs($this->owner->id);

		$response = $this->invoke('actionSaveAllAttachments', [$this->ownerFolder->id, 0, '', 0, $this->emlPath]);

		$this->assertTrue($response['success']);
		$this->assertNotEmpty(glob($this->ownerFolder->fsFolder->path() . '/*secret*'));
	}
}
