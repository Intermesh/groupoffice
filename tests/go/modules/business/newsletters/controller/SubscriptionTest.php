<?php
namespace go\modules\business\newsletters\controller;

use go\core\auth\TemporaryState;
use go\core\exception\Forbidden;
use go\core\model\User;
use go\core\util\Crypt;
use go\modules\business\ModuleTablesTrait;
use go\modules\business\newsletters\model\AddressList;
use go\modules\community\addressbook\model\Contact;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../fixtures/ModuleTablesTrait.php';

/**
 * Anyone may subscribe with the token of an invitation e-mail. The token must be one the server encrypted, otherwise
 * anyone could add any contact to any list.
 */
class SubscriptionTest extends TestCase
{
	use ModuleTablesTrait;

	private $authState;
	private ?AddressList $addressList = null;
	private ?Contact $contact = null;

	protected function setUp(): void
	{
		$this->authState = go()->getAuthState();
		go()->setAuthState(new TemporaryState(User::ID_SUPER_ADMIN));

		$path = go()->getEnvironment()->getInstallPath() . '/go/modules/business/';
		$this->installModuleTables('business', 'newsletters', $path . 'newsletters/install/install.sql');

		$this->addressList = new AddressList();
		$this->addressList->name = uniqid('subscribe');
		$this->addressList->setEntity('Contact');
		$this->assertTrue($this->addressList->save(), $this->addressList->getValidationErrorsAsString());

		$this->contact = new Contact();
		$this->contact->firstName = uniqid('subscriber');
		$this->contact->addressBookId = go()->getAuthState()->getUser(['addressBookSettings'])->addressBookSettings->getDefaultAddressBookId();
		$this->assertTrue($this->contact->save(), $this->contact->getValidationErrorsAsString());
	}

	protected function tearDown(): void
	{
		go()->setAuthState(new TemporaryState(User::ID_SUPER_ADMIN));
		if (isset($this->addressList->id)) {
			AddressList::delete(['id' => $this->addressList->id]);
		}
		if (isset($this->contact->id)) {
			Contact::delete(['id' => $this->contact->id]);
		}
		$this->uninstallModuleTables();
		go()->setAuthState($this->authState);
	}

	private function subscribe(string $token): array
	{
		// Not logged in
		go()->setAuthState(new \go\core\jmap\State());
		$controller = (new \ReflectionClass(Subscription::class))->newInstanceWithoutConstructor();
		return $controller->subscribe(['token' => $token]);
	}

	private function payload(): string
	{
		return json_encode(['addressListId' => $this->addressList->id, 'contactId' => $this->contact->id]);
	}

	public function testUnencryptedTokenIsRefused(): void
	{
		$this->expectException(Forbidden::class);
		$this->subscribe($this->payload());
	}

	public function testInvitationToken(): void
	{
		$list = $this->subscribe(Crypt::encrypt($this->payload()));
		$this->assertEquals($this->addressList->id, $list['id']);
	}
}
