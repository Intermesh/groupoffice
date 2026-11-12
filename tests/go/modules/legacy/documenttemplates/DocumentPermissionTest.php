<?php
namespace go\modules\legacy\documenttemplates;

use GO\Base\Exception\AccessDenied;
use GO\Base\Model\Acl as LegacyAcl;
use GO\Base\Model\Template;
use GO\Documenttemplates\Controller\DocumentController;
use go\core\auth\TemporaryState;
use go\core\model\Acl;
use go\core\model\User;
use go\modules\community\addressbook\model\AddressBook;
use go\modules\community\addressbook\model\Contact;
use PHPUnit\Framework\TestCase;

/**
 * A document is filled with the data of an item and its linked items and saved in the folder of the item. The user must
 * be able to read those items and write in that folder.
 */
class DocumentPermissionTest extends TestCase
{
	private $authState;
	private User $user;
	private Template $template;
	private AddressBook $privateBook;
	private AddressBook $sharedBook;
	private AddressBook $readOnlyBook;
	private Contact $privateContact;
	private Contact $sharedContact;

	protected function setUp(): void
	{
		if (!class_exists(DocumentController::class) || !go()->getModule('community', 'addressbook')) {
			$this->markTestSkipped('The documenttemplates and addressbook modules are required');
		}

		$this->authState = go()->getAuthState();
		$this->actAs(User::ID_SUPER_ADMIN);

		$name = uniqid('docuser');
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
		$this->sharedBook->setAcl([\go\core\model\Group::ID_ADMINS => Acl::LEVEL_MANAGE, $this->user->getPersonalGroup()->id => Acl::LEVEL_WRITE]);
		$this->assertTrue($this->sharedBook->save());

		$this->readOnlyBook = new AddressBook();
		$this->readOnlyBook->name = uniqid('readonly');
		$this->readOnlyBook->setAcl([\go\core\model\Group::ID_ADMINS => Acl::LEVEL_MANAGE, $this->user->getPersonalGroup()->id => Acl::LEVEL_READ]);
		$this->assertTrue($this->readOnlyBook->save());

		$this->privateContact = $this->createContact($this->privateBook, 'Private');
		$this->sharedContact = $this->createContact($this->sharedBook, 'Shared');

		$this->template = $this->createTemplate();
	}

	protected function tearDown(): void
	{
		if (!isset($this->authState)) {
			return;
		}
		$this->actAs(User::ID_SUPER_ADMIN);
		if (isset($this->template)) {
			$folder = new \GO\Base\Fs\Folder(\GO::config()->tmpdir . 'templates/' . $this->template->id . '/');
			$folder->delete();
			$this->template->delete(true);
		}
		foreach (['privateBook', 'sharedBook', 'readOnlyBook'] as $book) {
			if (isset($this->$book)) {
				AddressBook::delete(['id' => $this->$book->id]);
			}
		}
		if (isset($this->user)) {
			User::delete(['id' => $this->user->id]);
		}
		go()->setAuthState($this->authState);
	}

	private function actAs(int $userId): void
	{
		go()->setAuthState(new TemporaryState($userId));
		\GO::session()->setCurrentUser($userId);
	}

	private function createContact(AddressBook $book, string $name): Contact
	{
		$contact = new Contact();
		$contact->addressBookId = $book->id;
		$contact->firstName = $name;
		$this->assertTrue($contact->save(), $contact->getValidationErrorsAsString());
		return $contact;
	}

	/**
	 * An OpenDocument template with a contact tag, readable for the user
	 */
	private function createTemplate(): Template
	{
		$zipFile = \GO::config()->getTempFolder()->path() . '/' . uniqid('template') . '.odt';
		$zip = new \ZipArchive();
		$this->assertTrue($zip->open($zipFile, \ZipArchive::CREATE));
		$zip->addFromString('content.xml', '<office:document-content><office:text>{contact:firstName}</office:text></office:document-content>');
		$zip->close();

		$template = new Template();
		$template->name = uniqid('permtemplate');
		$template->type = Template::TYPE_DOCUMENT;
		$template->extension = 'odt';
		$template->content = file_get_contents($zipFile);
		unlink($zipFile);
		$this->assertTrue($template->save(true), var_export($template->getValidationErrors(), true));
		$template->acl->addGroup($this->user->getPersonalGroup()->id, LegacyAcl::READ_PERMISSION);
		return $template;
	}

	private function create(int $modelId, array $links = []): array
	{
		$this->actAs($this->user->id);

		// The client first gets the links, which extracts the template
		(new \ReflectionMethod(DocumentController::class, 'actionGetLinks'))->invoke(new DocumentController(), [
			'template_id' => $this->template->id,
			'model_name' => 'Contact',
			'model_id' => $this->sharedContact->id
		]);

		$method = new \ReflectionMethod(DocumentController::class, 'actionCreate');
		return $method->invoke(new DocumentController(), [
			'template_id' => $this->template->id,
			'model_name' => 'Contact',
			'model_id' => $modelId,
			'filename' => uniqid('document'),
			'selectedLinks' => json_encode($links)
		]);
	}

	public function testDocumentForUnreadableItem(): void
	{
		$this->expectException(AccessDenied::class);
		$this->create($this->privateContact->id);
	}

	public function testDocumentInFolderWithoutWritePermission(): void
	{
		$contact = $this->createContact($this->readOnlyBook, 'ReadOnly');
		$this->expectException(AccessDenied::class);
		$this->create($contact->id);
	}

	public function testDocumentWithUnreadableLinkedItem(): void
	{
		$this->expectException(AccessDenied::class);
		$this->create($this->sharedContact->id, [Contact::class . ':' . $this->privateContact->id]);
	}

	public function testDocumentWithLinkToAClassThatIsNotAnEntity(): void
	{
		$this->expectException(AccessDenied::class);
		$this->create($this->sharedContact->id, [\go\core\util\StringUtil::class . ':1']);
	}

	public function testDocumentWithReadableLinkedItem(): void
	{
		$response = $this->create($this->sharedContact->id, [Contact::class . ':' . $this->sharedContact->id]);
		$this->assertTrue($response['success']);
	}
}
