<?php

namespace go\modules\legacy\savemailas;

use go\core\auth\TemporaryState;
use go\core\model\Acl;
use go\core\model\User;
use go\modules\community\addressbook\model\AddressBook;
use go\modules\community\addressbook\model\Contact;
use PHPUnit\Framework\TestCase;

/**
 * E-mail may only be linked to items the user may read. This is also used for the link tags in the body of incoming
 * e-mail, which a stranger can write.
 */
class LinkModelPermissionTest extends TestCase
{
	private $authState;
	private User $user;
	private AddressBook $addressBook;
	private Contact $contact;

	protected function setUp(): void
	{
		$this->authState = go()->getAuthState();

		// getLinkModel() doesn't need the module to be installed
		if(!class_exists(\GO\Savemailas\SavemailasModule::class) || !go()->getModule('community', 'addressbook')) {
			$this->markTestSkipped('The savemailas and addressbook modules are required');
		}

		$name = uniqid('linkmodel');
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

	private function getLinkModel()
	{
		go()->setAuthState(new TemporaryState($this->user->id));
		\GO::session()->setCurrentUser($this->user->id);
		// the old class name is accepted too
		return \GO\Savemailas\SavemailasModule::getLinkModel('GO\\Addressbook\\Model\\Contact', $this->contact->id);
	}

	public function testUnreadableItemCannotBeLinked(): void
	{
		$this->assertFalse($this->getLinkModel());
	}

	public function testReadableItemCanBeLinked(): void
	{
		$this->addressBook->setAcl([$this->user->getPersonalGroup()->id => Acl::LEVEL_READ]);
		$this->assertTrue($this->addressBook->save());

		$model = $this->getLinkModel();
		$this->assertInstanceOf(Contact::class, $model);
		$this->assertSame($this->contact->id, $model->id);
	}
}
