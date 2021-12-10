<?php
namespace go\core;

use go\core\model\Link;
use go\modules\community\addressbook\model\Address;
use go\modules\community\addressbook\model\AddressBook;
use go\modules\community\addressbook\model\Contact;

class LanguageTest extends \PHPUnit\Framework\TestCase
{
	public function testFormatAddress() {
		$address = go()->getLanguage()->formatAddress("nl", [
			'street' => 'Veemarktkade',
			'street2' => '8'
		]);

		$this->assertEquals('Veemarktkade 8', $address);


		$address = go()->getLanguage()->formatAddress("gb", [
			'street' => 'Veemarktkade',
			'street2' => '8'
		]);

		$this->assertEquals("Veemarktkade\n8", $address);
	}
}