<?php
namespace go\modules\community\addressbook\cli\controller;


use go\core\Controller;
use go\core\jmap\Entity;
use go\core\orm\LoggingTrait;
use go\core\orm\Property;
use go\modules\community\addressbook\install\Migrate63to64;
use go\modules\community\addressbook\model\Address;
use go\modules\community\addressbook\model\AddressBook;
use go\modules\community\addressbook\model\Contact;
use go\modules\community\addressbook\model\EmailAddress;

class Script extends Controller {

  /**
   * ./cli.php community/addressbook/Script/fixMissingCompanies
   */
  public function fixMissingCompanies() {

    Entity::$trackChanges = false;
    LoggingTrait::disable();

    $m = new Migrate63to64();
    $m->fixMissing();
  }
}