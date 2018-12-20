<?php
namespace go\modules\community\carddav;

use Exception;
use go\core\acl\model\Acl;
use go\core\ErrorHandler;
use go\modules\community\addressbook\convert\VCard;
use go\modules\community\addressbook\model\AddressBook;
use go\modules\community\addressbook\model\Contact;
use go\modules\community\addressbook\model\VCard as VCardModel;
use Sabre\CardDAV\Backend\AbstractBackend;
use Sabre\CardDAV\Plugin;
use Sabre\CardDAV\Xml\Property\SupportedAddressData;
use Sabre\DAV\Exception\Forbidden;
use Sabre\DAV\Exception\NotFound;
use Sabre\DAV\PropPatch;
use Sabre\VObject\Reader;
use function GO;

class Backend extends AbstractBackend {
	
	public function createAddressBook($principalUri, $url, array $properties): void {
		
	}

	public function createCard($addressBookId, $cardUri, $cardData) {
		$addressbook = AddressBook::findById($addressbookId);
		if(!$addressbook) {
			throw new NotFound();
		}
		
		if(!$addressbook->getPermissionLevel()) {
			throw new Forbidden();
		}
		
		$vcardComp = Reader::read($cardData, Reader::OPTION_FORGIVING + Reader::OPTION_IGNORE_INVALID_LINES);
		
		$blob = \go\core\fs\Blob::fromString($cardData);
		$blob->type = 'text/vcard';
		$blob->name = $vcardComp->uid . '.vcf';
		if(!$blob->save()) {
			throw new \Exception("could not save vcard blob");
		}
			
		$contact = new Contact();
		$contact->addressBookId = $addressBookId;
		
		$contact->vcardBlobId = $blob->id;
		$c = new VCard();
		$c->import($vcardComp, $contact);
		
		//blob id can serve as ETag
		return $contact->vcardBlobId;		
	}

	public function deleteAddressBook($addressBookId): void {
		
	}

	public function deleteCard($addressBookId, $cardUri): bool {		
		$contact = Contact::find(['id', 'addressBookId'])->where(['addressBookId' => $addressBookId, 'uid' => $cardUri])->single();
		if(!$contact) {
			throw new NotFound();
		}
		if($contact->getPermissionLevel() < Acl::LEVEL_DELETE) {
			throw new Forbidden();
		}
		return $contact->delete();
	}
	
	private function addressBookToDAV(AddressBook $addressBook, $principalUri) {
		return array(
			'id' => $addressBook->id,
			'uri' => $addressBook->id,
			'principaluri' => $principalUri,
			'{DAV:}displayname' => $addressBook->name,
			'{http://calendarserver.org/ns/}getctag' => Contact::getState(),
			'{' . Plugin::NS_CARDDAV . '}supported-address-data' => new SupportedAddressData(['contentType' => 'text/vcard', 'version' => '3.0']),
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
		
		$addressbook = AddressBook::findById($addressBookId);
		if(!$addressbook) {
			throw new NotFound();
		}
		
		if(!$addressbook->getPermissionLevel()) {
			throw new Forbidden();
		}
		
		$contact = Contact::find()->where(['addressBookId' => $addressBookId, 'uid' => $cardUri])->single();
		if(!$contact) {
			throw new NotFound();
		}
		
		
		$blob = \go\core\fs\Blob::findById($contact->vcardBlobId);
		
		return [
					'carddata' => $blob->getFile()->getContents(),
					'uri' => $contact->uid,
					'lastmodified' => $contact->modifiedAt->format("U"),
					'etag' => $contact->vcardBlobId,
					'size' => $blob->size
			];
	}
	
	private function generateMissingCards($addressbookId) {
		$contacts = Contact::find()->where(['addressBookId' => $addressbookId])->andWhere('vcardBlobId', 'IS', null)->execute();
		if(!$contacts->rowCount()) {
			return;
		}
		
		$c = new VCard();
		
		foreach($contacts as $contact) {
			$vcardData = $c->export($contact);
			
			$blob = \go\core\fs\Blob::fromString($vcardData);
			$blob->type = 'text/vcard';
			$blob->name = $contact->uid . '.vcf';
			if(!$blob->save()) {
				throw new \Exception("could not save vcard blob");
			}
			
			$contact->vcardBlobId = $blob->id;
			$contact->save();
		}
	}

	public function getCards($addressbookId): array {
		$addressbook = AddressBook::findById($addressbookId);
		if(!$addressbook) {
			throw new NotFound();
		}
		
		if(!$addressbook->getPermissionLevel()) {
			throw new Forbidden();
		}
		
		$this->generateMissingCards($addressbookId);		
		
		return GO()->getDbConnection()->select('uid as uri, UNIX_TIMESTAMP(c.modifiedAt) as lastmodified, vcardBlobId AS etag, b.size')
						->from('addressbook_contact', 'c')
						->join('core_blob', 'b', 'c.vcardBlobId = b.id')
						->where('c.addressBookId', '=', $addressbookId)->all();
	}
	

	public function updateAddressBook($addressBookId, PropPatch $propPatch): void {
		
	}

	public function updateCard($addressBookId, $cardUri, $cardData) {
		
		$contact = Contact::find(['id', 'addressBookId'])->where(['addressBookId' => $addressBookId, 'uid' => $cardUri])->single();
		if(!$contact) {
			throw new NotFound();
		}
		if(!$contact->getPermissionLevel() < Acl::LEVEL_DELETE) {
			throw new Forbidden();
		}
		
		$vcardModel = VCardModel::find()
						->join('addressbook_contact', 'c', 'c.id = v.contactId')
						->where(['uid' => $cardUri, 'addressBookId' => $addressBookId])
						->single();
		
		if(!$vcardModel) {
			throw new NotFound();
		}
		
		$contact = Contact::findById($vcardModel->contactId);
		
		GO()->getDbConnection()->beginTransaction();
		
		try {
			GO()->debug($cardData);
			$c = new VCard();
			$vobject = Reader::read($cardData, Reader::OPTION_FORGIVING + Reader::OPTION_IGNORE_INVALID_LINES);
			$c->importVObject($vobject, $contact);

			$vcardModel->data = $cardData;
			if(!$vcardModel->save()) {
				throw new Exception("Can't save vcardmodel");
			}
			
		} catch(Exception $e) {
			ErrorHandler::logException($e);
			GO()->getDbConnection()->rollBack();
			return false;
		}
		
		GO()->getDbConnection()->commit();
		
		return $vcardModel->getETag();						
	}
}
