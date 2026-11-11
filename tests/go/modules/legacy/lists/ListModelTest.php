<?php
namespace go\modules\legacy\lists;

use GO\Base\Exception\AccessDenied;
use GO\Lists\Model\ListModel;
use GO\Lists\Model\ListObject;
use go\core\auth\TemporaryState;
use go\core\model\Acl;
use go\core\model\User;
use go\modules\community\notes\model\Note;
use go\modules\community\notes\model\NoteBook;
use PHPUnit\Framework\TestCase;

/**
 * A class that isn't a model but takes any constructor arguments
 */
class ListModelTestGadget
{
	public static bool $created = false;

	public function __construct(...$args)
	{
		self::$created = true;
	}
}

/**
 * Adding an item to a list creates the class of the item type that the client sends, so it must be a model, and the
 * user must be allowed to change the list and to read the item.
 */
class ListModelTest extends TestCase
{
	private $authState;
	private User $user;
	private array $lists = [];
	private array $noteBooks = [];
	private Note $readable;
	private Note $hidden;

	protected function setUp(): void
	{
		$this->authState = go()->getAuthState();
		$this->actAs(User::ID_SUPER_ADMIN);

		if (!\GO::modules()->isInstalled('lists')) {
			$this->assertTrue((bool) \GO\Base\Model\Module::install('lists'), 'Could not install lists module');
		}

		$name = uniqid('listuser');
		$this->user = new User();
		$this->user->setValues([
			'username' => $name,
			'displayName' => $name,
			'email' => $name . '@example.com',
			'password' => 'Pa55word!!xyz'
		]);
		$this->assertTrue($this->user->save(), $this->user->getValidationErrorsAsString());
		$groupId = $this->user->getPersonalGroup()->id;

		foreach (['write' => Acl::LEVEL_WRITE, 'read' => Acl::LEVEL_READ] as $key => $level) {
			$list = new ListObject();
			$list->name = $name . $key;
			$this->assertTrue($list->save(), var_export($list->getValidationErrors(), true));
			$list->acl->addGroup($groupId, $level);
			$this->lists[$key] = $list;
		}

		foreach (['read' => Acl::LEVEL_READ, 'none' => null] as $key => $level) {
			$noteBook = new NoteBook();
			$noteBook->name = $name . $key;
			if ($level) {
				$noteBook->setAcl([$groupId => $level]);
			}
			$this->assertTrue($noteBook->save(), $noteBook->getValidationErrorsAsString());
			$this->noteBooks[$key] = $noteBook;

			$note = new Note();
			$note->noteBookId = $noteBook->id;
			$note->name = $name . $key;
			$note->content = "";
			$this->assertTrue($note->save(), $note->getValidationErrorsAsString());
			if ($key == 'read') {
				$this->readable = $note;
			} else {
				$this->hidden = $note;
			}
		}
	}

	protected function tearDown(): void
	{
		unset($_REQUEST['model_type_name']);
		$this->actAs(User::ID_SUPER_ADMIN);
		foreach ($this->lists as $list) {
			foreach (ListModel::model()->findByAttribute('list_id', $list->id) as $listModel) {
				$listModel->delete();
			}
			$list->delete();
		}
		NoteBook::delete(['id' => array_map(fn($nb) => $nb->id, $this->noteBooks)]);
		User::delete(['id' => $this->user->id]);
		go()->setAuthState($this->authState);
	}

	private function actAs(int $userId): void
	{
		go()->setAuthState(new TemporaryState($userId));
		\GO::session()->values['user_id'] = $userId;
	}

	private function add(string $list, Note $note): bool
	{
		$this->actAs($this->user->id);
		$listModel = new ListModel();
		$listModel->list_id = $this->lists[$list]->id;
		$listModel->model_id = $note->id;
		$listModel->model_type_id = Note::entityType()->getId();
		$listModel->name = 'test';
		try {
			return $listModel->save();
		} catch (AccessDenied $e) {
			return false;
		} finally {
			$this->actAs(User::ID_SUPER_ADMIN);
		}
	}

	public function testAddReadableItemToWritableList(): void
	{
		$this->assertTrue($this->add('write', $this->readable));
	}

	public function testListMustBeWritable(): void
	{
		$this->assertFalse($this->add('read', $this->readable));
	}

	public function testItemMustBeReadable(): void
	{
		$this->assertFalse($this->add('write', $this->hidden));
	}

	public function testModelTypeMustBeAModel(): void
	{
		ListModelTestGadget::$created = false;
		$_REQUEST['model_type_name'] = ListModelTestGadget::class;

		$this->actAs($this->user->id);
		$listModel = new ListModel();
		$listModel->list_id = $this->lists['write']->id;
		$listModel->model_id = $this->readable->id;
		$listModel->name = 'test';
		try {
			$listModel->save();
		} catch (\Throwable $e) {
		}
		$this->actAs(User::ID_SUPER_ADMIN);

		$this->assertFalse(ListModelTestGadget::$created, "The class may not be created");
		$this->assertTrue($listModel->isNew);
	}
}
