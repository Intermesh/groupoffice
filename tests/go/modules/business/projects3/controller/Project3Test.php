<?php
namespace go\modules\business\projects3\controller;

use go\core\auth\TemporaryState;
use go\core\exception\Forbidden;
use go\core\model\Acl;
use go\core\model\Group;
use go\core\model\User;
use go\modules\business\business\model\Business;
use go\modules\business\ModuleTablesTrait;
use go\modules\business\projects3\model\Project3 as Project3Model;
use go\modules\business\projects3\model\ProjectBook;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../fixtures/ModuleTablesTrait.php';

/**
 * The booked hours of the employees and the task progress of a project are only for users who may read the project
 */
class Project3Test extends TestCase
{
	use ModuleTablesTrait;

	private $authState;
	private User $user;
	private ?Business $business = null;
	private ?ProjectBook $book = null;
	private ?Project3Model $project = null;

	protected function setUp(): void
	{
		$this->authState = go()->getAuthState();
		$this->actAs(User::ID_SUPER_ADMIN);

		$path = go()->getEnvironment()->getInstallPath() . '/go/modules/business/';
		$this->installModuleTables('business', 'business', $path . 'business/install/install.sql');
		$this->installModuleTables('business', 'finance', $path . 'finance/install/install.sql');
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

		// A book the user can't see
		$this->book = new ProjectBook();
		$this->book->name = $name;
		$this->book->businessId = $this->business->id;
		$this->book->setAcl([Group::ID_INTERNAL => null, Group::ID_EVERYONE => null]);
		$this->assertTrue($this->book->save(), $this->book->getValidationErrorsAsString());

		$this->project = new Project3Model();
		$this->project->name = $name;
		$this->project->bookId = $this->book->id;
		$this->assertTrue($this->project->save(), $this->project->getValidationErrorsAsString());
	}

	protected function tearDown(): void
	{
		$this->actAs(User::ID_SUPER_ADMIN);
		if ($this->project) {
			Project3Model::delete(['id' => $this->project->id]);
		}
		if ($this->book) {
			ProjectBook::delete(['id' => $this->book->id]);
		}
		if ($this->business) {
			// Business::delete() needs the finance tables
			go()->getDbConnection()->delete('business_business', ['id' => $this->business->id])->execute();
		}
		User::delete(['id' => $this->user->id]);
		if (in_array('business_projects3_project3', self::$createdModuleTables)) {
			// install.sql adds these to the tasks table, which isn't dropped
			foreach (['tasks_task_business_projects3_project3_id_fk', 'tasks_task_business_projects3_project3_milestone_id_fk'] as $fk) {
				if (go()->getDbConnection()->query("SELECT 1 FROM information_schema.TABLE_CONSTRAINTS WHERE CONSTRAINT_SCHEMA = DATABASE() AND TABLE_NAME = 'tasks_task' AND CONSTRAINT_NAME = '" . $fk . "'")->fetch()) {
					go()->getDbConnection()->exec('ALTER TABLE `tasks_task` DROP FOREIGN KEY `' . $fk . '`');
				}
			}
		}
		$this->uninstallModuleTables();
		go()->setAuthState($this->authState);
	}

	private function actAs(int $userId): void
	{
		go()->setAuthState(new TemporaryState($userId));
	}

	/**
	 * Without a license the module is only available to administrators, so the constructor is skipped as if the user may
	 * use the module.
	 */
	private function controller(): Project3
	{
		return (new \ReflectionClass(Project3::class))->newInstanceWithoutConstructor();
	}

	public static function methods(): array
	{
		return [['resources'], ['getTaskProgress']];
	}

	#[DataProvider('methods')]
	public function testUnreadableProject(string $method): void
	{
		$this->actAs($this->user->id);
		$this->expectException(Forbidden::class);
		$this->controller()->$method(['projectId' => $this->project->id]);
	}

	public function testReadableProject(): void
	{
		$this->actAs(User::ID_SUPER_ADMIN);
		$book = ProjectBook::findById($this->book->id);
		$book->setAcl([$this->user->getPersonalGroup()->id => Acl::LEVEL_READ]);
		$this->assertTrue($book->save(), $book->getValidationErrorsAsString());
		(new \ReflectionProperty(\go\core\acl\model\AclItemEntity::class, 'aclEntityCache'))->setValue(null, []);

		$this->actAs($this->user->id);
		$this->assertEquals([], iterator_to_array($this->controller()->getTaskProgress(['projectId' => $this->project->id])));
	}

	public function testCreateInvoiceInUnknownBook(): void
	{
		$this->expectException(Forbidden::class);
		$this->controller()->createInvoice(['projectId' => $this->project->id, 'bookId' => 999999999, 'daterange' => null]);
	}
}
