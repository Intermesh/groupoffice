<?php
namespace go\modules\legacy\tickets\controller;

use GO\Base\Exception\AccessDenied;
use GO\Tickets\Controller\TicketController;
use GO\Tickets\Model\Ticket;
use go\core\auth\TemporaryState;
use go\core\model\Acl;
use go\core\model\User;
use go\modules\business\ModuleTablesTrait;
use go\modules\community\addressbook\model\AddressBook;
use go\modules\community\addressbook\model\Contact;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../../business/fixtures/ModuleTablesTrait.php';

/**
 * A new ticket can be filled with the details of a contact and company. They are returned to the client, so the user
 * must be able to read them.
 */
class TicketContactTest extends TestCase
{
	use ModuleTablesTrait;

	private $authState;
	private User $user;
	private AddressBook $privateBook;
	private AddressBook $sharedBook;
	private Contact $privateContact;
	private Contact $sharedContact;

	protected function setUp(): void
	{
		if (!class_exists(TicketController::class) || !go()->getModule('community', 'addressbook')) {
			$this->markTestSkipped('The tickets and addressbook modules are required');
		}

		$this->authState = go()->getAuthState();
		$this->createTables(go()->getEnvironment()->getInstallPath() . '/promodules/tickets/install/install.sql', ['ti_tickets', 'ti_types']);

		$this->actAs(User::ID_SUPER_ADMIN);

		$name = uniqid('ticketuser');
		$this->user = new User();
		$this->user->setValues([
			'username' => $name,
			'displayName' => $name,
			'email' => $name . '@example.com',
			'password' => 'Pa55word!!xyz'
		]);
		$this->assertTrue($this->user->save(), $this->user->getValidationErrorsAsString());

		$this->privateBook = new AddressBook();
		$this->privateBook->name = uniqid('private');
		$this->privateBook->setAcl([\go\core\model\Group::ID_ADMINS => Acl::LEVEL_MANAGE]);
		$this->assertTrue($this->privateBook->save());

		$this->sharedBook = new AddressBook();
		$this->sharedBook->name = uniqid('shared');
		$this->sharedBook->setAcl([\go\core\model\Group::ID_ADMINS => Acl::LEVEL_MANAGE, $this->user->getPersonalGroup()->id => Acl::LEVEL_READ]);
		$this->assertTrue($this->sharedBook->save());

		$this->privateContact = $this->createContact($this->privateBook);
		$this->sharedContact = $this->createContact($this->sharedBook);
	}

	protected function tearDown(): void
	{
		if (!isset($this->authState)) {
			return;
		}
		$this->actAs(User::ID_SUPER_ADMIN);
		foreach (['privateBook', 'sharedBook'] as $book) {
			if (isset($this->$book)) {
				AddressBook::delete(['id' => $this->$book->id]);
			}
		}
		if (isset($this->user)) {
			User::delete(['id' => $this->user->id]);
		}
		$this->uninstallModuleTables();
		go()->setAuthState($this->authState);
	}

	private function actAs(int $userId): void
	{
		go()->setAuthState(new TemporaryState($userId));
		\GO::session()->setCurrentUser($userId);
	}

	private function createContact(AddressBook $book): Contact
	{
		$contact = new Contact();
		$contact->addressBookId = $book->id;
		$contact->firstName = uniqid('contact');
		$this->assertTrue($contact->save(), $contact->getValidationErrorsAsString());
		return $contact;
	}

	/**
	 * Run beforeLoad() of the ticket dialog for a new ticket
	 */
	private function load(array $params): Ticket
	{
		$this->actAs($this->user->id);
		$response = [];
		$model = new Ticket();
		$method = new \ReflectionMethod(TicketController::class, 'beforeLoad');
		$method->invokeArgs(new TicketController(), [&$response, &$model, &$params]);
		return $model;
	}

	public function testUnreadableContact(): void
	{
		$this->expectException(AccessDenied::class);
		$this->load(['contact_id' => $this->privateContact->id]);
	}

	public function testUnreadableCompany(): void
	{
		$this->expectException(AccessDenied::class);
		$this->load(['company_id' => $this->privateContact->id]);
	}

	public function testReadableContact(): void
	{
		$ticket = $this->load(['contact_id' => $this->sharedContact->id]);
		$this->assertEquals($this->sharedContact->id, $ticket->contact_id);
	}
}
