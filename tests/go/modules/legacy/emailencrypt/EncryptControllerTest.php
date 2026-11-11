<?php
namespace go\modules\legacy\emailencrypt;

use GO\Base\Exception\AccessDenied;
use GO\Emailencrypt\Controller\EncryptController;
use GO\Emailencrypt\Model\Encrypt;
use go\core\auth\TemporaryState;
use go\core\model\User;
use PHPUnit\Framework\TestCase;

/**
 * Encrypting attachments puts files in a zip file for the user. The paths of files from the file storage come from the
 * client, so only files that the user may read may be used.
 */
class EncryptControllerTest extends TestCase
{
	private $authState;
	private bool $installed = false;
	private array $tables = [];
	private User $user;
	private Encrypt $key;
	private string $adminFile;

	protected function setUp(): void
	{
		$this->authState = go()->getAuthState();
		$this->actAs(User::ID_SUPER_ADMIN);

		if (!\GO::modules()->isInstalled('emailencrypt')) {
			$tables = $this->findTables();
			$old = \GO::setIgnoreAclPermissions(true);
			try {
				\GO\Base\Model\Module::install('emailencrypt');
			} finally {
				\GO::setIgnoreAclPermissions($old);
				$this->tables = array_diff($this->findTables(), $tables);
			}
			$this->installed = true;
		}

		$this->key = new Encrypt();
		$this->key->name = uniqid('key');
		$this->key->password = 'test';
		$this->key->ctime = time();
		$this->assertTrue($this->key->save(true));

		// a file of the administrator that the user may not read
		$home = \GO\Files\Model\Folder::model()->findHomeFolder(\GO\Base\Model\User::model()->findByPk(User::ID_SUPER_ADMIN, false, true));
		$name = uniqid('secret') . '.txt';
		file_put_contents($home->fsFolder->path() . '/' . $name, 'secret');
		$this->adminFile = $home->addFile($name)->path;

		$name = uniqid('encuser');
		$this->user = new User();
		$this->user->setValues([
			'username' => $name,
			'displayName' => $name,
			'email' => $name . '@example.com',
			'password' => 'Pa55word!!xyz'
		]);
		$this->assertTrue($this->user->save(), $this->user->getValidationErrorsAsString());
	}

	protected function tearDown(): void
	{
		$this->actAs(User::ID_SUPER_ADMIN);
		$old = \GO::setIgnoreAclPermissions(true);
		try {
			$file = \GO\Files\Model\File::model()->findByPath($this->adminFile);
			if ($file) {
				$file->delete(true);
			}
			$this->key->delete(true);
			User::delete(['id' => $this->user->id]);

			if ($this->installed) {
				\GO\Base\Model\Module::model()->findByName('emailencrypt')->delete();
				$oldFKchecks = go()->getDbConnection()->foreignKeyChecks(false);
				foreach ($this->tables as $table) {
					go()->getDbConnection()->exec('DROP TABLE `' . $table . '`');
				}
				if ($oldFKchecks) {
					go()->getDbConnection()->foreignKeyChecks(true);
				}
			}
		} finally {
			\GO::setIgnoreAclPermissions($old);
		}
		go()->setAuthState($this->authState);
	}

	private function findTables(): array
	{
		return go()->getDbConnection()->query('SHOW TABLES')->fetchAll(\PDO::FETCH_COLUMN);
	}

	private function actAs(int $userId): void
	{
		go()->setAuthState(new TemporaryState($userId));
		\GO::session()->setCurrentUser($userId);
	}

	public static function paths(): array
	{
		return [
			'file of another user' => [null],
			'outside the file storage' => ['../../../../../../etc/hostname'],
		];
	}

	#[\PHPUnit\Framework\Attributes\DataProvider('paths')]
	public function testOnlyReadableFilesAreEncrypted(?string $path): void
	{
		$this->actAs($this->user->id);
		$method = new \ReflectionMethod(EncryptController::class, 'actionEncrypt');
		ob_start();
		try {
			$method->invoke(new EncryptController(), [
				'passwordId' => $this->key->id,
				'attachments' => json_encode([[
					'from_file_storage' => true,
					'tmp_file' => $path ?? $this->adminFile,
					'fileName' => 'file.txt'
				]])
			]);
			$output = ob_get_contents();
			$this->fail('The file was encrypted: ' . $output);
		} catch (AccessDenied $e) {
			$this->assertTrue(true);
		} finally {
			ob_end_clean();
			$this->actAs(User::ID_SUPER_ADMIN);
		}
	}
}
