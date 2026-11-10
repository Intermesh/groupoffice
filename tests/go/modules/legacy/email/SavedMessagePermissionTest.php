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
	private bool $disableModelCache;
	private User $owner;
	private User $other;
	private Folder $ownerFolder;
	private Folder $otherFolder;
	private string $emlPath;
	private ?\go\core\model\Acl $linkAcl = null;
	private ?\GO\Savemailas\Model\LinkedEmail $linkedEmail = null;
	private string $linkedPath;

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
		$this->disableModelCache = \GO::$disableModelCache;

		// Models remember the permission level of the user they were first used by. The users change in this test.
		\GO::$disableModelCache = true;

		$this->owner = $this->createUser('emlowner');
		$this->other = $this->createUser('emlother');

		$this->actAs($this->owner->id);
		$this->ownerFolder = Folder::model()->findHomeFolder(\GO\Base\Model\User::model()->findByPk($this->owner->id));
		file_put_contents($this->ownerFolder->fsFolder->path() . '/private.eml', self::MIME);
		$this->emlPath = $this->ownerFolder->addFile('private.eml', true)->path;

		$this->actAs($this->other->id);
		$this->otherFolder = Folder::model()->findHomeFolder(\GO\Base\Model\User::model()->findByPk($this->other->id));
	}

	/**
	 * A linked e-mail (savemailas module) is not in the files module. It's in file_storage_path/email and has an ACL of
	 * the item it's linked to. The owner may read this one.
	 */
	private function createLinkedEmail(): void
	{
		if(!\GO::modules()->isInstalled('savemailas')) {
			$this->markTestSkipped('The savemailas module is required');
		}

		$this->linkedPath = 'email/phpunit/' . uniqid('linked') . '.eml';
		$fsFile = new \GO\Base\Fs\File(\GO::config()->file_storage_path . $this->linkedPath);
		$fsFile->parent()->create();
		$fsFile->putContents(self::MIME);

		// as the owner, because the user who creates an ACL gets manage permission
		$this->actAs($this->owner->id);
		$this->linkAcl = new \go\core\model\Acl();
		$this->linkAcl->usedIn = 'em_links.acl_id';
		$this->linkAcl->ownedBy = $this->owner->id;
		$this->assertTrue($this->linkAcl->save());

		$this->linkedEmail = new \GO\Savemailas\Model\LinkedEmail();
		$this->linkedEmail->setAttributes([
			'user_id' => $this->owner->id,
			'subject' => 'Private subject',
			'path' => $this->linkedPath,
			'acl_id' => $this->linkAcl->id,
			'uid' => uniqid('linked'),
			'ctime' => time(),
			'mtime' => time()
		]);
		$this->assertTrue($this->linkedEmail->save(true), var_export($this->linkedEmail->getValidationErrors(), true));
	}

	protected function tearDown(): void
	{
		go()->setAuthState($this->authState);
		\GO::session()->setCurrentUser(User::ID_SUPER_ADMIN);
		\GO::$ignoreAclPermissions = true;

		if(isset($this->linkedEmail)) {
			$this->linkedEmail->delete(true);
			\go\core\model\Acl::delete(['id' => $this->linkAcl->id]);
			$fsFile = new \GO\Base\Fs\File(\GO::config()->file_storage_path . $this->linkedPath);
			$fsFile->delete();
			$fsFile->parent()->delete();
		}

		$this->ownerFolder->delete(true);
		$this->otherFolder->delete(true);
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

	public function testUserWhoMayReadLinkedEmailCanForward(): void
	{
		$this->createLinkedEmail();
		$this->actAs($this->owner->id);

		$this->assertSame('Private subject', SavedMessage::model()->createFromReadableMimeFile($this->linkedPath)->subject);
		// Like the linked message panel does. Reply also needs an e-mail account, which the test user doesn't have.
		$this->assertNotEmpty($this->invoke('actionForward', [['path' => $this->linkedPath, 'is_tmp_file' => 'false', 'content_type' => 'plain']]));
	}

	public function testUserWhoMayReadLinkedEmailCanSaveItsAttachments(): void
	{
		$this->createLinkedEmail();
		$this->actAs($this->owner->id);

		$response = $this->invoke('actionSaveAllAttachments', [$this->ownerFolder->id, 0, '', 0, $this->linkedPath]);

		$this->assertTrue($response['success']);
		$this->assertNotEmpty(glob($this->ownerFolder->fsFolder->path() . '/*secret*'));
	}

	public function testOtherUserCannotReadLinkedEmail(): void
	{
		$this->createLinkedEmail();
		$this->actAs($this->other->id);

		try {
			$this->invoke('actionReply', [['path' => $this->linkedPath, 'is_tmp_file' => 'false', 'content_type' => 'plain']]);
			$this->fail('A linked e-mail the user may not read was opened');
		} catch (AccessDenied $e) {
			$this->assertTrue(true);
		}

		$this->expectException(AccessDenied::class);
		SavedMessage::model()->createFromReadableMimeFile($this->linkedPath);
	}

	public function testUserWithAccessToTheLinkedItemCanReadLinkedEmail(): void
	{
		$this->createLinkedEmail();
		$this->linkAcl->addGroup($this->other->getPersonalGroup()->id, \go\core\model\Acl::LEVEL_READ);
		$this->assertTrue($this->linkAcl->save());

		$this->actAs($this->other->id);

		$this->assertSame('Private subject', SavedMessage::model()->createFromReadableMimeFile($this->linkedPath)->subject);
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
