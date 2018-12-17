<?php
namespace go\modules\community\addressbook\convert;

use GO;
use go\core\data\convert\AbstractConverter;
use go\core\fs\Blob;
use go\core\orm\Entity;
use go\core\util\StringUtil;
use go\modules\community\addressbook\model\Contact;
use Sabre\VObject\Component\VCard as VCardComponent;
use Sabre\VObject\Document;
use Sabre\VObject\Reader;
use Sabre\VObject\Splitter\VCard as VCardSplitter;

class VCard extends AbstractConverter {

	const PRODID = '-//Intermesh//NONSGML Group-Office //EN';
	const EMPTY_NAME = '(no name)';
	
	private static function createUid(Contact $contact) {
		$url = trim(GO()->getSettings()->URL, '/');
		$uid = substr($url, strpos($url, '://') + 3);
		$uid = str_replace('/', '-', $uid);
		
		return $contact->id .'@' . $uid;
	}
	
	private $vcard;
	
	public function setVCard(VCardComponent $vcard) {
		$this->vcard = $vcard;
	}

	/**
	 * Parse an Event object to a VObject
	 * @param Contact $contact
	 */
	public function export(Entity $contact) {

		if (!isset($this->vcard)) {
			//We have to use 3.0 for the photo property :( See https://github.com/sabre-io/vobject/issues/294#issuecomment-231987064
			$vcard = new VCardComponent([
					"VERSION" => "3.0"
			]);
		} else {
			$vcard = $this->vcard;
			unset($this->vcard);
			
			if($vcard->version != "3.0") {
				$vcard->convert("3.0");
			}
			//remove all supported properties
			$vcard->remove('EMAIL');
			$vcard->remove('TEL');
			$vcard->remove('ADR');
			$vcard->remove('ORG');
//			$vcard->remove('CATEGORIES');
			$vcard->remove('PHOTO');
		}

		$vcard->uid = self::createUid($contact);
		$vcard->LANGUAGE = GO()->getSettings()->language;
		$vcard->PRODID = self::PRODID;
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
							'ADR', ['', $address->street2, $address->street, $address->city, $address->state, $address->zipCode, $address->country], // @todo country must be full name
							['TYPE' => [$address->type]]
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
		$vcard->GENDER = $contact->gender;

		$blob = isset($contact->photoBlobId) ? Blob::findById($contact->photoBlobId) : false;
		if($blob && $blob->getFile()->exists()) {			
			//Attepted this for vcard 4.0 version
			//$vcard->add('PHOTO', "data:" . $blob->type . ";base64," . base64_encode($blob->getFile()->getContents()));			
			$vcard->add('PHOTO', $blob->getFile()->getContents(), ['TYPE' => $blob->type, 'ENCODING' => 'b']);
		}

		return $vcard->serialize();
	}

	/**
	 * Parse a VObject to an Contact object
	 * @param VCardComponent $data
	 * @param Contact $contact;
	 * @return Contact[]
	 */
	public function import($data, Entity $entity = null) {

		if (!isset($entity)) {
			$entity = new Contact();
		}
		
		
		switch((string) $data->gender) {
			case 'M':
			case 'F':
				$entity->gender = (string) $data->gender;
			break;
			default:
				$entity->gender = null;
		}
		
		$entity->isOrganization = isset($data->{"X-ABShowAs"}) && $data->{"X-ABShowAs"} == "COMPANY";
							

		$n = $data->N->getParts();
		empty($n[0]) ?: $entity->lastName = $n[0];
		empty($n[1]) ?: $entity->firstName = $n[1];
		empty($n[2]) ?: $entity->middleName = $n[2];
		empty($n[3]) ?: $entity->prefixes = $n[3];
		empty($n[4]) ?: $entity->suffixes = $n[4];
		$entity->name = (string) $data->FN ?? self::EMPTY_NAME;

		$dates = [];
		empty($data->BDAY) ?: $dates[] = ['date' => $data->BDAY, 'type' => 'birthday'];
		empty($data->ANNIVERSARY) ?: $dates[] = ['date' => $data->ANNIVERSARY, 'type' => 'anniversary'];

//		$entity->dates->replace($dates);

		empty($data->NOTE) ?: $entity->notes = (string) $data->NOTE;

//		self::mergeRelation($entity->phoneNumbers, $data->TEL, function($value) {
//			return ['number' => (string) $value, 'type' => self::convertType($value['TYPE'])];
//		});
//
//		self::mergeRelation($entity->emailAddresses, $data->EMAIL, function($value) {
//			return ['email' => (string) $value, 'type' => self::convertType($value['TYPE'])];
//		});

		//TODO CATEGORIES -> tags

		$orgAdr = [];
		$contactAdr = [];
		if(isset($data->ADR)) {
			foreach ($data->ADR as $adr) {
				if (stristr($adr['TYPE'], 'work')) {
					$orgAdr[] = $adr;
				} else {
					$contactAdr[] = $adr;
				}
			}
		}

//		self::mergeRelation($entity->addresses, $contactAdr, function($value) {
//			$a = $value->getParts();
//			$addr = ['type' => self::convertType($value['TYPE'])];
//			empty($a[2]) ?: $addr['street'] = $a[2];
//			empty($a[3]) ?: $addr['city'] = $a[3];
//			empty($a[4]) ?: $addr['state'] = $a[4];
//			empty($a[5]) ?: $addr['zipCode'] = $a[5];
//			empty($a[6]) ?: $addr['country'] = $a[6];
//			return $addr;
//		});
//
//		$org = self::mergeOrg($entity, $data);
//
//		if($org && $org->isNew()) {
//			self::mergeRelation($org->addresses, $orgAdr, function($value) {
//				$a = $value->getParts();
//				$addr = ['type' => self::convertType($value['TYPE'])];
//				empty($a[2]) ?: $addr['street'] = $a[2];
//				empty($a[3]) ?: $addr['city'] = $a[3];
//				empty($a[4]) ?: $addr['state'] = $a[4];
//				empty($a[5]) ?: $addr['zipCode'] = $a[5];
//				empty($a[6]) ?: $addr['country'] = $a[6];
//				return $addr;
//			});
//		}


		if (isset($data->PHOTO)) {
			$data = $data->PHOTO->getValue();
			if($data) {
				$blob = Blob::fromString($data);
				if ($blob->save()) {
					$entity->photoBlobId = $blob->blobId;
				}
			} else
			{
				$entity->photoBlobId = null;
			}
		}

		return $entity;
	}

	private static function mergeOrg(Contact $contact, $vcard) {
		if (empty($vcard->ORG)) {
			return;
		}

		foreach ($vcard->ORG as $org) {
			$parts = $org->getParts();
			$org = Contact::find(['name' => $parts[0], 'isOrganization' => true])->single();

			if (!$org) {
				$org = new Contact();
				$org->isOrganization = true;
				$org->name = $parts[0];
			}

			$contact->organizations[] = $org;
		}

		return $org;
	}

	private static function mergeRelation(\IFW\Orm\RelationStore $store, $vcardProp, $fn) {

		$vcardCount = isset($vcardProp) ? count($vcardProp) : 0;
		$contactCount = count($store->all());
		//remove emails
		for ($i = $vcardCount; $i < $contactCount; $i++) {
			$store[$i]->markDeleted = true;
		}

		if (isset($vcardProp)) {
			foreach ($vcardProp as $index => $value) {
				$rel = call_user_func($fn, $value);
				$store[$index] = $rel;
			}
		}
	}

	private static function convertType($vCardType) {
		return str_replace(',', ', ', strtolower((string) $vCardType));
	}


	public function getFileExtension() {
		return 'vcf';
	}

	public function importFile(\go\core\fs\File $file, $values = []){
		
		$response = [
				'ids' => [],
				'errors' => []
		];
		
		$splitter = new VCardSplitter(StringUtil::cleanUtf8($file->getContents()), Reader::OPTION_FORGIVING + Reader::OPTION_IGNORE_INVALID_LINES);
		
		while($vcard = $splitter->getNext()) {
			$contact = $this->import($vcard);
			$contact->setValues($values);
			if(!$contact->save()) {
				$response['errors'][] = "Failed to save contact";
			} else
			{
				$response['ids'][] = $contact->id;
			}
		}
		
		return $response;
	}

}