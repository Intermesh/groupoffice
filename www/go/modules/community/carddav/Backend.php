<?php /** @noinspection HttpUrlsUsage */

/** @noinspection PhpUndefinedFieldInspection */

namespace go\modules\community\carddav;

use Exception;
use go\core\fs\Blob;
use go\core\http\Request;
use go\core\model\Acl;
use go\core\ErrorHandler;
use go\core\orm\exception\SaveException;
use go\core\orm\Query;
use go\core\util\DateTime;
use go\modules\community\addressbook\convert\VCard;
use go\modules\community\addressbook\model\AddressBook;
use go\modules\community\addressbook\model\Contact;
use Sabre\CardDAV\Backend\AbstractBackend;
use Sabre\CardDAV\Plugin;
use Sabre\CardDAV\Xml\Property\SupportedAddressData;
use Sabre\DAV\Exception\Forbidden;
use Sabre\DAV\Exception\NotFound;
use Sabre\DAV\PropPatch;
use Sabre\VObject\Component\VCard as VCardComp;
use Sabre\VObject\Reader;
use function GO;

class Backend extends AbstractBackend {
	
	public function createAddressBook($principalUri, $url, array $properties): void {
		
	}

	/**
	 * Get default address book
	 *
	 * @return AddressBook
	 * @throws Exception
	 */
	public function getDefaultAddressBook(): AddressBook
	{

		$addressbook = AddressBook::find()
			->join('sync_addressbook_user', 'su', 'su.addressBookId = a.id')
			->filter(['permissionLevel' => Acl::LEVEL_WRITE])
			->where('su.userId', '=', go()->getAuthState()->getUserId())
			->orderBy(['su.isDefault' => 'DESC'])
			->single();

		if (!$addressbook)
			throw new Exception("FATAL: No default addressbook configured");

		return $addressbook;
	}

	/**
	 * @throws Forbidden
	 * @throws NotFound
	 * @throws Exception
	 */
	public function createCard($addressBookId, $cardUri, $cardData): string
	{
		if($addressBookId == "all") {
			$addressbook = $this->getDefaultAddressBook();
			$addressBookId = $addressbook->id;
		} else {
			$addressbook = AddressBook::findById($addressBookId);
			if (!$addressbook) {
				throw new NotFound();
			}

			if (!$addressbook->getPermissionLevel()) {
				throw new Forbidden();
			}
		}
		
		$vcardComp = Reader::read($cardData, Reader::OPTION_FORGIVING + Reader::OPTION_IGNORE_INVALID_LINES);
		/** @var $vcardComp VCardComp */
		$contact = new Contact();
		$contact->addressBookId = (int) $addressBookId;
		$contact->setUid((string) $vcardComp->uid);
		$contact->setUri($cardUri);
		
		$this->createBlob($contact, $cardData);
		
		$c = new VCard();
		$c->import($vcardComp, $contact);
		
		//blob id can serve as ETag
		return '"' . $contact->vcardBlobId . '"';
	}

	public function deleteAddressBook($addressBookId) {
		
	}

	/**
	 * @throws Exception
	 */
	private function createBlob(Contact $contact, $cardData): Blob
	{
		
		//Important to set exactly the same modifiedAt on both blob and contact. 
		//We compare these to check if vcards need to be updated.
		$contact->modifiedAt = new DateTime();
		
		$blob = Blob::fromString($cardData);
		$blob->type = 'text/vcard';
		$blob->name = $contact->getUri();
		if(empty($blob->name)) {
			$blob->name = 'nouid.vcf';
		}
		$blob->modifiedAt = $contact->modifiedAt;
		if(!$blob->save()) {
			throw new Exception("could not save vcard blob for contact '" . $contact->id() . "'. Validation error: " . $blob->getValidationErrorsAsString());
		}
		
		$contact->vcardBlobId = $blob->id;
		
		return $blob;
	}

	/**
	 * @throws NotFound
	 * @throws Forbidden
	 * @throws Exception
	 */
	public function deleteCard($addressBookId, $cardUri): bool {

		if(!go()->getAuthState()->getUser(['syncSettings'])->syncSettings->allowDeletes) {
			go()->debug("Deleting is disabled by user sync settings");
			throw new Forbidden("Deleting is disabled by user sync settings");
		}


		$contact = Contact::find(['id', 'addressBookId'])->where(['addressBookId' => $addressBookId, 'uri' => $cardUri])->single();
		if(!$contact) {
			throw new NotFound();
		}
		if($contact->getPermissionLevel() < Acl::LEVEL_DELETE) {
			throw new Forbidden();
		}
		return Contact::delete($contact->primaryKeyValues());
	}
	
	private function addressBookToDAV(AddressBook $addressBook, $principalUri): array
	{
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

	private function isMacOs() {
		//// [user-agent] => macOS/12.1 (21C52) AddressBookCore/2498.2.1
		$ua =  Request::get()->getHeader('user-agent');
		return stripos($ua, 'macos') !== false && stripos($ua, "AddressBookCore") !== false;
	}

	/**
	 * @throws Exception
	 */
	public function getAddressBooksForUser($principalUri): array {

		if($this->isMacOs()) {
			return [
				[
					'id' => "all",
					'uri' => "all",
					'principaluri' => $principalUri,
					'{DAV:}displayname' => "All together",
					'{http://calendarserver.org/ns/}getctag' => Contact::getState(),
					'{' . Plugin::NS_CARDDAV . '}supported-address-data' => new SupportedAddressData(['contentType' => 'text/vcard', 'version' => '3.0']),
					'{' . Plugin::NS_CARDDAV . '}addressbook-description' => 'User addressbook'
				]
			];
		}

		$r = [];

		$addressBooks = $this->findAddressBooks();
						
		foreach($addressBooks as $a) {
			$r[] = $this->addressBookToDAV($a, $principalUri);
		}
		
		return $r;						
	}


	/**
	 * @throws Exception
	 */
	private function findAddressBooks(): Query {
		return AddressBook::find()
			->join("sync_addressbook_user", "u", "u.addressBookId = a.id")
			->filter(['permissionLevel' => Acl::LEVEL_READ])
			->andWhere('u.userId', '=', go()->getAuthState()->getUserId());
	}

	/**
	 * @throws SaveException
	 * @throws NotFound
	 * @throws Forbidden
	 * @throws Exception
	 */
	public function getCard($addressBookId, $cardUri): array {

		if($addressBookId == "all") {
			$addressBookId = $this->findAddressBooks()->selectSingleValue('addressBookId');
		}

		$contact = Contact::find()->where(['addressBookId' => $addressBookId, 'uri' => rawurldecode($cardUri)])->single();
		/** @var Contact $contact */
		if(!$contact) {
			throw new NotFound();
		}

		if(!$contact->getPermissionLevel()) {
			throw new Forbidden();
		}
		
		$blob = isset($contact->vcardBlobId) ? Blob::findById($contact->vcardBlobId) : false;
		
		if(!$blob || $blob->modifiedAt < $contact->modifiedAt) {
			//blob won't be deleted if still used
			$c = new VCard();
			$cardData = $c->export($contact);			
			$blob = $this->createBlob($contact, $cardData);
			$contact->save();
		}
		
		return [
					'carddata' => $blob->getFile()->getContents(),
					'uri' => $contact->getUri(),
					'lastmodified' => $contact->modifiedAt->format("U"),
					'etag' => '"' . $contact->vcardBlobId . '"',
					'size' => $blob->size
			];
	}

	/**
	 * @throws SaveException
	 * @throws Exception
	 */
	private function generateCards($addressBookId) {
		$contacts = Contact::find()
						->join('core_blob', 'b', 'b.id = c.vcardBlobId', 'LEFT')
						->where(['addressBookId' => $addressBookId])
						->andWhere('(c.vcardBlobId IS NULL OR b.modifiedAt < c.modifiedAt)')
						->execute();
		
		if(!$contacts->rowCount()) {
			return;
		}
		
		$c = new VCard();
		
		foreach($contacts as $contact) {
			$cardData = $c->export($contact);
			
			$blob = $this->createBlob($contact, $cardData);
			
			if(!$contact->save()) {
				throw new Exception("Could not save contact");
			}
		}
	}

	/**
	 * @throws SaveException
	 * @throws Forbidden
	 * @throws NotFound
	 */
	public function getCards($addressBookId): array
	{
		if ($addressBookId == "all") {
			$addressBookId = $this->findAddressBooks()->selectSingleValue('addressBookId');
			$op = 'IN';
		}else {
			$op = '=';
			$addressbook = AddressBook::findById($addressBookId);
			if (!$addressbook) {
				throw new NotFound();
			}

			if (!$addressbook->getPermissionLevel()) {
				throw new Forbidden();
			}
		}
		$this->generateCards($addressBookId);		
		
		return go()->getDbConnection()->select('c.uri, UNIX_TIMESTAMP(c.modifiedAt) as lastmodified, CONCAT(\'"\', vcardBlobId, \'"\') AS etag, b.size')
						->from('addressbook_contact', 'c')
						->join('core_blob', 'b', 'c.vcardBlobId = b.id')
						->where('c.addressBookId', $op, $addressBookId)
						->all();
	}
	

	public function updateAddressBook($addressBookId, PropPatch $propPatch): void {
		
	}

	/**
	 * @param $addressBookId
	 * @param $cardUri
	 * @param $cardData
	 * @return string|null
	 * @throws Forbidden
	 * @throws NotFound
	 * @throws SaveException
	 * @throws Exception
	 */
	public function updateCard($addressBookId, $cardUri, $cardData): ?string
	{
		if ($addressBookId == "all") {
			$addressBookId = $this->findAddressBooks()->selectSingleValue('addressBookId');
		}

		$contact = Contact::find()->where(['addressBookId' => $addressBookId, 'uri' => rawurldecode($cardUri)])->single();
		if(!$contact) {
			throw new NotFound();
		}
		if($contact->getPermissionLevel() < Acl::LEVEL_DELETE) {
			throw new Forbidden();
		}
		
		$blob = $this->createBlob($contact, $cardData);	
		
		try {
			go()->debug($cardData);
			$c = new VCard();			
			$vcardComponent = Reader::read($cardData, Reader::OPTION_FORGIVING + Reader::OPTION_IGNORE_INVALID_LINES);
			/** @var $vcardComponent VCardComp */
			$c->import($vcardComponent, $contact);
			
		} catch(Exception $e) {
			ErrorHandler::logException($e);
			
			return null;
		}
		//vcardBlobId can serve as etag
		return '"' . $contact->vcardBlobId . '"';
	}
}
