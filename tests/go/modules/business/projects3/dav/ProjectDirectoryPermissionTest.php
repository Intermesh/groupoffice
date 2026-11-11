<?php
namespace go\modules\business\projects3\dav;

use go\core\auth\TemporaryState;
use go\core\model\Acl;
use go\core\model\User;
use go\modules\business\business\model\Business;
use go\modules\business\ModuleTablesTrait;
use go\modules\business\projects3\model\Project3;
use go\modules\business\projects3\model\ProjectBook;
use PHPUnit\Framework\TestCase;
use Sabre\DAV\Exception\Forbidden;

require_once __DIR__ . '/../../fixtures/ModuleTablesTrait.php';

/**
 * Creating a file or folder in the WebDAV folder of a project creates the files folder of the project and saves the
 * project, so it needs write permission on the project first.
 */
class ProjectDirectoryPermissionTest extends TestCase
{
	use ModuleTablesTrait;

	private $authState;
	private bool $ignoreAcl;
	private bool $disableModelCache;
	private User $user;
	private ?Business $business = null;
	private ?ProjectBook $book = null;
	private ?Project3 $project = null;

	protected function setUp(): void
	{
		$this->authState = go()->getAuthState();
		$this->ignoreAcl = \GO::$ignoreAclPermissions;
		$this->disableModelCache = \GO::$disableModelCache;
		\GO::$disableModelCache = true;
		$this->actAs(User::ID_SUPER_ADMIN);

		$path = go()->getEnvironment()->getInstallPath() . '/go/modules/business/';
		$this->installModuleTables('business', 'business', $path . 'business/install/install.sql');
		$this->installModuleTables('business', 'projects3', $path . 'projects3/install/install.sql');
		// The tables are created for every test, so books get the same id. Forget the books of other tests.
		(new \ReflectionProperty(\go\core\acl\model\AclItemEntity::class, 'aclEntityCache'))->setValue(null, []);

		$name = uniqid('p3user');
		$this->user = new User();
		$this->user->setValues([
			'username' => $name,
			'displayName' => $name,
			'email' => $name . '@example.com',
			'password' => 'Pa55word!!xyz'
		]);
		$this->assertTrue($this->user->save(), $this->user->getValidationErrorsAsString());

		$this->business = new Business();
		$this->business->name = $name;
		$this->assertTrue($this->business->save(), $this->business->getValidationErrorsAsString());

		$this->book = new ProjectBook();
		$this->book->name = $name;
		$this->book->businessId = $this->business->id;
		$this->book->setAcl([$this->user->getPersonalGroup()->id => Acl::LEVEL_READ]);
		$this->assertTrue($this->book->save(), $this->book->getValidationErrorsAsString());

		$this->project = new Project3();
		$this->project->name = $name;
		$this->project->bookId = $this->book->id;
		$this->assertTrue($this->project->save(), $this->project->getValidationErrorsAsString());
	}

	protected function tearDown(): void
	{
		$this->actAs(User::ID_SUPER_ADMIN);
		\GO::$ignoreAclPermissions = true;
		if ($this->project) {
			$project = Project3::findById($this->project->id);
			if ($project && $project->filesFolderId) {
				$folder = \GO\Files\Model\Folder::model()->findByPk($project->filesFolderId, false, true);
				$folder?->delete(true);
			}
			Project3::delete(['id' => $this->project->id]);
		}
		if ($this->book) {
			ProjectBook::delete(['id' => $this->book->id]);
		}
		if ($this->business) {
			// Business::delete() needs the finance tables
			go()->getDbConnection()->delete('business_business', ['id' => $this->business->id])->execute();
		}
		if (isset($this->user)) {
			User::delete(['id' => $this->user->id]);
		}
		if (in_array('business_projects3_project3', $this->createdModuleTables)) {
			// install.sql adds these to the tasks table, which isn't dropped
			foreach (['tasks_task_business_projects3_project3_id_fk', 'tasks_task_business_projects3_project3_milestone_id_fk'] as $fk) {
				if (go()->getDbConnection()->query("SELECT 1 FROM information_schema.TABLE_CONSTRAINTS WHERE CONSTRAINT_SCHEMA = DATABASE() AND TABLE_NAME = 'tasks_task' AND CONSTRAINT_NAME = '" . $fk . "'")->fetch()) {
					go()->getDbConnection()->exec('ALTER TABLE `tasks_task` DROP FOREIGN KEY `' . $fk . '`');
				}
			}
		}
		$this->uninstallModuleTables();

		\GO::$ignoreAclPermissions = $this->ignoreAcl;
		\GO::$disableModelCache = $this->disableModelCache;
		go()->setAuthState($this->authState);
	}

	private function actAs(int $userId): void
	{
		go()->setAuthState(new TemporaryState($userId));
		\GO::session()->setCurrentUser($userId);
	}

	private function directory(): ProjectDirectory
	{
		return new ProjectDirectory(Project3::findById($this->project->id));
	}

	public function testReaderCantCreateFilesFolder(): void
	{
		$this->actAs($this->user->id);
		try {
			$this->directory()->createDirectory('test');
			$this->fail('A user with read permission created a folder');
		} catch (Forbidden $e) {
			$this->assertNull(Project3::findById($this->project->id)->filesFolderId, 'The files folder of the project was created');
		}
	}

	public function testWriterCreatesFilesFolder(): void
	{
		$this->actAs(User::ID_SUPER_ADMIN);
		$this->book->setAcl([$this->user->getPersonalGroup()->id => Acl::LEVEL_WRITE]);
		$this->assertTrue($this->book->save(), $this->book->getValidationErrorsAsString());

		$this->actAs($this->user->id);
		$this->directory()->createDirectory('test');
		$this->assertNotNull(Project3::findById($this->project->id)->filesFolderId);
	}
}
