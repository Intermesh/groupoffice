<?php
namespace go\modules\business\newsletters\controller;

use go\core\auth\TemporaryState;
use go\core\exception\Forbidden;
use go\core\model\Acl;
use go\core\model\Group;
use go\core\model\SmtpAccount;
use go\core\model\User;
use go\modules\business\ModuleTablesTrait;
use go\modules\business\newsletters\model\AddressList;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../fixtures/ModuleTablesTrait.php';

/**
 * A test message is sent with the chosen SMTP account and filled with the first recipient of the address list. The
 * user must be allowed to use both.
 */
class NewsletterTest extends TestCase
{
	use ModuleTablesTrait;

	private $authState;
	private User $user;
	private ?AddressList $addressList = null;
	private ?SmtpAccount $smtpAccount = null;

	public static function setUpBeforeClass(): void
	{
		$path = go()->getEnvironment()->getInstallPath() . '/go/modules/business/';
		try {
			self::installModuleTables('business', 'newsletters', $path . 'newsletters/install/install.sql');
		} catch (\Throwable $e) {
			// PHPUnit doesn't call tearDownAfterClass() when this fails, and the module tables would stay behind
			self::uninstallModuleTables();
			throw $e;
		}
	}

	public static function tearDownAfterClass(): void
	{
		self::uninstallModuleTables();
	}

	protected function setUp(): void
	{
		$this->authState = go()->getAuthState();
		$this->actAs(User::ID_SUPER_ADMIN);

		$name = uniqid('nluser');
		$this->user = new User();
		$this->user->setValues([
			'username' => $name,
			'displayName' => $name,
			'email' => $name . '@example.com',
			'password' => 'Pa55word!!xyz'
		]);
		$this->assertTrue($this->user->save(), $this->user->getValidationErrorsAsString());

		$this->addressList = new AddressList();
		$this->addressList->name = $name;
		$this->addressList->setEntity('Contact');
		$this->addressList->setAcl([Group::ID_INTERNAL => null, Group::ID_EVERYONE => null]);
		$this->assertTrue($this->addressList->save(), $this->addressList->getValidationErrorsAsString());

		$this->smtpAccount = new SmtpAccount();
		$this->smtpAccount->setModule(['package' => 'business', 'name' => 'newsletters']);
		$this->smtpAccount->hostname = 'localhost';
		$this->smtpAccount->fromName = $name;
		$this->smtpAccount->fromEmail = $name . '@example.com';
		$this->smtpAccount->setAcl([Group::ID_INTERNAL => null, Group::ID_EVERYONE => null]);
		$this->assertTrue($this->smtpAccount->save(), $this->smtpAccount->getValidationErrorsAsString());
	}

	protected function tearDown(): void
	{
		$this->actAs(User::ID_SUPER_ADMIN);
		if (isset($this->addressList->id)) {
			AddressList::delete(['id' => $this->addressList->id]);
		}
		if (isset($this->smtpAccount->id)) {
			SmtpAccount::delete(['id' => $this->smtpAccount->id]);
		}
		User::delete(['id' => $this->user->id]);

		go()->setAuthState($this->authState);
	}

	/**
	 * Without a license the module is only available to administrators, so the constructor is skipped as if the user may
	 * use the module.
	 */
	private function controller(): Newsletter
	{
		return (new \ReflectionClass(Newsletter::class))->newInstanceWithoutConstructor();
	}

	private function actAs(int $userId): void
	{
		go()->setAuthState(new TemporaryState($userId));
	}

	private function grant($entity, int $level): void
	{
		$this->actAs(User::ID_SUPER_ADMIN);
		$entity = $entity::findById($entity->id);
		$entity->setAcl([$this->user->getPersonalGroup()->id => $level]);
		$this->assertTrue($entity->save(), $entity->getValidationErrorsAsString());
	}

	private function test(): void
	{
		$this->actAs($this->user->id);
		$this->controller()->test([
			'addressListId' => $this->addressList->id,
			'smtpAccountId' => $this->smtpAccount->id,
			'subject' => 'Test',
			'body' => 'Test'
		]);
	}

	public function testSmtpAccountWithoutPermission(): void
	{
		$this->grant($this->addressList, Acl::LEVEL_READ);

		$this->expectException(Forbidden::class);
		$this->test();
	}

	public function testAddressListWithoutPermission(): void
	{
		$this->grant($this->smtpAccount, Acl::LEVEL_READ);

		$this->expectException(Forbidden::class);
		$this->test();
	}

	public function testWithPermission(): void
	{
		$this->grant($this->addressList, Acl::LEVEL_READ);
		$this->grant($this->smtpAccount, Acl::LEVEL_READ);

		// It continues after the permission checks and stops at the empty list
		$this->expectExceptionMessage(go()->t("Please add at least one recipient to the list to send a test message.", 'business', 'newsletters'));
		$this->test();
	}

	private function saveNewsletter(): \go\modules\business\newsletters\model\Newsletter
	{
		$this->actAs($this->user->id);
		$newsletter = new \go\modules\business\newsletters\model\Newsletter();
		$newsletter->setValues([
			'addressListId' => $this->addressList->id,
			'smtpAccountId' => $this->smtpAccount->id,
			'subject' => 'Test',
			'body' => 'Test'
		]);
		$newsletter->save();
		return $newsletter;
	}

	public function testSaveWithSmtpAccountWithoutPermission(): void
	{
		$newsletter = $this->saveNewsletter();
		$this->assertArrayHasKey('smtpAccountId', $newsletter->getValidationErrors());
	}

	public function testSaveWithSmtpAccountWithPermission(): void
	{
		$this->grant($this->smtpAccount, Acl::LEVEL_READ);
		$newsletter = $this->saveNewsletter();
		$this->assertArrayNotHasKey('smtpAccountId', $newsletter->getValidationErrors());
		if (isset($newsletter->id)) {
			$this->actAs(User::ID_SUPER_ADMIN);
			\go\modules\business\newsletters\model\Newsletter::delete(['id' => $newsletter->id]);
		}
	}
}
