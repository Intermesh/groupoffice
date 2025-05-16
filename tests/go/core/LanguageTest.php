<?php
namespace go\core;

use go\core\model\Link;
use go\modules\community\addressbook\model\Address;
use go\modules\community\addressbook\model\AddressBook;
use go\modules\community\addressbook\model\Contact;

class LanguageTest extends \PHPUnit\Framework\TestCase
{
	public function testFormatAddress() {
		$address = go()->getLanguage()->formatAddress([
			'address' => 'Veemarktkade 8',
		], "nl", false);

		$this->assertEquals('Veemarktkade 8', $address);


		$address = go()->getLanguage()->formatAddress([
			'address' => 'Veemarktkade 8'
		], "gb", false);

		$this->assertEquals("Veemarktkade 8", $address);
	}
}