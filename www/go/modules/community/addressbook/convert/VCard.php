<?php
namespace go\modules\community\addressbook\convert;

use Exception;
use GO;
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
use go\core\model\Link;
use Sabre\VObject\Component\VCard as VCardComponent;
use Sabre\VObject\Reader;
use Sabre\VObject\Splitter\VCard as VCardSplitter;

/**
 * VCard converter
 * 
 * Converts contacts from and to vCard 3.0 format files.
 * 
 * When importing it also keeps the original vCard data.
 */
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
			$file = $blob->getFile();
			if($file->exists()) {
				$vcard = Reader::read($file->open("r"), Reader::OPTION_FORGIVING + Reader::OPTION_IGNORE_INVALID_LINES);

				//remove all supported properties
				$vcard->remove('EMAIL');
				$vcard->remove('TEL');
				$vcard->remove('ADR');
				$vcard->remove('ORG');
				$vcard->remove('PHOTO');
				$vcard->remove('BDAY');
				$vcard->remove('ANNIVERSARY');

				return $vcard;
			}
		}

		//We have to use 3.0 for the photo property :( See https://github.com/sabre-io/vobject/issues/294#issuecomment-231987064
		return new VCardComponent([
				"VERSION" => "3.0",
				"UID" => $contact->getUid()
		]);
	}

	/**
	 * Parse an Event object to a VObject
	 * @param Contact $contact
	 */
	
	public function export(Entity $contact) {
		
		$vcard = $this->getVCard($contact);

		$vcard->LANGUAGE = go()->getSettings()->language;
		$vcard->PRODID = '-//Intermesh//NONSGML Group-Office ' . go()->getVersion() . '//EN';
		
		$vcard->N = $contact->isOrganization ? [$contact->name] : [$contact->lastName, $contact->firstName, $contact->middleName, $contact->prefixes, $contact->suffixes];
		$vcard->FN = $contact->name;
		$vcard->REV = $contact->modifiedAt->getTimestamp();
		$vcard->TITLE = $contact->jobTitle;

		foreach ($contact->emailAddresses as $emailAddr) {
			$vcard->add('EMAIL', $emailAddr->email, ['TYPE' => [$emailAddr->type]]);
		}
		foreach ($contact->phoneNumbers as $phoneNb) {
			$vcard->add('TEL', $phoneNb->number, ['TYPE' => [$phoneNb->type]]);
		}
		$bdayAdded = false;
		$anniversaryAdded = false;
		foreach ($contact->dates as $date) {
			if($date->type === Date::TYPE_BIRTHDAY){
				if($bdayAdded) {
					continue;
				}
				$type = 'BDAY';
				$bdayAdded = true;
			} else {
				if($anniversaryAdded) {
					continue;
				}
				$type = 'ANNIVERSARY';
				$anniversaryAdded = true;
			}

			//Some databases have '0000-00-00' in the date??
			if(isset($date->date)) {
				$vcard->add($type, $date->date->format('Y-m-d'));
			}
		}
		foreach ($contact->addresses as $address) {
			//ADR: [post-office-box, apartment, street, locality, region, postal, country]
			$vcard->add(
							'ADR', ['', $address->street2, $address->street, $address->city, $address->state, $address->zipCode, $address->country], ['TYPE' => [$address->type]]
			);
		}
		if (!$contact->isOrganization) {
			$org = $this->exportOrganization($contact);
			if(isset($org)) {
				$vcard->add('ORG', [$org]);
			}
		} else {
			//For apple
			$vcard->{"X-ABShowAs"} = "COMPANY";
			$vcard->{"X-GO-IS-ORGANIZATION"} = 1;
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
	
	private function exportOrganization(Contact $contact) {
		
			$organizations = Contact::find()
							->withLink($contact)
							->andWhere('isOrganization', '=', true)
							->selectSingleValue('name')
							->all();
			$count = count($organizations);
			switch($count) {
				case 0:
					return null;
				case 1:
					return $organizations[0];
								
				default:
					$str = "";
					for($i = 0; $i < $count; $i++) {
						$str .= '[' . ($i + 1) . '] '.$organizations[$i].' ';
					}
					return trim($str);
			}
	}
	
	protected function exportEntity(Entity $entity, $fp, $index, $total) {
		$str = $this->export($entity);
		fputs($fp, $str);
	}

	/**
	 * 
	 * @param array $prop
	 * @param \Sabre\VObject\Property  $vcardProp
	 * @param string $cls
	 * @param function $fn
	 * @return \go\modules\community\addressbook\convert\cls
	 */
	private function importHasMany(array $prop, $vcardProp, $cls, $fn) {

		if (isset($vcardProp)) {		
			foreach ($vcardProp as $index => $value) {
				if (!isset($prop[$index])) {
					$prop[$index] = new $cls;
				}

				$prop[$index]->type = $this->convertType($value['TYPE']);
				$v = call_user_func($fn, $value);
				$prop[$index]->setValues($v);
			}
			$index++;
		}else
		{
			$index = 0;
		}
		
		
		$c = count($prop);
		if ($c > $index) {
			array_splice($prop, $index, $c - $index);
		}
		
		return $prop;
	}

	private function importDate(Contact $contact, $type, $date) {
			
		$bday = $contact->findDateByType($type, false);

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

	/**
	 * Parse a VObject to an Contact object
	 * @param VCardComponent $vcardComponent
	 * @param Contact $entity
	 * @return Contact[]
	 */
	public function import(VCardComponent $vcardComponent, Entity $entity = null) {
		if ($vcardComponent->VERSION != "3.0") {
			$vcardComponent->convert(\Sabre\VObject\Document::VCARD30);
		}
		
		if (!isset($entity)) {
			$entity = new Contact();
		}
		
		if(!$entity->hasUid() && isset($vcardComponent->uid)) {
			$entity->setUid((string) $vcardComponent->uid);
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

		if(isset($vcardComponent->{"X-ABShowAs"})) {
			$entity->isOrganization = $vcardComponent->{"X-ABShowAs"} == "COMPANY";
		}
		
		if(isset($vcardComponent->{"X-GO-IS-ORGANIZATION"})) {
			$entity->isOrganization = !empty($vcardComponent->{"X-GO-IS-ORGANIZATION"});
		}

		$n = $vcardComponent->N->getParts();
		$entity->lastName = $n[0] ?? null;
		$entity->firstName = $n[1] ?? null;
		$entity->middleName = $n[2] ?? null;
		$entity->prefixes = $n[3] ?? null;
		$entity->suffixes = $n[4] ?? null;
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
			
			//iOS Accepts street2 but sends back {street}\n{street2} in the street value :(
			if(empty($a[1])){
				$parts = explode("\n", $a[2]);
				if(count($parts) > 1) {
					$a[1] = array_pop($parts);
					$a[2] = implode("\n", $parts);
				}
			}			
			
			$addr['street2'] = $a[1] ?? null;
			$addr['street'] = $a[2] ?? null;
			$addr['city'] = $a[3] ?? null;
			$addr['state'] = $a[4] ?? null;
			$addr['zipCode'] = $a[5] ?? null;
			$addr['country'] = $a[6] ?? null;
			return $addr;
		});

		$this->importPhoto($entity, $vcardComponent);

		if (!$entity->save()) {
			throw new Exception("Could not save contact");
		}

		if(!$entity->isOrganization) {
			$this->importOrganizations($entity, $vcardComponent);
		}
		
		return $entity;
	}
	
	private function importPhoto(Contact $entity, VCardComponent $vcardComponent) {
		$vcardComponent = isset($vcardComponent->PHOTO) ? $vcardComponent->PHOTO->getValue() : null;
		if ($vcardComponent) {
			$blob = Blob::fromString($vcardComponent);
			$blob->type = 'image/jpeg';
			$blob->name = $entity->getUid() . '.jpg';
			if ($blob->save()) {
				$entity->photoBlobId = $blob->id;
			}
		} else {
			$entity->photoBlobId = null;
		}
	}

	private function getVCardOrganizations($vcard) {
		$vcardOrganizationNames = [];
		if(isset($vcard->ORG)) {
			foreach ($vcard->ORG as $org) {
				$vcardOrganizationNames = array_merge($vcardOrganizationNames, $this->splitOrganizationName((string) $org->getParts()[0]));
			}
		}
		
		return $vcardOrganizationNames;
	}
	
	/**
	 * Because iOS (or more?) clients only support one "ORG" element allthough
	 * the spec says cardinality *. We put multiple organizations in this format:
	 * 
	 * [1] Company A [2] Company B
	 * 
	 * We detect this syntax on import.
	 * 
	 * @param type $name
	 * @return type
	 */
	private function splitOrganizationName($name) {
		if(preg_match_all('/\[[0-9]+] ([^\[]*)/', $name, $matches)){
			return array_map('trim', $matches[1]);
		}
		
		return [$name];
	}
	
	private function importOrganizations(Contact $contact, $vcard) {		
		
		$vcardOrganizationNames = $this->getVCardOrganizations($vcard);
		
		go()->debug($vcardOrganizationNames);

		//compare with existing.
		$goOrganizations = $contact->isNew() ? [] : Contact::find()
										->withLink($contact)
										->andWhere('isOrganization', '=', true)
										->all();

		$goOrganizationsNames = [];
		foreach ($goOrganizations as $o) {
			if (!in_array($o->name, $vcardOrganizationNames)) {
				if(!Link::deleteLink($o, $contact)) {
					throw new \Exception("Could not unlink organization " . $o->name);
				}
			} else {
				$goOrganizationsNames[] = $o->name;
			}
		}
		
		go()->debug($goOrganizationsNames);

		$newVcardOrgNames = array_diff($vcardOrganizationNames, $goOrganizationsNames);
		foreach ($newVcardOrgNames as $name) {
			$org = Contact::find()->where(['isOrganization' => true])->andWhere('name', 'LIKE', $name)->single();
			if (!$org) {
				go()->debug("Create org: " . $name);
				$org = new Contact();
				$org->name = $name;
				$org->isOrganization = true;
				$org->addressBookId = $contact->addressBookId;
				if (!$org->save()) {
					throw new Exception("Could not save organization");
				}
			}
			
			go()->debug("Link org: " . $org->name);
			$link = Link::create($contact, $org);
			if (!$link) {
				throw new Exception("Could not link organization");
			}
		}
	}

	private static function convertType($vCardType) {
		$types = explode(',', strtolower((string) $vCardType));
		foreach($types as $type) {
			
			//skip internet type.
			if($type != 'internet') {
				return $type;
			}
		}
	}

	public function getFileExtension() {
		return 'vcf';
	}
	
	protected function importEntity($entityClass, $fp, $index, array $params) {
		//not needed because of import file override
	}

	public function importFile(File $file, $entityClass, $params = []) {

		$response = [
				'ids' => [],
				'errors' => [],
				'count' => 0
		];
		
		$values = $params['values'] ?? [];
		
		if(!isset($values['addressBookId'])) {
			$values['addressBookId'] = go()->getAuthState()->getUser(['addressBookSettings'])->addressBookSettings->getDefaultAddressBookId();
		}

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
			$response['count']++;
		}

		return $response;
	}

	/**
	 *
	 * @param VCardComponent $vcardComponent
	 * @param int $addressBookId
	 * @return Contact
	 * @throws \ReflectionException
	 */
	private function findOrCreateContact(VCardComponent $vcardComponent, $addressBookId) {
		$contact = false;
			if(isset($vcardComponent->uid)) {
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
	 * @param VCardComponent $vcardComponent
	 * @return Blob
	 * @throws Exception
	 */
	private function saveBlob(VCardComponent $vcardComponent){
		$blob = Blob::fromString($vcardComponent->serialize());
		$blob->type = 'text/vcard';
		$blob->name = ($vcardComponent->uid ?? 'nouid' ) . '.vcf';
		if(!$blob->save()) {
			throw new \Exception("could not save vcard blob");
		}
		
		return $blob;
	}

	/**
	 * @inheritDoc
	 */
	public static function supportedExtensions()
	{
		return ['vcf'];
	}
}
