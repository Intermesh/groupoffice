<?php

namespace go\core\db;

use go\core\App;
use go\core\model\User;
use go\modules\community\addressbook\model\Address;
use go\modules\community\addressbook\model\AddressBook;
use go\modules\community\addressbook\model\Contact;
use PHPUnit\Framework\TestCase;

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

		$exists = $this->connExists($id);

		$this->assertEquals(true, $exists);

		go()->getDbConnection()->disconnect();
		$exists = $this->connExists($id);
		$this->assertEquals(false, $exists);
	}

	private function connExists(int $id) : bool {

		$dsn = go()->getDbConnection()->getDsn();
		$config = go()->getConfig();

		$watchConn = new Connection(
			$dsn, $config['db_user'], $config['db_pass']
		);
		$processes = $watchConn->query("SHOW PROCESSLIST")->fetchAll(\PDO::FETCH_ASSOC);

		foreach($processes as $process) {
			if($process['Id'] == $id) {
				return true;
			}
		}
		return false;
	}


}
