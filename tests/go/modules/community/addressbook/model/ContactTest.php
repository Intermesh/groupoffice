<?php

namespace go\modules\community\addressbook\model;

use PHPUnit\Framework\TestCase;
use go\modules\community\addressbook\model\AddressBook;

class ContactTest extends TestCase {	

  public function testContact() {
    $addressBook = AddressBook::find()->where(['name' => 'Test'])->single();
    if(!$addressBook) {
      $addressBook = new AddressBook();
      $addressBook->name = "Test";
      $success = $addressBook->save();

      $this->assertEquals(true, $success);
    }

    $contact = new Contact();
    $contact->addressBookId = $addressBook->id;
    $contact->firstName = "John";
    $contact->lastName = "Doe";
    $contact->emailAddresses[0] = (new EmailAddress())->setValues(['email' => 'john@doe.test']);

    $contact->emailAddresses[0] = (new EmailAddress())->setValues(['email' => 'john@doe.test']);
  
		$contact->addresses[0] = $a = new Address();		

    $a->street =	"Street";
    $a->street2 = "1";
    $a->city = "Den Bosch";    
    $a->zipCode = "5222 AE";					
    $a->countryCode = "NL";
    
    $success = $contact->save();

    $this->assertEquals(true, $success);

  }
}