<?php
namespace go\modules\legacy\announcements;

use GO\Announcements\Controller\AnnouncementController;
use GO\Announcements\Model\Announcement;
use GO\Announcements\Model\Target;
use GO\Base\Exception\AccessDenied;
use go\core\auth\TemporaryState;
use go\core\model\User;
use PHPUnit\Framework\TestCase;

/**
 * The users action lists the names and e-mail addresses of the users of an announcement and adds or removes users. It
 * is for announcement managers only, and it may not leave the ACL ignored.
 */
class AnnouncementControllerTest extends TestCase
{
	private $authState;
	private bool $ignoreAcl;
	private bool $installed = false;
	private array $tables = [];
	private User $user;
	private Announcement $announcement;

	protected function setUp(): void
	{
		$this->authState = go()->getAuthState();
		$this->ignoreAcl = \GO::$ignoreAclPermissions;
		$this->actAs(User::ID_SUPER_ADMIN);

		if (!\GO::modules()->isInstalled('announcements')) {
			$tables = $this->findTables();
			\GO::$ignoreAclPermissions = true;
			try {
				\GO\Base\Model\Module::install('announcements');
			} finally {
				\GO::$ignoreAclPermissions = $this->ignoreAcl;
				$this->tables = array_diff($this->findTables(), $tables);
			}
			$this->installed = true;
		}

		$name = uniqid('anuser');
		$this->user = new User();
		$this->user->setValues([
			'username' => $name,
			'displayName' => $name,
			'email' => $name . '@example.com',
			'password' => 'Pa55word!!xyz'
		]);
		$this->assertTrue($this->user->save(), $this->user->getValidationErrorsAsString());

		$this->announcement = new Announcement();
		$this->announcement->name = uniqid('announcement');
		$this->announcement->text = 'test';
		$this->assertTrue($this->announcement->save(true), var_export($this->announcement->getValidationErrors(), true));

		$target = new Target();
		$target->announcement_id = $this->announcement->id;
		$target->user_id = User::ID_SUPER_ADMIN;
		$this->assertTrue($target->save(true));
	}

	protected function tearDown(): void
	{
		$this->actAs(User::ID_SUPER_ADMIN);
		\GO::$ignoreAclPermissions = true;
		try {
			foreach (Target::model()->findByAttribute('announcement_id', $this->announcement->id) as $target) {
				$target->delete(true);
			}
			$this->announcement->delete(true);
			User::delete(['id' => $this->user->id]);

			if ($this->installed) {
				\GO\Base\Model\Module::model()->findByName('announcements')->delete();
				$oldFKchecks = go()->getDbConnection()->foreignKeyChecks(false);
				foreach ($this->tables as $table) {
					go()->getDbConnection()->exec('DROP TABLE IF EXISTS `' . $table . '`');
				}
				if ($oldFKchecks) {
					go()->getDbConnection()->foreignKeyChecks(true);
				}
			}
		} finally {
			\GO::$ignoreAclPermissions = $this->ignoreAcl;
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

	private function users(array $params): string
	{
		$method = new \ReflectionMethod(AnnouncementController::class, 'actionUsers');
		ob_start();
		try {
			$method->invoke(new AnnouncementController(), $params);
			return ob_get_contents();
		} finally {
			ob_end_clean();
		}
	}

	public function testUsersAreOnlyForManagers(): void
	{
		$this->actAs($this->user->id);
		try {
			$output = $this->users(['announcement_id' => $this->announcement->id, 'add_users' => json_encode([$this->user->id])]);
			$this->fail('A user listed the users of an announcement: ' . $output);
		} catch (AccessDenied $e) {
			$this->assertTrue(true);
		} finally {
			$this->actAs(User::ID_SUPER_ADMIN);
		}

		$this->assertSame($this->ignoreAcl, \GO::$ignoreAclPermissions);
		$this->assertFalse(Target::model()->findByPk(['announcement_id' => $this->announcement->id, 'user_id' => $this->user->id], false, true), 'A user added themselves to an announcement');
	}

	public function testManagerListsUsersAndTheAclIsNotIgnoredAfterwards(): void
	{
		$output = $this->users(['announcement_id' => $this->announcement->id]);

		$this->assertStringContainsString('"results"', $output);
		$this->assertSame($this->ignoreAcl, \GO::$ignoreAclPermissions, 'The ACL is still ignored after the action');
	}
}
