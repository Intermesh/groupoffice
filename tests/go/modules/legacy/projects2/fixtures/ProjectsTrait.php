<?php
namespace go\modules\legacy\projects2;

use GO\Projects2\Model\Project;
use GO\Projects2\Model\Status;
use GO\Projects2\Model\Template;
use GO\Projects2\Model\Type;
use go\core\auth\TemporaryState;
use go\core\model\User;

/**
 * A user and projects of types with a given permission level for that user. The projects module is installed when
 * needed and uninstalled afterwards.
 */
trait ProjectsTrait
{
	private $authState;
	private bool $ignoreAcl;
	private bool $disableModelCache;
	private bool $installedProjects = false;
	/** @var string[] Tables created for the projects module */
	private array $projectTables = [];
	private ?Template $template = null;
	private ?Status $status = null;
	private User $user;
	/** @var Type[] */
	private array $types = [];
	/** @var Project[] */
	private array $projects = [];

	protected function setUpProjects(): void
	{
		$this->authState = go()->getAuthState();
		$this->ignoreAcl = \GO::$ignoreAclPermissions;
		$this->disableModelCache = \GO::$disableModelCache;
		// Models remember the permission level of the user they were first used by. The users change in these tests.
		\GO::$disableModelCache = true;

		$this->actAs(User::ID_SUPER_ADMIN);

		if (!\GO\Base\Model\Module::model()->findByName('projects2')) {
			$this->installProjects();
		}

		$name = uniqid('pr2user');
		$this->user = new User();
		$this->user->setValues([
			'username' => $name,
			'displayName' => $name,
			'email' => $name . '@example.com',
			'password' => 'Pa55word!!xyz'
		]);
		$this->assertTrue($this->user->save(), $this->user->getValidationErrorsAsString());
	}

	protected function tearDownProjects(): void
	{
		$this->actAs(User::ID_SUPER_ADMIN);
		\GO::$ignoreAclPermissions = true;

		foreach (array_reverse($this->projects) as $project) {
			$project->delete(true);
		}
		foreach ($this->types as $type) {
			$type->delete(true);
		}
		if (isset($this->user)) {
			User::delete(['id' => $this->user->id]);
		}

		if ($this->installedProjects) {
			$this->uninstallProjects();
		}

		\GO::$ignoreAclPermissions = $this->ignoreAcl;
		\GO::$disableModelCache = $this->disableModelCache;
		go()->setAuthState($this->authState);
	}

	/**
	 * The projects module needs a license, which the tests don't have. Create its tables and the module record without
	 * the license check.
	 */
	private function installProjects(): void
	{
		$path = \GO::config()->root_path . 'promodules/projects2/install/';
		if (!is_file($path . 'install.sql')) {
			$this->markTestSkipped('The projects2 module is not available');
		}

		$tables = $this->findTables();
		go()->getDbConnection()->insert('core_module', ['name' => 'projects2', 'version' => 0, 'enabled' => 1])->execute();
		$this->installedProjects = true;
		try {
			foreach (\GO\Base\Util\SQL::getSqlQueries($path . 'install.sql') as $query) {
				go()->getDbConnection()->exec($query);
			}
		} finally {
			$this->projectTables = array_diff($this->findTables(), $tables);
		}

		$type = new Type();
		$type->name = 'Default';
		$this->assertTrue($type->save(true));
		$this->types[] = $type;

		$status = new Status();
		$status->name = 'Ongoing';
		$this->assertTrue($status->save(true));
		$this->status = $status;

		$template = new Template();
		$template->name = 'Standard project';
		$template->name_template = '';
		$template->default_status_id = $status->id;
		$template->default_type_id = $type->id;
		$template->project_type = Template::PROJECT_TYPE_PROJECT;
		$this->assertTrue($template->save(true), var_export($template->getValidationErrors(), true));
		$this->template = $template;
	}

	private function findTables(): array
	{
		return go()->getDbConnection()->query('SHOW TABLES')->fetchAll(\PDO::FETCH_COLUMN);
	}

	private function uninstallProjects(): void
	{
		if ($this->template) {
			$this->template->delete(true);
		}
		if ($this->status) {
			$this->status->delete(true);
		}
		$oldFKchecks = go()->getDbConnection()->foreignKeyChecks(false);
		try {
			foreach ($this->projectTables as $table) {
				go()->getDbConnection()->exec('DROP TABLE `' . $table . '`');
			}
		} finally {
			if ($oldFKchecks) {
				go()->getDbConnection()->foreignKeyChecks(true);
			}
		}
		// Deleting legacy models doesn't delete their ACL
		go()->getDbConnection()->exec("DELETE a FROM core_acl a INNER JOIN core_entity e ON e.id = a.entityTypeId INNER JOIN core_module m ON m.id = e.moduleId WHERE m.name = 'projects2' AND m.package IS NULL");
		go()->getDbConnection()->delete('core_module', ['name' => 'projects2', 'package' => null])->execute();
		go()->getCache()->delete('entity-types');
		\go\core\orm\Entity::clearCache();
	}

	private function actAs(int $userId): void
	{
		go()->setAuthState(new TemporaryState($userId));
		\GO::session()->setCurrentUser($userId);
	}

	/**
	 * A project type that gives the user the given permission level on its projects
	 */
	private function createType(int $level): Type
	{
		$type = new Type();
		$type->name = uniqid('type');
		$this->assertTrue($type->save(), var_export($type->getValidationErrors(), true));
		$type->acl->addGroup($this->user->getPersonalGroup()->id, $level);
		$this->types[] = $type;
		return $type;
	}

	private function createProject(Type $type, ?Project $parent = null): Project
	{
		$template = Template::model()->findSingleByAttribute('project_type', Template::PROJECT_TYPE_PROJECT);
		$project = new Project();
		$project->name = uniqid('project');
		$project->type_id = $type->id;
		$project->template_id = $template->id;
		$project->status_id = $template->default_status_id;
		$project->responsible_user_id = User::ID_SUPER_ADMIN;
		if ($parent) {
			$project->parent_project_id = $parent->id;
		}
		$this->assertTrue($project->save(true), var_export($project->getValidationErrors(), true));
		$this->projects[] = $project;
		return $project;
	}
}
