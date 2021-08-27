<?php

namespace go\modules\community\addressbook\model;

use PHPUnit\Framework\TestCase;
use go\modules\community\addressbook\model\AddressBook;
use go\modules\community\addressbook\model\Contact;

class ContactTest extends TestCase {	

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

  public function testContact() {
    $addressBook = $this->getAddressBook();

    $contact = new Contact();
    $contact->addressBookId = $addressBook->id;
    $contact->firstName = "John";
    $contact->lastName = "Doe";
    $contact->emailAddresses[0] = (new EmailAddress($contact))->setValues(['email' => 'john@doe.test', 'type' => EmailAddress::TYPE_HOME]);
		$contact->addresses[0] = $a = new Address($contact);

    $a->street =	"Street";
    $a->street2 = "1";
    $a->city = "Den Bosch";    
    $a->zipCode = "5222 AE";					
    $a->countryCode = "NL";
    
    $success = $contact->save();

    $this->assertEquals(true, $success);

    $contact = Contact::findById($contact->id);

    $email = $contact->findEmailByType(EmailAddress::TYPE_HOME);
    $email->type = EmailAddress::TYPE_WORK;

    $address = $contact->findAddressByType(Address::TYPE_POSTAL);
    $address->countryCode = "DE";

    $success = $contact->save();

    $this->assertEquals(true, $success);

    $contact = Contact::findById($contact->id);

    $this->assertEquals(EmailAddress::TYPE_WORK, $contact->emailAddresses[0]->type);
    $this->assertEquals("DE", $contact->addresses[0]->countryCode);

    
    $this->assertEquals(true, $success);

  }

  public function testDoubleSave() {

    $addressBook = $this->getAddressBook();
    
    $contact = new Contact();
    $contact->addressBookId = $addressBook->id;
    $contact->firstName = "John";
    $contact->lastName = "Doe";
    $contact->emailAddresses[0] = (new EmailAddress($contact))->setValues(['email' => 'john@doe.test', 'type' => EmailAddress::TYPE_HOME]);
    $contact->addresses[0] = $a = new Address($contact);
    
//    $contact->setStarred(true);

    $a->street =	"Street";
    $a->street2 = "1";
    $a->city = "Den Bosch";    
    $a->zipCode = "5222 AE";					
    $a->countryCode = "NL";

    $success = $contact->save();

    $this->assertEquals(true, $success);

    $success = $contact->save();
    $this->assertEquals(true, $success);    
  }

  public function testDelete() {
    $contact = Contact::find()->single();
    
    $success = Contact::delete(['id' => $contact->id]);

    $this->assertEquals(true, $success);
  }
}