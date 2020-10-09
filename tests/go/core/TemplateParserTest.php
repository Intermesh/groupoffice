<?php
namespace go\core;

use go\core\model\Link;
use go\modules\community\addressbook\model\AddressBook;
use go\modules\community\addressbook\model\Contact;

class TemplateParserTest extends \PHPUnit\Framework\TestCase
{
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

	public function testLinks()
	{
		$addressBook = $this->getAddressBook();

		$contact1 = new Contact();
		$contact1->addressBookId = $addressBook->id;
		$contact1->firstName = "John";
		$contact1->lastName = "Doe";
		$success = $contact1->save();
		$this->assertEquals(true, $success);


		$contact2 = new Contact();
		$contact2->addressBookId = $addressBook->id;
		$contact2->firstName = "Linda";
		$contact2->lastName = "Smith";
		$success = $contact2->save();
		$this->assertEquals(true, $success);

		Link::create($contact1, $contact2);

		$tplParser = new TemplateParser();
		$tplParser->addModel('contact', $contact1);

		$tpl = '[assign firstContactLink = contact | links:Contact | first]{{firstContactLink.name}}';

		$str = $tplParser->parse($tpl);

		$this->assertEquals($contact2->name, $str);

	}
}