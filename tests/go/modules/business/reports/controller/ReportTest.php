<?php
namespace go\modules\business\reports\controller;

use go\core\auth\TemporaryState;
use go\core\model\EntityFilter;
use go\core\model\Group;
use go\core\model\User;
use go\modules\business\ModuleTablesTrait;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../fixtures/ModuleTablesTrait.php';

/**
 * The report builder lists the saved filters of an entity type. Private filters of other users are left out.
 */
class ReportTest extends TestCase
{
	use ModuleTablesTrait;

	private $authState;
	private User $user;
	private ?EntityFilter $filter = null;

	protected function setUp(): void
	{
		$this->authState = go()->getAuthState();
		go()->setAuthState(new TemporaryState(User::ID_SUPER_ADMIN));

		$path = go()->getEnvironment()->getInstallPath() . '/go/modules/business/';
		$this->installModuleTables('business', 'reports', $path . 'reports/install/install.sql');

		$name = uniqid('reportuser');
		$this->user = new User();
		$this->user->setValues([
			'username' => $name,
			'displayName' => $name,
			'email' => $name . '@example.com',
			'password' => 'Pa55word!!xyz'
		]);
		$this->assertTrue($this->user->save(), $this->user->getValidationErrorsAsString());

		$this->filter = new EntityFilter();
		$this->filter->name = uniqid('private filter');
		$this->filter->setEntity('Contact');
		$this->filter->setFilter(['text' => 'secret']);
		$this->filter->setAcl([Group::ID_INTERNAL => null, Group::ID_EVERYONE => null]);
		$this->assertTrue($this->filter->save(), $this->filter->getValidationErrorsAsString());
	}

	protected function tearDown(): void
	{
		go()->setAuthState(new TemporaryState(User::ID_SUPER_ADMIN));
		if (isset($this->filter->id)) {
			EntityFilter::delete(['id' => $this->filter->id]);
		}
		User::delete(['id' => $this->user->id]);
		$this->uninstallModuleTables();
		go()->setAuthState($this->authState);
	}

	private function filterIds(int $userId): array
	{
		go()->setAuthState(new TemporaryState($userId));
		$controller = (new \ReflectionClass(Report::class))->newInstanceWithoutConstructor();
		$response = $controller->defaultEntityFilters(['entityType' => 'Contact']);
		return array_map(fn($f) => (string) $f->id, $response['data']['entityFilters']);
	}

	public function testPrivateFiltersOfOthersAreLeftOut(): void
	{
		$this->assertNotContains((string) $this->filter->id, $this->filterIds($this->user->id));
	}

	public function testOwnerSeesTheFilter(): void
	{
		$this->assertContains((string) $this->filter->id, $this->filterIds(User::ID_SUPER_ADMIN));
	}
}
