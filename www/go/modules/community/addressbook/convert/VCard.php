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

	
	const EMPTY_NAME = '(no name)';

	private static function createUid(Contact $contact) {
		$url = trim(GO()->getSettings()->URL, '/');
		$uid = substr($url, strpos($url, '://') + 3);
		$uid = str_replace('/', '-', $uid);

		return $contact->id . '@' . $uid;
	}

	/**
	 * 
	 * @param Contact $contact
	 * @return VCardComponent
	 */
	public function getVCard(Contact $contact, $vcardModel) {
		
		if ($vcardModel) {
			$vobject = $vcardModel->toVObject();
			//remove all supported properties
			$vobject->remove('EMAIL');
			$vobject->remove('TEL');
			$vobject->remove('ADR');
			$vobject->remove('ORG');
//			$vobject->remove('CATEGORIES');
			$vobject->remove('PHOTO');

			return $vobject;
		} else {
			//We have to use 3.0 for the photo property :( See https://github.com/sabre-io/vobject/issues/294#issuecomment-231987064
			return new VCardComponent([
					"VERSION" => "3.0",
					"UID" => self::createUid($contact)
			]);
		}
	}

	/**
	 * Parse an Event object to a VObject
	 * @param Contact $contact
	 */
	public function export(Entity $contact) {

		$vcardModel = \go\modules\community\addressbook\model\VCard::findById($contact->id);
		$vcard = $this->getVCard($contact, $vcardModel);

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

		return $this->saveCard($contact, $vcard, $vcardModel);
	}

	private function importHasMany(array &$prop, $vcardProp, $cls, $fn) {

		if (!isset($vcardProp)) {
			return;
		}

		foreach ($vcardProp as $index => $value) {
			if (!isset($prop->phoneNumbers[$index])) {
				$prop[$index] = new $cls;
			}

			$prop[$index]->type = $this->convertType($value['TYPE']);
			;
			$prop[$index]->setValues(call_user_func($fn, $value));
		}

		$c = count($prop) - 1;
		if ($c > $index) {
			array_splice($prop, $index, $c - $index);
		}
	}

	private function importDate(Contact $contact, $type = \go\modules\community\addressbook\model\Date::TYPE_BIRTHDAY, $date) {

		if (isset($date)) {
			$bday = $contact->findDateByType($type);

			if (!empty($date)) {
				if (!$bday) {
					$bday = new \go\modules\community\addressbook\model\Date();
					$bday->type = $type;
					$contact->dates[] = $bday;
				}
				$bday->date = new \go\core\util\DateTime((string) $date);
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
	 * @param VCardComponent $data
	 * @param Contact $contact;
	 * @return Contact[]
	 */
	public function import($data, Entity $entity = null) {

		if ($data->VERSION != "3.0") {
			$data->convert("3.0");
		}
		
		if (!isset($entity)) {
			$entity = new Contact();
		}

		if(isset($data->{"X_GO-GENDER"})) {
			$gender = (string) $data->{"X_GO-GENDER"};
			switch ($gender) {
				case 'M':
				case 'F':
					$entity->gender = $gender;
					break;
				default:
					$entity->gender = null;
			}
		}

		$entity->isOrganization = isset($data->{"X-ABShowAs"}) && $data->{"X-ABShowAs"} == "COMPANY";

		$n = $data->N->getParts();
		empty($n[0]) ?: $entity->lastName = $n[0];
		empty($n[1]) ?: $entity->firstName = $n[1];
		empty($n[2]) ?: $entity->middleName = $n[2];
		empty($n[3]) ?: $entity->prefixes = $n[3];
		empty($n[4]) ?: $entity->suffixes = $n[4];
		$entity->name = (string) $data->FN ?? self::EMPTY_NAME;

		$this->importDate($entity, \go\modules\community\addressbook\model\Date::TYPE_BIRTHDAY, $data->BDAY);
		$this->importDate($entity, \go\modules\community\addressbook\model\Date::TYPE_ANNIVERSARY, $data->ANNIVERSARY);

		empty($data->NOTE) ?: $entity->notes = (string) $data->NOTE;

		$this->importHasMany($entity->emailAddresses, $data->EMAIL, \go\modules\community\addressbook\model\EmailAddress::class, function($value) {
			return ['email' => (string) $value];
		});

		$this->importHasMany($entity->phoneNumbers, $data->TEL, \go\modules\community\addressbook\model\PhoneNumber::class, function($value) {
			return ['number' => (string) $value];
		});

		$this->importHasMany($entity->addresses, $data->ADR, \go\modules\community\addressbook\model\Address::class, function($value) {
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

		if (isset($data->PHOTO)) {
			$data = $data->PHOTO->getValue();
			if ($data) {
				$blob = Blob::fromString($data);
				if ($blob->save()) {
					$entity->photoBlobId = $blob->id;
				}
			} else {
				$entity->photoBlobId = null;
			}
		}

		if (!$entity->save()) {
			throw new \Exception("Could not save contact");
		}

		$this->importOrganizations($entity, $data);
		
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

	public function importFile(\go\core\fs\File $file, $values = []) {

		$response = [
				'ids' => [],
				'errors' => []
		];

		$splitter = new VCardSplitter(StringUtil::cleanUtf8($file->getContents()), Reader::OPTION_FORGIVING + Reader::OPTION_IGNORE_INVALID_LINES);

		while ($vcardComponent = $splitter->getNext()) {
			
			if($vcardComponent->uid) {
				$vcardModel = \go\modules\community\addressbook\model\VCard::find()
								->filter(['permissionLevel' => \go\core\acl\model\Acl::LEVEL_READ])
								->where(['uid' => (string) $vcardComponent->uid])->single();
			} else
			{
				$vcardModel = false;
			}
			
			if($vcardModel) {
				$contact = Contact::findById($vcardModel->contactId);
			}else {
				$contact = new Contact();
				$contact->setValues($values);
			}

			GO()->getDbConnection()->beginTransaction();
			try {
				$contact = $this->import($vcardComponent, $contact);
				$this->saveCard($contact, $vcardComponent, $vcardModel);

				GO()->getDbConnection()->commit();

				$response['ids'][] = $contact->id;
			} catch (\Exception $e) {

				GO()->getDbConnection()->rollBack();
				\go\core\ErrorHandler::logException($e);

				$response['errors'][] = "Failed to save contact";
			}
		}

		return $response;
	}

	private function saveCard(Contact $contact, $vcardComponent, $vcardModel) {
//		$vcModel = \go\modules\community\addressbook\model\VCard::findById($contact->id);
		if (!$vcardModel) {
			$vcardModel = new \go\modules\community\addressbook\model\VCard();
			$vcardModel->uid = (string) $vcardComponent->UID ?? $this->createUid($contact);
			$vcardModel->contactId = $contact->id;
		}
		$vcardModel->data = $vcardComponent->serialize();

		if (!$vcardModel->save()) {
			throw new \Exception("Could not save card");
		}
		
		
		return $vcardModel->data;
	}

}
