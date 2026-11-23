<?php
namespace go\core\model;

use go\core\auth\TemporaryState;
use go\modules\community\notes\model\Note;
use go\modules\community\notes\model\NoteBook;
use PHPUnit\Framework\TestCase;

/**
 * An alert of an entity that was deleted has nothing to show. Deleting the entity deletes its alerts, and an alert
 * without a search record doesn't break Alert/get.
 */
class AlertTest extends TestCase
{
	private static $authState;
	private static NoteBook $noteBook;
	private Note $note;

	public static function setUpBeforeClass(): void
	{
		self::$authState = go()->getAuthState();
		go()->setAuthState(new TemporaryState(User::ID_SUPER_ADMIN));

		self::$noteBook = new NoteBook();
		self::$noteBook->name = "Alert test " . uniqid();
		if (!self::$noteBook->save()) {
			throw new \Exception(self::$noteBook->getValidationErrorsAsString());
		}
	}

	public static function tearDownAfterClass(): void
	{
		NoteBook::delete(['id' => self::$noteBook->id]);
		go()->setAuthState(self::$authState);
	}

	protected function setUp(): void
	{
		$this->note = new Note();
		$this->note->noteBookId = self::$noteBook->id;
		$this->note->name = "Alert test " . uniqid();
		$this->note->content = "test";
		$this->assertTrue($this->note->save(), $this->note->getValidationErrorsAsString());
	}

	protected function tearDown(): void
	{
		Note::delete(['id' => $this->note->id]);
		Alert::delete(['entityTypeId' => Note::entityType()->getId(), 'entityId' => $this->note->id]);
	}

	private function createAlert(): Alert
	{
		$alert = $this->note->createAlert(new \DateTime(), 'alerttest', User::ID_SUPER_ADMIN);
		$this->assertTrue($alert->save(), $alert->getValidationErrorsAsString());
		return $alert;
	}

	public function testDeletingTheEntityDeletesItsAlerts(): void
	{
		$alert = $this->createAlert();
		$this->assertSame($this->note->name, $alert->getSearch()->name);

		$this->assertTrue(Note::delete(['id' => $this->note->id]));

		$this->assertNull(Alert::findById($alert->id));
	}

	public function testAlertWithoutSearchRecord(): void
	{
		$alert = $this->createAlert();
		go()->getDbConnection()->delete('core_search', [
			'entityTypeId' => Note::entityType()->getId(),
			'entityId' => $this->note->id
		])->execute();

		$alert = Alert::findById($alert->id);
		$this->assertNull($alert->getSearch());
		$this->assertArrayHasKey('search', $alert->toArray());
	}
}
