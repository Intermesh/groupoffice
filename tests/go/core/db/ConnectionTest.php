<?php

namespace go\core\db;

use Exception;
use go\core\App;
use go\core\event\Listeners;
use go\core\model\User;
use go\core\orm\EntityType;
use go\modules\community\addressbook\model\Address;
use go\modules\community\addressbook\model\AddressBook;
use go\modules\community\addressbook\model\Contact;
use PHPUnit\Framework\TestCase;
use ReflectionProperty;
use Throwable;

class ConnectionTest extends TestCase {

	private function getAddressBook() {
		$addressBook = AddressBook::find()->where(['name' => 'Test'])->single();
		if(!$addressBook) {
			$addressBook = new AddressBook();
			$addressBook->name = "Test";
			$success = $addressBook->save();

			$this->assertEquals(true, $success);
		}
		return $addressBook;
	}
	public function testConnect() {
		$id = go()->getDbConnection()->getId();

		//for some db activity
		$user1 = User::findById(1);
		$user2 = \GO\Base\Model\User::model()->findByPk(1);
		$contact = Contact::find()->single();

		$addressBook = $this->getAddressBook();

		$contact1 = new Contact();
		$contact1->addressBookId = $addressBook->id;
		$contact1->firstName = "John";
		$contact1->lastName = "Doe";

		$contact1->addresses[0] = $a = new Address($contact1);

		$a->type = Address::TYPE_POSTAL;
		$a->address =	"Street 1";
		$a->city = "Den Bosch";
		$a->zipCode = "5222 AE";
		$a->countryCode = "NL";

	  $contact1->save();

		$props = $user1->toArray();

		// allow one second for mysql 5.7 to close the connection
		sleep(1);

		$exists = Connection::exists($id);

		$this->assertEquals(true, $exists);

		go()->getDbConnection()->disconnect();
		$exists = Connection::exists($id);
		$this->assertEquals(false, $exists);
	}

	/**
	 * See issue #1573: a rolled back save used to leave stale entries in
	 * EntityType::$changes because it was never cleared on rollback. Those
	 * stale entries (referencing data that no longer exists after the
	 * rollback) then broke the whole batched core_change insert in
	 * EntityType::push().
	 */
	public function testRollbackDiscardsChangesQueuedDuringTransaction() {
		$changesProp = new ReflectionProperty(EntityType::class, 'changes');
		$changesProp->setAccessible(true);

		$snapshotProp = new ReflectionProperty(EntityType::class, 'changesSnapshot');
		$snapshotProp->setAccessible(true);

		$originalChanges = $changesProp->getValue();
		$originalSnapshot = $snapshotProp->getValue();

		try {
			// Changes queued and already committed before this transaction started.
			$beforeTransaction = ['1' => ['100' => ['entityId' => 100, 'aclId' => 1, 'destroyed' => false]]];
			$changesProp->setValue(null, $beforeTransaction);

			go()->getDbConnection()->beginTransaction();

			// Simulate changes queued by entities saved (and nested-committed) during
			// this transaction, referencing data that only exists because of it.
			$duringTransaction = $beforeTransaction;
			$duringTransaction['2'] = ['200' => ['entityId' => 200, 'aclId' => 999999, 'destroyed' => false]];
			$changesProp->setValue(null, $duringTransaction);

			go()->getDbConnection()->rollBack();

			$this->assertEquals(
				$beforeTransaction,
				$changesProp->getValue(),
				"Changes queued during a rolled back transaction must be discarded, while changes queued before it must survive."
			);
			$this->assertNull($snapshotProp->getValue(), "The snapshot must be consumed after a rollback.");
		} finally {
			$changesProp->setValue(null, $originalChanges);
			$snapshotProp->setValue(null, $originalSnapshot);
		}
	}

	/**
	 * Same as testRollbackDiscardsChangesQueuedDuringTransaction() but going
	 * through the real production path: Entity::save() -> Entity::rollback()
	 * -> Connection::rollBack(), instead of calling Connection::rollBack()
	 * directly. Entity::rollback() only calls Connection::rollBack() when
	 * go()->getDbConnection()->inTransaction() is still true, so this proves
	 * that guard doesn't prevent EntityType::$changes from being cleared for
	 * an ordinary failed save.
	 *
	 * The AddressBook change queued by self::queueChangeAndThrow() below
	 * simulates what {@see \go\core\model\User::internalSave()} does for the
	 * personal AddressBook/NoteBook/TaskList it creates while saving a new
	 * user (see issue #1573): those saves complete (and queue their change)
	 * before the outer entity's own EVENT_SAVE/EVENT_BEFORE_SAVE fires.
	 */
	public function testFailedSaveDiscardsChangesQueuedDuringItThroughRealRollback() {
		$changesProp = new ReflectionProperty(EntityType::class, 'changes');
		$changesProp->setAccessible(true);

		$snapshotProp = new ReflectionProperty(EntityType::class, 'changesSnapshot');
		$snapshotProp->setAccessible(true);

		$originalChanges = $changesProp->getValue();
		$originalSnapshot = $snapshotProp->getValue();

		Contact::on(Contact::EVENT_BEFORE_SAVE, self::class, 'queueChangeAndThrow');

		try {
			$addressBook = $this->getAddressBook();
			self::$addressBookForTest = $addressBook;

			// Changes already queued (and, in a real request, already committed)
			// before this save starts.
			$baseline = $changesProp->getValue();

			$contact = new Contact();
			$contact->addressBookId = $addressBook->id;
			$contact->firstName = "Rollback";
			$contact->lastName = "Test";

			$threw = false;
			try {
				$contact->save();
			} catch(Throwable $e) {
				$threw = true;
			}

			$this->assertTrue($threw, "The EVENT_BEFORE_SAVE listener should have thrown and aborted the save.");

			$this->assertEquals(
				$baseline,
				$changesProp->getValue(),
				"A failed save must discard changes queued for nested entities during it (here: the AddressBook change queued by the listener), while changes queued before it must survive."
			);
			$this->assertNull($snapshotProp->getValue(), "The snapshot must be consumed after the save's rollback.");
		} finally {
			$changesProp->setValue(null, $originalChanges);
			$snapshotProp->setValue(null, $originalSnapshot);
			self::$addressBookForTest = null;
			// Drop the request-only listener we just added so it doesn't leak into other tests.
			Listeners::get()->clear();
		}
	}

	private static ?AddressBook $addressBookForTest = null;

	public static function queueChangeAndThrow($entity) {
		AddressBook::entityType()->change(self::$addressBookForTest);
		throw new Exception("Simulated failure during save to test EntityType::\$changes rollback");
	}

	public function testCommitDiscardsChangesSnapshot() {
		$changesProp = new ReflectionProperty(EntityType::class, 'changes');
		$changesProp->setAccessible(true);

		$snapshotProp = new ReflectionProperty(EntityType::class, 'changesSnapshot');
		$snapshotProp->setAccessible(true);

		$originalChanges = $changesProp->getValue();
		$originalSnapshot = $snapshotProp->getValue();

		try {
			$changesProp->setValue(null, []);

			go()->getDbConnection()->beginTransaction();
			go()->getDbConnection()->commit();

			$this->assertNull($snapshotProp->getValue(), "A successful commit must discard the snapshot instead of leaving it around for a later unrelated rollback to restore.");
		} finally {
			$changesProp->setValue(null, $originalChanges);
			$snapshotProp->setValue(null, $originalSnapshot);
		}
	}

}
