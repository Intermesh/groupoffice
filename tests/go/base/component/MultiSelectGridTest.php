<?php

namespace go\base\component;

use GO\Base\Component\MultiSelectGrid;
use GO\Base\Data\AbstractStore;
use GO\Email\Model\Account;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../modules/legacy/email/fixtures/SharedAccountTrait.php';

/**
 * The ids selected in a MultiSelectGrid come from the request and are used to find items without checking their ACL
 * (for example orders of the selected books). Only ids of models the user may read may be used.
 */
class MultiSelectGridTest extends TestCase
{
	use \go\modules\legacy\email\SharedAccountTrait;

	const PARAM = 'ms-phpunit-accounts';

	protected function setUp(): void
	{
		$this->setUpSharedAccount(\go\core\model\Acl::LEVEL_READ);
	}

	protected function tearDown(): void
	{
		\GO::config()->delete_setting('ms_' . self::PARAM, $this->other->id);
		$this->tearDownSharedAccount();
	}

	private function createGrid(array $requestParams): MultiSelectGrid
	{
		$store = $this->createStub(AbstractStore::class);
		return new MultiSelectGrid(self::PARAM, Account::class, $store, $requestParams, true);
	}

	public function testReadableIdIsSelected(): void
	{
		$this->actAs($this->other->id);
		$grid = $this->createGrid([self::PARAM => json_encode([$this->account->id])]);

		$this->assertSame([(int) $this->account->id], array_map('intval', $grid->selectedIds));
	}

	public function testUnreadableIdIsNotSelected(): void
	{
		$this->removeAccess();

		$grid = $this->createGrid([self::PARAM => json_encode([$this->account->id])]);

		$this->assertNotContains((int) $this->account->id, array_map('intval', $grid->selectedIds));
	}

	public function testStoredIdIsCheckedAgain(): void
	{
		$this->actAs($this->other->id);
		$this->createGrid([self::PARAM => json_encode([$this->account->id])]);

		// access is removed after the selection was stored
		$this->removeAccess();

		$grid = $this->createGrid([]);
		$this->assertNotContains((int) $this->account->id, array_map('intval', $grid->selectedIds));
	}

	private function removeAccess(): void
	{
		$this->actAs(\go\core\model\User::ID_SUPER_ADMIN);
		$acl = \go\core\model\Acl::findById($this->account->acl_id);
		$acl->removeGroup($this->other->getPersonalGroup()->id);
		$this->assertTrue($acl->save());
		$this->actAs($this->other->id);
		$this->assertFalse(Account::model()->findByPk($this->account->id, false, true)->checkPermissionLevel(\go\core\model\Acl::LEVEL_READ));
	}
}
