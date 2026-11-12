<?php
namespace go\modules\legacy\eliteodsextractor;

use GO\Base\Exception\AccessDenied;
use GO\Base\Model\Acl;
use GO\Eliteodsextractor\Controller\PrivateFileController;
use GO\Files\Model\Folder;
use go\core\auth\TemporaryState;
use go\core\model\Acl as CoreAcl;
use go\core\model\Field;
use go\core\model\FieldSet;
use go\core\model\User;
use PHPUnit\Framework\TestCase;

/**
 * The private file field reads a selected file and creates the folder of a field value from request values.
 */
class PrivateFileControllerTest extends TestCase
{
	private $authState;
	private bool $ignoreAcl;
	private bool $disableModelCache;

	private User $writer;
	private User $nobody;
	private FieldSet $fieldSet;
	private Field $field;
	private Folder $folder;
	private Acl $acl;
	private string $filePath;

	protected function setUp(): void
	{
		if (!\GO::modules()->isInstalled('files')) {
			$this->markTestSkipped('The files module is not installed');
		}
		if (!is_file(\GO::config()->root_path . 'promodules/eliteodsextractor/controller/PrivateFileController.php')) {
			$this->markTestSkipped('The eliteodsextractor module is not available');
		}

		$this->authState = go()->getAuthState();
		$this->ignoreAcl = \GO::$ignoreAclPermissions;
		$this->disableModelCache = \GO::$disableModelCache;
		// Models remember the permission level of the user they were first used by. The users change in this test.
		\GO::$disableModelCache = true;

		$this->writer = $this->newUser();
		$this->nobody = $this->newUser();

		$this->actAs($this->adminId());
		\GO::$ignoreAclPermissions = true;

		$this->fieldSet = new FieldSet();
		$this->fieldSet->setEntity('A');
		$this->fieldSet->name = uniqid('odsfieldset');
		$this->assertTrue($this->fieldSet->save(), $this->fieldSet->getValidationErrorsAsString());
		// Field sets are writable for all internal users by default. Only the writer may write to this one.
		go()->getDbConnection()->delete('core_acl_group', ['aclId' => $this->fieldSet->findAclId()])->execute();
		CoreAcl::findById($this->fieldSet->findAclId())->addGroup($this->writer->getPersonalGroup()->id, CoreAcl::LEVEL_WRITE)->save();

		$this->field = new Field();
		$this->field->fieldSetId = $this->fieldSet->id;
		$this->field->name = 'ODS file';
		$this->field->databaseName = uniqid('odsfile');
		$this->field->type = 'Text';
		$this->assertTrue($this->field->save(), $this->field->getValidationErrorsAsString());

		// A file that only the writer may read
		$home = Folder::model()->findHomeFolder(\GO::user());
		$this->folder = $home->addFolder('ods-permissions-' . uniqid());
		$this->acl = new Acl();
		$this->acl->usedIn = 'fs_folders.acl_id';
		$this->acl->ownedBy = User::ID_SUPER_ADMIN;
		$this->acl->save();
		$this->acl->addGroup($this->writer->getPersonalGroup()->id, Acl::READ_PERMISSION);
		$this->folder->acl_id = $this->acl->id;
		$this->folder->save();
		(new \GO\Base\Fs\File($this->folder->fsFolder->path() . '/sheet.ods'))->putContents('not really a spreadsheet');
		$this->folder->addFile('sheet.ods');
		$this->filePath = $this->folder->path . '/sheet.ods';

		\GO::$ignoreAclPermissions = false;
	}

	protected function tearDown(): void
	{
		if (!isset($this->fieldSet)) {
			return;
		}

		$this->actAs($this->adminId());
		\GO::$ignoreAclPermissions = true;

		$folder = Folder::model()->findByPath('customfields/' . $this->fieldSet->id);
		if ($folder) {
			$folder->delete(true);
		}
		$this->folder->delete(true);
		$this->acl->delete();
		Field::delete(['id' => $this->field->id]);
		FieldSet::delete(['id' => $this->fieldSet->id]);
		User::delete(['id' => [$this->writer->id, $this->nobody->id]]);

		\GO::$ignoreAclPermissions = $this->ignoreAcl;
		\GO::$disableModelCache = $this->disableModelCache;
		go()->setAuthState($this->authState);
	}

	private function adminId(): int
	{
		return User::ID_SUPER_ADMIN;
	}

	private function newUser(): User
	{
		$name = uniqid('ods');
		$user = new User();
		$user->setValues([
			'username' => $name,
			'displayName' => $name,
			'email' => $name . '@example.com',
			'password' => 'Pa55word!!xyz'
		]);
		$this->assertTrue($user->save(), $user->getValidationErrorsAsString());
		return $user;
	}

	private function actAs(int $userId): void
	{
		go()->setAuthState(new TemporaryState($userId));
		\GO::session()->setCurrentUser($userId);
		Folder::model()->clearFolderCache();
	}

	private function call(string $action, ...$args): string
	{
		ob_start();
		try {
			(new PrivateFileController())->$action(...$args);
		} finally {
			$output = ob_get_clean();
		}
		return $output;
	}

	public function testSelectFileNeedsReadPermission(): void
	{
		$this->actAs($this->nobody->id);
		$this->expectException(AccessDenied::class);
		$this->call('actionSelectFile', $this->fieldSet->id, $this->field->databaseName, $this->filePath);
	}

	public function testSelectFileWithReadPermission(): void
	{
		$this->actAs($this->writer->id);
		$response = json_decode($this->call('actionSelectFile', $this->fieldSet->id, $this->field->databaseName, $this->filePath), true);
		$this->assertTrue($response['success']);
	}

	public function testRootFolderNeedsWritePermissionOnFieldSet(): void
	{
		$this->actAs($this->nobody->id);
		$this->assertLessThan(CoreAcl::LEVEL_WRITE, FieldSet::findById($this->fieldSet->id)->getPermissionLevel());
		try {
			$this->call('actionRootFolderId', $this->fieldSet->id, $this->field->databaseName, 1);
			$this->fail('The folder was created');
		} catch (AccessDenied $e) {
			$this->assertFalse(Folder::model()->findByPath('customfields/' . $this->fieldSet->id), 'The folder was created');
		}
	}

	public function testRootFolderWithWritePermissionOnFieldSet(): void
	{
		$this->actAs($this->writer->id);
		$response = json_decode($this->call('actionRootFolderId', $this->fieldSet->id, $this->field->databaseName, 1), true);
		$this->assertNotEmpty($response['file_folder_id']);
	}

	public function testRootFolderRefusesOtherFolderNames(): void
	{
		$this->actAs($this->writer->id);
		$this->expectException(\GO\Base\Exception\NotFound::class);
		$this->call('actionRootFolderId', $this->fieldSet->id, $this->field->databaseName, '../1');
	}
}
