<?php
namespace go\modules\community\carddav;

use go\core\acl\model\Acl;
use go\modules\community\addressbook\model\AddressBook;
use Sabre\CardDAV\Backend\AbstractBackend;
use Sabre\CardDAV\Plugin;
use Sabre\CardDAV\Xml\Property\SupportedAddressData;
use Sabre\DAV\PropPatch;

class Backend extends AbstractBackend {
	
	public function createAddressBook($principalUri, $url, array $properties): void {
		
	}

	public function createCard($addressBookId, $cardUri, $cardData) {
		
	}

	public function deleteAddressBook($addressBookId): void {
		
	}

	public function deleteCard($addressBookId, $cardUri): bool {
		
	}
	
	private function addressBookToDAV(AddressBook $addressBook, $principalUri) {
		return array(
			'id' => $addressBook->id,
			'uri' => $addressBook->id,
			'principaluri' => $principalUri,
			'{DAV:}displayname' => $addressBook->name,
			'{http://calendarserver.org/ns/}getctag' => AddressBook::getState(),
			'{' . Plugin::NS_CARDDAV . '}supported-address-data' => new SupportedAddressData(['contentType' => 'text/vcard', 'version' => '4.0']),
			'{' . Plugin::NS_CARDDAV . '}addressbook-description' => 'User addressbook'
		);
	}

	public function getAddressBooksForUser($principalUri): array {
		
		$r = [];
		$addressBooks = AddressBook::find()
						->filter(['permissionLevel' => Acl::LEVEL_READ]);
						
		foreach($addressBooks as $a) {
			$r[] = $this->addressBookToDAV($a, $principalUri);
		}
		
		return $r;
						
	}

	public function getCard($addressBookId, $cardUri): array {
		
	}

	public function getCards($addressbookId): array {
		return [];
	}

	public function updateAddressBook($addressBookId, PropPatch $propPatch): void {
		
	}

	public function updateCard($addressBookId, $cardUri, $cardData) {
		
	}

}
