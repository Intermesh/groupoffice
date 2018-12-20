<?php

namespace go\modules\community\addressbook\convert;

use Exception;
use GO;
use go\core\acl\model\Acl;
use go\core\data\convert\AbstractConverter;
use go\core\ErrorHandler;
use go\core\fs\Blob;
use go\core\fs\File;
use go\core\orm\Entity;
use go\core\util\DateTime;
use go\core\util\StringUtil;
use go\modules\community\addressbook\model\Address;
use go\modules\community\addressbook\model\Contact;
use go\modules\community\addressbook\model\Date;
use go\modules\community\addressbook\model\EmailAddress;
use go\modules\community\addressbook\model\PhoneNumber;
use go\modules\community\addressbook\model\VCard as VCardModel;
use go\modules\core\links\model\Link;
use Sabre\VObject\Component\VCard as VCardComponent;
use Sabre\VObject\Reader;
use Sabre\VObject\Splitter\VCard as VCardSplitter;

class VCard extends AbstractConverter {

	
	const EMPTY_NAME = '(no name)';

	/**
	 * 
	 * @param Contact $contact
	 * @return VCardComponent
	 */
	private function getVCard(Contact $contact) {
		
		if ($contact->vcardBlobId) {
			//Contact has a stored VCard 
			$blob = Blob::findById($contact->vcardBlobId);
			$vcard = Reader::read($blob->getFile()->open("r"), Reader::OPTION_FORGIVING + Reader::OPTION_IGNORE_INVALID_LINES);			
			
			//remove all supported properties
			$vcard->remove('EMAIL');
			$vcard->remove('TEL');
			$vcard->remove('ADR');
			$vcard->remove('ORG');
			$vcard->remove('PHOTO');

			return $vcard;
		} else {
			//We have to use 3.0 for the photo property :( See https://github.com/sabre-io/vobject/issues/294#issuecomment-231987064
			return new VCardComponent([
					"VERSION" => "3.0",
					"UID" => $contact->uid
			]);
		}
	}

	/**
	 * Parse an Event object to a VObject
	 * @param Contact $contact
	 */
	
	public function export(Entity $contact) {
		
		$vcard = $this->getVCard($contact);

		$vcard->LANGUAGE = GO()->getSettings()->language;
		$vcard->PRODID = '-//Intermesh//NONSGML Group-Office ' . GO()->getVersion() . '//EN';
		
		$vcard->N = [$contact->lastName, $contact->firstName, $contact->middleName, $contact->prefixes, $contact->suffixes];
		$vcard->FN = $contact->name;
		$vcard->REV = $contact->modifiedAt->getTimestamp();

		foreach ($contact->emailAddresses as $emailAddr) {
			$vcard->add('EMAIL', $emailAddr->email, ['TYPE' => [$emailAddr->type]]);
		}
		foreach ($contact->phoneNumbers as $phoneNb) {
			$vcard->add('TEL', $phoneNb->number, ['TYPE' => [$phoneNb->type]]);
		}
		foreach ($contact->dates as $date) {
			$type = ($date->type === 'birthday') ? 'BDAY' : 'ANNIVERSARY';
			$vcard->add($type, $date->date);
		}
		foreach ($contact->addresses as $address) {
			//ADR: [post-office-box, apartment, street, locality, region, postal, country]
			$vcard->add(
							'ADR', ['', $address->street2, $address->street, $address->city, $address->state, $address->zipCode, $address->country], ['TYPE' => [$address->type]]
			);
		}
		if (!$contact->isOrganization) {

			$organications = Contact::find()
							->withLink($contact)
							->andWhere('isOrganization', '=', true)
							->selectSingleValue('name')
							->all();

			foreach ($organications as $org) {
				$vcard->add('ORG', [$org]);
			}
		} else {
			//For apple
			$vcard->{"X-ABShowAs"} = "COMPANY";
		}

		$vcard->NOTE = $contact->notes;
		$vcard->{"X-GO-GENDER"} = $contact->gender;
		

		$blob = isset($contact->photoBlobId) ? Blob::findById($contact->photoBlobId) : false;
		if ($blob && $blob->getFile()->exists()) {
			//Attepted this for vcard 4.0 version
			//$vcard->add('PHOTO', "data:" . $blob->type . ";base64," . base64_encode($blob->getFile()->getContents()));			
			$vcard->add('PHOTO', $blob->getFile()->getContents(), ['TYPE' => $blob->type, 'ENCODING' => 'b']);
		}

		return $vcard->serialize();
	}

	private function importHasMany(array $prop, $vcardProp, $cls, $fn) {

		if (!isset($vcardProp)) {
			return $prop;
		}
		
		foreach ($vcardProp as $index => $value) {
			GO()->debug("Import " . $index . " - ".$value);
			if (!isset($prop[$index])) {
				$prop[$index] = new $cls;
				GO()->debug("NEW!");
			}

			$prop[$index]->type = $this->convertType($value['TYPE']);
			$v = call_user_func($fn, $value);
			GO()->debug($v);
			$prop[$index]->setValues($v);
		}

		$c = count($prop) - 1;
		if ($c > $index) {
			array_splice($prop, $index, $c - $index);
		}
		
		return $prop;
	}

	private function importDate(Contact $contact, $type = Date::TYPE_BIRTHDAY, $date) {

		if (isset($date)) {
			$bday = $contact->findDateByType($type);

			if (!empty($date)) {
				if (!$bday) {
					$bday = new Date();
					$bday->type = $type;
					$contact->dates[] = $bday;
				}
				$bday->date = new DateTime((string) $date);
			} else {
				if ($bday) {
					$contact->dates = array_filter($contact->dates, function($d) use($bday) {
						$d !== $bday;
					});
				}
			}
		}
	}

	/**
	 * Parse a VObject to an Contact object
	 * @param VCardComponent $vcardComponent
	 * @param Contact $contact;
	 * @return Contact[]
	 */
	public function import(VCardComponent $vcardComponent, Entity $entity = null) {

		if ($vcardComponent->VERSION != "3.0") {
			$vcardComponent->convert("3.0");
		}
		
		if (!isset($entity)) {
			$entity = new Contact();
		}
		
		if(!isset($entity->uid) && isset($vcardComponent->uid)) {
			$entity->uid = (string) $vcardComponent->uid;
		}

		if(isset($vcardComponent->{"X_GO-GENDER"})) {
			$gender = (string) $vcardComponent->{"X_GO-GENDER"};
			switch ($gender) {
				case 'M':
				case 'F':
					$entity->gender = $gender;
					break;
				default:
					$entity->gender = null;
			}
		}

		$entity->isOrganization = isset($vcardComponent->{"X-ABShowAs"}) && $vcardComponent->{"X-ABShowAs"} == "COMPANY";

		$n = $vcardComponent->N->getParts();
		empty($n[0]) ?: $entity->lastName = $n[0];
		empty($n[1]) ?: $entity->firstName = $n[1];
		empty($n[2]) ?: $entity->middleName = $n[2];
		empty($n[3]) ?: $entity->prefixes = $n[3];
		empty($n[4]) ?: $entity->suffixes = $n[4];
		$entity->name = (string) $vcardComponent->FN ?? self::EMPTY_NAME;

		$this->importDate($entity, Date::TYPE_BIRTHDAY, $vcardComponent->BDAY);
		$this->importDate($entity, Date::TYPE_ANNIVERSARY, $vcardComponent->ANNIVERSARY);

		empty($vcardComponent->NOTE) ?: $entity->notes = (string) $vcardComponent->NOTE;

		$entity->emailAddresses = $this->importHasMany($entity->emailAddresses, $vcardComponent->EMAIL, EmailAddress::class, function($value) {
			return ['email' => (string) $value];
		});

		$entity->phoneNumbers = $this->importHasMany($entity->phoneNumbers, $vcardComponent->TEL, PhoneNumber::class, function($value) {
			return ['number' => (string) $value];
		});

		$entity->addresses = $this->importHasMany($entity->addresses, $vcardComponent->ADR, Address::class, function($value) {
			$a = $value->getParts();
			$addr = [];
			empty($a[1]) ?: $addr['street2'] = $a[1];
			empty($a[2]) ?: $addr['street'] = $a[2];
			empty($a[3]) ?: $addr['city'] = $a[3];
			empty($a[4]) ?: $addr['state'] = $a[4];
			empty($a[5]) ?: $addr['zipCode'] = $a[5];
			empty($a[6]) ?: $addr['country'] = $a[6];
			return $addr;
		});

		if (isset($vcardComponent->PHOTO)) {
			$vcardComponent = $vcardComponent->PHOTO->getValue();
			if ($vcardComponent) {
				$blob = Blob::fromString($vcardComponent);
				if ($blob->save()) {
					$entity->photoBlobId = $blob->id;
				}
			} else {
				$entity->photoBlobId = null;
			}
		}

		if (!$entity->save()) {
			throw new Exception("Could not save contact");
		}

		$this->importOrganizations($entity, $vcardComponent);
		
		return $entity;
	}

	private static function importOrganizations(Contact $contact, $vcard) {
		if (!isset($vcard->ORG)) {
			return;
		}

		$vcardOrganizationNames = [];
		foreach ($vcard->ORG as $org) {
			$vcardOrganizationNames[] = (string) $parts[0];
		}

		//compare with existing.
		$goOrganizations = $contact->isNew() ? [] : Contact::find()
										->withLink($contact)
										->andWhere('isOrganization', '=', true)
										->all();

		$goOrganizationsNames = [];
		foreach ($goOrganizations as $o) {
			if (!in_array($o->name, $vcardOrganizationNames)) {
				Link::deleteLink($o, $contact);
			} else {
				$goOrganizationsNames[] = $o->name;
			}
		}

		$newVcardOrgNrames = array_diff($vcardOrganizationNames, $goOrganizationsNames);
		foreach ($newAsOrgNames as $name) {
			$org = Contact::find()->where(['isOrganization' => true])->andWhere('name', 'LIKE', $name)->single();
			if (!$org) {
				$org = new Contact();
				$org->name = $name;
				$org->isOrganization = true;
				$org->addressBookId = $contact->addressBookId;
				if (!$org->save()) {
					throw new Exception("Could not save organization");
				}
			}
			$link = Link::create($contact, $org);
			if (!$link) {
				throw new Exception("Could not link organization");
			}
		}
	}

	private static function convertType($vCardType) {
		return explode(',', strtolower((string) $vCardType))[0];
	}

	public function getFileExtension() {
		return 'vcf';
	}

	public function importFile(File $file, $values = []) {

		$response = [
				'ids' => [],
				'errors' => []
		];

		$splitter = new VCardSplitter(StringUtil::cleanUtf8($file->getContents()), Reader::OPTION_FORGIVING + Reader::OPTION_IGNORE_INVALID_LINES);

		while ($vcardComponent = $splitter->getNext()) {			
			try {
				$contact = $this->findOrCreateContact($vcardComponent, $values['addressBookId']);
				$contact->setValues($values);
				$this->import($vcardComponent, $contact);
				$response['ids'][] = $contact->id;
			}
			catch(\Exception $e) {
				ErrorHandler::logException($e);
				$response['errors'][] = "Failed to import card: ". $e->getMessage();
			}			
		}

		return $response;
	}
	
	/**
	 * 
	 * @param VCardComponent $vcardComponent
	 * @param int $addressBookId
	 * @return Contact
	 */
	private function findOrCreateContact(VCardComponent $vcardComponent, $addressBookId) {
		$contact = false;
			if(isset($vcard->uid)) {
				$contact = Contact::find()->where(['addressBookId' => $addressBookId, 'uid' => (string) $vcardComponent->uid])->single();
			}
			
			if(!$contact) {
				$contact = new Contact();				
			}
			
			//Serialize data to store vcard
			$blob = $this->saveBlob($vcardComponent);			
			$contact->vcardBlobId = $blob->id;
			
			return $contact;
	}
	/**
	 * 
	 * @param string $vcardData
	 * @return Blob
	 * @throws Exception
	 */
	private function saveBlob($vcardComponent){
		$blob = \go\core\fs\Blob::fromString($vcardComponent->serialize());
		$blob->type = 'text/vcard';
		$blob->name = ($vcardComponent->uid ?? 'nouid' ) . '.vcf';
		if(!$blob->save()) {
			throw new \Exception("could not save vcard blob");
		}
		
		return $blob;
	}

}
