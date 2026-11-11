<?php
namespace go\modules\legacy\site;

use GO\Base\Exception\AccessDenied;
use GO\Files\Model\File;
use GO\Files\Model\Folder;
use GO\Site\Controller\MultifileController;
use go\core\auth\TemporaryState;
use go\core\model\User;
use go\modules\business\ModuleTablesTrait;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../business/fixtures/ModuleTablesTrait.php';

/**
 * The multi file field of the site module lists and adds files by id. Only files the user may read can be listed or
 * added.
 */
class MultifileControllerTest extends TestCase
{
	use ModuleTablesTrait;

	private const MODEL_ID = 987654;
	private const FIELD_ID = 1;

	private $authState;
	private bool $ignoreAcl;
	private bool $disableModelCache;
	private User $owner;
	private User $other;
	private Folder $ownerFolder;
	private File $file;

	protected function setUp(): void
	{
		$this->authState = go()->getAuthState();
		$this->ignoreAcl = \GO::$ignoreAclPermissions;
		\GO::$ignoreAclPermissions = false;
		$this->disableModelCache = \GO::$disableModelCache;
		\GO::$disableModelCache = true;

		$this->createTables(go()->getEnvironment()->getInstallPath() . '/modules/site/install/install.sql', ['site_multifile_files']);

		$this->owner = $this->createUser('mfowner');
		$this->other = $this->createUser('mfother');

		$this->actAs($this->owner->id);
		$this->ownerFolder = Folder::model()->findHomeFolder(\GO\Base\Model\User::model()->findByPk($this->owner->id));
		file_put_contents($this->ownerFolder->fsFolder->path() . '/private.txt', 'private');
		$this->file = $this->ownerFolder->addFile('private.txt');

		go()->getDbConnection()->insert('site_multifile_files', [
			'model_id' => self::MODEL_ID,
			'field_id' => self::FIELD_ID,
			'file_id' => $this->file->id
		])->execute();
	}

	protected function tearDown(): void
	{
		$this->actAs(User::ID_SUPER_ADMIN);
		\GO::$ignoreAclPermissions = true;
		go()->getDbConnection()->delete('site_multifile_files', ['model_id' => [self::MODEL_ID, self::MODEL_ID + 1]])->execute();
		$this->ownerFolder->delete(true);
		$otherHome = Folder::model()->findByPath('users/' . $this->other->username);
		if ($otherHome) {
			$otherHome->delete(true);
		}
		User::delete(['id' => [$this->owner->id, $this->other->id]]);
		$this->uninstallModuleTables();

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

	private function store(int $userId, array $params = []): array
	{
		$this->actAs($userId);
		ob_start();
		try {
			(new MultifileController())->actionStore(array_merge(['model_id' => self::MODEL_ID, 'field_id' => self::FIELD_ID], $params));
		} finally {
			$output = ob_get_clean();
			$this->actAs(User::ID_SUPER_ADMIN);
		}
		$response = json_decode($output, true);
		$this->assertIsArray($response, $output);
		return array_map('intval', array_column($response['results'], 'id'));
	}

	public function testOnlyReadableFilesAreListed(): void
	{
		$this->assertContains((int) $this->file->id, $this->store($this->owner->id));
		$this->assertNotContains((int) $this->file->id, $this->store($this->other->id));
	}

	public function testOnlyReadableFilesCanBeAdded(): void
	{
		$this->expectException(AccessDenied::class);
		$this->store($this->other->id, ['model_id' => self::MODEL_ID + 1, 'addFileStorageFilesById' => json_encode([$this->file->id])]);
	}
}
