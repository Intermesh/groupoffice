<?php

namespace go\modules\legacy\savemailas;

use GO\Base\Exception\AccessDenied;
use GO\Savemailas\Controller\LinkedEmailController;
use go\core\auth\TemporaryState;
use go\core\model\Acl;
use go\core\model\User;
use go\modules\community\addressbook\model\AddressBook;
use go\modules\community\addressbook\model\Contact;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * E-mail may only be linked to a contact or company the user may read
 */
class LinkedEmailControllerTest extends TestCase
{
	private $authState;
	private User $user;
	private AddressBook $addressBook;
	private Contact $contact;

	protected function setUp(): void
	{
		$this->authState = go()->getAuthState();

		if(!class_exists(LinkedEmailController::class) || !go()->getModule('community', 'addressbook')) {
			$this->markTestSkipped('The savemailas and addressbook modules are required');
		}

		$name = uniqid('linkedemail');
		$this->user = new User();
		$this->user->setValues([
			'username' => $name,
			'displayName' => $name,
			'email' => $name . '@example.com',
			'password' => 'Pa55word!!xyz'
		]);
		$this->assertTrue($this->user->save(), var_export($this->user->getValidationErrors(), true));

		go()->setAuthState(new TemporaryState(User::ID_SUPER_ADMIN));
		$this->addressBook = new AddressBook();
		$this->addressBook->name = uniqid('private');
		$this->addressBook->setAcl([\go\core\model\Group::ID_ADMINS => Acl::LEVEL_MANAGE]);
		$this->assertTrue($this->addressBook->save());

		$this->contact = new Contact();
		$this->contact->addressBookId = $this->addressBook->id;
		$this->contact->firstName = 'Private';
		$this->assertTrue($this->contact->save());
	}

	protected function tearDown(): void
	{
		go()->setAuthState(new TemporaryState(User::ID_SUPER_ADMIN));
		if(isset($this->addressBook)) {
			AddressBook::delete(['id' => $this->addressBook->id]);
		}
		if(isset($this->user)) {
			User::delete(['id' => $this->user->id]);
		}
		if($this->authState) {
			go()->setAuthState($this->authState);
		}
	}

	public static function actions(): array
	{
		return [['actionLinkContact', 'contact_id'], ['actionLinkCompany', 'company_id']];
	}

	#[DataProvider('actions')]
	public function testUnreadableContact(string $action, string $param): void
	{
		go()->setAuthState(new TemporaryState($this->user->id));
		\GO::session()->setCurrentUser($this->user->id);

		$this->expectException(AccessDenied::class);
		(new \ReflectionMethod(LinkedEmailController::class, $action))->invoke(new LinkedEmailController(), [
			$param => $this->contact->id,
			'account_id' => 0,
			'mailbox' => 'INBOX',
			'uid' => 1
		]);
	}
}
