<?php
namespace go\modules\community\addressbook\cli\controller;


use go\core\Controller;
use go\core\jmap\Entity;
use go\core\model\Link;
use go\core\model\User;
use go\core\orm\LoggingTrait;
use go\core\orm\Property;
use go\modules\community\addressbook\install\Migrate63to64;
use go\modules\community\addressbook\model\Address;
use go\modules\community\addressbook\model\AddressBook;
use go\modules\community\addressbook\model\Contact;
use go\modules\community\addressbook\model\EmailAddress;
use go\modules\community\addressbook\model\PhoneNumber;
use go\modules\community\history\Module;
use Faker;

class Script extends Controller {

  /**
   * ./cli.php community/addressbook/Script/fixMissingCompanies
   */
  public function fixMissingCompanies() {

    Entity::$trackChanges = false;

    $m = new Migrate63to64();
    $m->fixMissing();
  }


	/**
	 * Reset's country text to current locale from iso codes
	 *
	 * ./cli.php community/addressbook/Script/fixCountries
	 */
  public function fixCountries() {
  	$countries = go()->t("countries");

  	foreach($countries as $iso => $text) {
  		echo $iso . ': '. $text ."\n";
  		$sql = "UPDATE addressbook_address set country = :text where countryCode = :code";
  		$stmt = go()->getDbConnection()->getPDO()->prepare($sql);
  		$stmt->execute(['code' => $iso, 'text' => $text]);
	  }

  	echo "Done\n";
  }
}