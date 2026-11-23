<?php

namespace go\modules\legacy\email;

use go\core\auth\TemporaryState;
use go\core\model\Acl;
use go\core\model\User;
use GO\Email\Controller\MessageController;
use go\modules\community\addressbook\model\AddressBook;
use go\modules\community\addressbook\model\Contact;
use PHPUnit\Framework\TestCase;

/**
 * An e-mail template is filled with the data of the contact given by "contact_id". The user must be able to read that
 * contact.
 */
class TemplateContactPermissionTest extends TestCase
{
	const MIME = "Subject: Template\r\n" .
		"MIME-Version: 1.0\r\n" .
		"Content-Type: text/html; charset=utf-8\r\n" .
		"\r\n" .
		"<p>Dear {contact:firstName} {contact:lastName}</p>\r\n";

	private $authState;
	private bool $disableModelCache;
	private User $user;
	private AddressBook $addressBook;
	private Contact $contact;
	private \GO\Base\Model\Template $template;

	protected function setUp(): void
	{
		if(!\GO::modules()->isInstalled('email') || !go()->getModule('community', 'addressbook')) {
			$this->markTestSkipped('The email and addressbook modules are required');
		}

		$this->authState = go()->getAuthState();
		$this->disableModelCache = \GO::$disableModelCache;
		\GO::$disableModelCache = true;

		$name = uniqid('tplcontact');
		$this->user = new User();
		$this->user->setValues([
			'username' => $name,
			'displayName' => $name,
			'email' => $name . '@example.com',
			'password' => 'Pa55word!!xyz'
		]);
		$this->assertTrue($this->user->save(), var_export($this->user->getValidationErrors(), true));

		$this->actAs(User::ID_SUPER_ADMIN);
		$this->addressBook = new AddressBook();
		$this->addressBook->name = uniqid('private');
		$this->addressBook->setAcl([\go\core\model\Group::ID_ADMINS => Acl::LEVEL_MANAGE]);
		$this->assertTrue($this->addressBook->save());

		$this->contact = new Contact();
		$this->contact->addressBookId = $this->addressBook->id;
		$this->contact->firstName = 'Secretfirst';
		$this->contact->lastName = 'Secretlast';
		$this->assertTrue($this->contact->save());

		// as admin, because only admins may create templates in the base module
		$this->template = new \GO\Base\Model\Template();
		$this->template->setAttributes([
			'user_id' => $this->user->id,
			'name' => 'Contact template',
			'type' => 0,
			'content' => self::MIME
		], false);
		$this->assertTrue($this->template->save(true), var_export($this->template->getValidationErrors(), true));
	}

	protected function tearDown(): void
	{
		$this->actAs(User::ID_SUPER_ADMIN);
		if(isset($this->template)) {
			$this->template->delete(true);
		}
		if(isset($this->addressBook)) {
			AddressBook::delete(['id' => $this->addressBook->id]);
		}
		User::delete(['id' => $this->user->id]);
		go()->setAuthState($this->authState);
		\GO::$disableModelCache = $this->disableModelCache;
	}

	private function actAs(int $userId): void
	{
		go()->setAuthState(new TemporaryState($userId));
		\GO::session()->setCurrentUser($userId);
	}

	private function loadTemplate(): string
	{
		$response = (new MessageController())->loadTemplate([
			'template_id' => $this->template->id,
			'contact_id' => $this->contact->id,
			'content_type' => 'html'
		]);
		return $response['data']['htmlbody'];
	}

	public function testTemplateIsNotFilledWithUnreadableContact(): void
	{
		$this->actAs($this->user->id);
		$this->assertFalse(Contact::findById($this->contact->id)->hasPermissionLevel(Acl::LEVEL_READ));

		$body = $this->loadTemplate();

		$this->assertStringNotContainsString('Secretfirst', $body);
		$this->assertStringNotContainsString('Secretlast', $body);
	}

	public function testTemplateIsFilledWithReadableContact(): void
	{
		$this->actAs(User::ID_SUPER_ADMIN);
		$this->addressBook->setAcl([$this->user->getPersonalGroup()->id => Acl::LEVEL_READ]);
		$this->assertTrue($this->addressBook->save());

		$this->actAs($this->user->id);
		$this->assertStringContainsString('Secretfirst Secretlast', $this->loadTemplate());
	}
}
