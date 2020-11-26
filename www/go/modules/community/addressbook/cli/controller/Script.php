<?php
namespace go\modules\community\addressbook\cli\controller;


use go\core\Controller;
use go\core\jmap\Entity;
use go\core\model\Link;
use go\core\orm\LoggingTrait;
use go\core\orm\Property;
use go\modules\community\addressbook\install\Migrate63to64;
use go\modules\community\addressbook\model\Address;
use go\modules\community\addressbook\model\AddressBook;
use go\modules\community\addressbook\model\Contact;
use go\modules\community\addressbook\model\EmailAddress;
use go\modules\community\addressbook\model\PhoneNumber;
use go\modules\community\history\Module;

class Script extends Controller {

  /**
   * ./cli.php community/addressbook/Script/fixMissingCompanies
   */
  public function fixMissingCompanies() {

    Entity::$trackChanges = false;

    $m = new Migrate63to64();
    $m->fixMissing();
  }

	public function createLotsOfData()
	{
		Entity::$trackChanges = false;
		Module::$enabled = false;
		go()->getDebugger()->enabled = false;

		$addressBook = AddressBook::find()->single();

		for ($i = 5000; $i < 500000; $i++) {
			echo $i . "\n";
			$company = new Contact();
			$company->isOrganization = true;
			$company->addressBookId = $addressBook->id;
			$company->name = "Acme" . $i;
			$company->phoneNumbers[0] = (new PhoneNumber())->setValues(['number' => "(555)" . $i, 'type' => PhoneNumber::TYPE_MOBILE]);
			$company->emailAddresses[0] = (new EmailAddress())->setValues(['email' => 'john' . $i . '@doe.test', 'type' => EmailAddress::TYPE_HOME]);
			$company->addresses[0] = $a = new Address();

			$a->street = "Street";
			$a->street2 = $i;
			$a->city = "Den Bosch";
			$a->zipCode = "5222 AE";
			$a->countryCode = "NL";

			$company->notes = "The standard Lorem Ipsum passage, used since the 1500s
Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.
Section 1.10.32 of de Finibus Bonorum et Malorum, written by Cicero in 45 BC
Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque laudantium, totam rem aperiam, eaque ipsa quae ab illo inventore veritatis et quasi architecto beatae vitae dicta sunt explicabo. Nemo enim ipsam voluptatem quia voluptas sit aspernatur aut odit aut fugit, sed quia consequuntur magni dolores eos qui ratione voluptatem sequi nesciunt. Neque porro quisquam est, qui dolorem ipsum quia dolor sit amet, consectetur, adipisci velit, sed quia non numquam eius modi tempora incidunt ut labore et dolore magnam aliquam quaerat voluptatem. Ut enim ad minima veniam, quis nostrum exercitationem ullam corporis suscipit laboriosam, nisi ut aliquid ex ea commodi consequatur? Quis autem vel eum iure reprehenderit qui in ea voluptate velit esse quam nihil molestiae consequatur, vel illum qui dolorem eum fugiat quo voluptas nulla pariatur?
";
			if(!$company->save()) {
				var_dump($company->getValidationErrors());
				exit();
			}

			$contact = new Contact();
			$contact->addressBookId = $addressBook->id;
			$contact->firstName = "John";
			$contact->lastName = "Doe " . $i;
			$contact->phoneNumbers[0] = (new PhoneNumber())->setValues(['number' => "(555)" .  $i, 'type' => PhoneNumber::TYPE_MOBILE]);
			$contact->emailAddresses[0] = (new EmailAddress())->setValues(['email' => 'john' . $i . '@doe.test', 'type' => EmailAddress::TYPE_HOME]);
			$contact->addresses[0] = $a = new Address();

			$a->street = "Street";
			$a->street2 = $i;
			$a->city = "Den Bosch";
			$a->zipCode = "5222 AE";
			$a->countryCode = "NL";

			$contact->notes = "The standard Lorem Ipsum passage, used since the 1500s
Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.
Section 1.10.32 of de Finibus Bonorum et Malorum, written by Cicero in 45 BC
Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque laudantium, totam rem aperiam, eaque ipsa quae ab illo inventore veritatis et quasi architecto beatae vitae dicta sunt explicabo. Nemo enim ipsam voluptatem quia voluptas sit aspernatur aut odit aut fugit, sed quia consequuntur magni dolores eos qui ratione voluptatem sequi nesciunt. Neque porro quisquam est, qui dolorem ipsum quia dolor sit amet, consectetur, adipisci velit, sed quia non numquam eius modi tempora incidunt ut labore et dolore magnam aliquam quaerat voluptatem. Ut enim ad minima veniam, quis nostrum exercitationem ullam corporis suscipit laboriosam, nisi ut aliquid ex ea commodi consequatur? Quis autem vel eum iure reprehenderit qui in ea voluptate velit esse quam nihil molestiae consequatur, vel illum qui dolorem eum fugiat quo voluptas nulla pariatur?
";
			if(!$contact->save()) {
				var_dump($company->getValidationErrors());
				exit();
			}


			Link::create($contact, $company);
		}
	}
}