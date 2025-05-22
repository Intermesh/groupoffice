<?php /** @noinspection PhpUndefinedFieldInspection */

namespace go\modules\community\addressbook\convert;

use Exception;
use go\core\data\convert\AbstractConverter;
use go\core\ErrorHandler;
use go\core\fs\Blob;
use go\core\fs\File;
use go\core\orm\Entity;
use go\core\orm\exception\SaveException;
use go\core\orm\Property as OrmProperty;
use go\core\util\DateTime;
use go\core\util\StringUtil;
use go\modules\community\addressbook\model\Address;
use go\modules\community\addressbook\model\Contact;
use go\modules\community\addressbook\model\Date;
use go\modules\community\addressbook\model\EmailAddress;
use go\modules\community\addressbook\model\PhoneNumber;
use go\core\model\Link;
use Sabre\VObject\Component\VCard as VCardComponent;
use Sabre\VObject\Document as SabreDocument;
use Sabre\VObject\ParseException;
use Sabre\VObject\Property;
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
	/**
	 * @var File
	 */
	private $tempFile;
	/**
	 * @var resource
	 */
	private $fp;

	public function __construct()
	{
		parent::__construct('vcf', Contact::class);
	}

	/**
	 *
	 * @param Contact $contact
	 * @return VCardComponent
	 * @throws Exception
	 */
	private function getVCard(Contact $contact): VCardComponent
	{
		if ($contact->vcardBlobId) {
			//Contact has a stored VCard 
			$blob = Blob::findById($contact->vcardBlobId);
			$file = $blob->getFile();
			if($file->exists()) {
				try {
					$vcard = Reader::read($file->open("r"), Reader::OPTION_FORGIVING + Reader::OPTION_IGNORE_INVALID_LINES);
					/** @var $vcard VCardComponent */
					if ($vcard->VERSION != "4.0") {
						// we can only use 3.0 for photo's somehow :( See https://github.com/sabre-io/vobject/issues/294#issuecomment-231987064
						$vcard = $vcard->convert(SabreDocument::VCARD40);
					}

					//remove all supported properties
					$vcard->remove('EMAIL');
					$vcard->remove('TEL');
					$vcard->remove('ADR');
					$vcard->remove('ORG');
					$vcard->remove('PHOTO');
					$vcard->remove('BDAY');
					$vcard->remove('ANNIVERSARY');

					return $vcard;
				} catch(Exception $e) {
					ErrorHandler::log("Broken vcard for contact with id = " .$contact->id .' in file ' . $file->getPath());
					ErrorHandler::logException($e);
				}
			}
		}

		return new VCardComponent([
				"VERSION" => "4.0",
				"UID" => $contact->getUid()
		]);
	}

	/**
	 * Parse an Event object to a VObject
	 * @param Contact $contact
	 * @throws Exception
	 */
	
	public function export(Entity $contact): string
	{

		$vcard = $this->getVCard($contact);

		$vcard->LANGUAGE = go()->getSettings()->language;
		$vcard->PRODID = '-//Intermesh//NONSGML Group-Office ' . go()->getVersion() . '//EN';

		$vcard->N = $contact->isOrganization ? [$contact->name] : [$contact->lastName, $contact->firstName, $contact->middleName, $contact->prefixes, $contact->suffixes];
		$vcard->FN = $contact->name;
		$vcard->REV = $contact->modifiedAt->format("Ymd\THis\Z");
		$vcard->TITLE = (string) $contact->jobTitle;

		foreach ($contact->emailAddresses as $emailAddr) {
			$vcard->add('EMAIL', $emailAddr->email, ['TYPE' => [$emailAddr->type ?? ""]]);
		}
		foreach ($contact->phoneNumbers as $phoneNb) {
			$vcard->add('TEL', $phoneNb->number, ['TYPE' => [$phoneNb->type ?? ""]]);
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
							'ADR', ['', '', $address->address, $address->city, $address->state, $address->zipCode, $address->country], ['TYPE' => [$address->type ?? ""]]
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

		$vcard->NOTE = (string) $contact->notes;
		$vcard->{"X-GO-GENDER"} = (string) $contact->gender;


		$blob = isset($contact->photoBlobId) ? Blob::findById($contact->photoBlobId) : false;
		if ($blob && $blob->getFile()->exists()) {
			// vCard v4 version
			$vcard->add('PHOTO', "data:" . $blob->type . ";base64," . base64_encode($blob->getFile()->getContents()));
			// vCard v3 version
			//$vcard->add('PHOTO', $blob->getFile()->getContents(), ['TYPE' => $blob->type, 'ENCODING' => 'b']);
		}

		return $vcard->serialize();
	}

	/**
	 * @throws Exception
	 */
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

	protected function initExport(): void
	{
		$this->tempFile = File::tempFile($this->getFileExtension());
		$this->fp = $this->tempFile->open('w+');
	}

	/**
	 * @throws Exception
	 */
	protected function exportEntity(Entity $entity): void
	{
		$str = $this->export($entity);
		fputs($this->fp, $str);
	}

	/**
	 * @throws Exception
	 * @noinspection DuplicatedCode
	 */
	protected function finishExport(): Blob
	{
		$cls = $this->entityClass;
		$blob = Blob::fromTmp($this->tempFile);
		$blob->name = $cls::entityType()->getName() . "-" . date('Y-m-d-H:i:s') . '.'. $this->getFileExtension();
		if(!$blob->save()) {
			throw new Exception("Couldn't save blob: " . var_export($blob->getValidationErrors(), true));
		}

		return $blob;
	}

	/**
	 *
	 * @param Contact $entity
	 * @param array $prop
	 * @param Property|null $vcardProp
	 * @param class-string<OrmProperty> $cls
	 * @param callable $fn
	 * @return OrmProperty[]
	 */
	private function importHasMany(Contact $entity, array $prop, ?Property $vcardProp, string $cls, callable $fn): array {
		$importCount = 0;
		if (!empty($vcardProp)) {
			foreach ($vcardProp as $value) {
				$v = call_user_func($fn, $value);
				if(!$v) {
					continue;
				}
				if (!isset($prop[$importCount])) {
					$prop[$importCount] = new $cls($entity);
				}

				/** @noinspection PhpPossiblePolymorphicInvocationInspection */
				$prop[$importCount]->type = $this->convertType((string) $value['TYPE']);

				$prop[$importCount]->setValues($v);
				$importCount++;
			}

		}

		$index = $importCount + 1;
		$c = count($prop);
		if ($c > $index) {
			array_splice($prop, $index, $c - $index);
		}
		
		return $prop;
	}

	/**
	 * @throws Exception
	 */
	private function importDate(Contact $contact, string $type, ?Property $date) {
			
		$bday = $contact->findDateByType($type, false);

		if (!empty($date)) {
			if (!$bday) {
				$bday = new Date($contact);
				$bday->type = $type;
				$contact->dates[] = $bday;
			}
			$bday->date = new DateTime((string) $date);
		} else {
			if ($bday) {
				$contact->dates = array_filter($contact->dates, function($d) use($bday) {
					return $d !== $bday;
				});
			}
		}
		
	}

	/**
	 * Parse a VObject to an Contact object
	 * @param VCardComponent $vcardComponent
	 * @param Entity|null $entity
	 * @return Contact
	 * @throws Exception
	 * @noinspection PhpCastIsUnnecessaryInspection
	 */
	public function import(VCardComponent $vcardComponent, Entity $entity = null): Contact
	{
		if ($vcardComponent->VERSION != "3.0") {
			$vcardComponent = $vcardComponent->convert(SabreDocument::VCARD30);
		}

		if (!isset($entity)) {
			$entity = new Contact();
		}

		if (!$entity->hasUid() && isset($vcardComponent->uid)) {
			$entity->setUid((string)$vcardComponent->uid);
		}

		if (isset($vcardComponent->{"X_GO-GENDER"})) {
			$gender = (string)$vcardComponent->{"X_GO-GENDER"};
			switch ($gender) {
				case 'M':
				case 'F':
					$entity->gender = $gender;
					break;
				default:
					$entity->gender = null;
			}
		}

		if(!empty($vcardComponent->title)) {
			$entity->jobTitle = (string) $vcardComponent->title;
		}

		if (isset($vcardComponent->{"X-ABShowAs"})) {
			$entity->isOrganization = $vcardComponent->{"X-ABShowAs"} == "COMPANY";
		}

		if (isset($vcardComponent->{"X-GO-IS-ORGANIZATION"})) {
			$entity->isOrganization = !empty($vcardComponent->{"X-GO-IS-ORGANIZATION"});
		}

		if (isset($vcardComponent->N)) {
			$n = $vcardComponent->N->getParts();
			$entity->lastName = $n[0] ?? null;
			$entity->firstName = $n[1] ?? null;
			$entity->middleName = $n[2] ?? null;
			$entity->prefixes = $n[3] ?? null;
			$entity->suffixes = $n[4] ?? null;
		}

		if (isset($vcardComponent->FN)) {
			$entity->name = (string) $vcardComponent->FN;
		}

		$this->importDate($entity, Date::TYPE_BIRTHDAY, $vcardComponent->BDAY);
		$this->importDate($entity, Date::TYPE_ANNIVERSARY, $vcardComponent->ANNIVERSARY);

		empty($vcardComponent->NOTE) ?: $entity->notes = (string) $vcardComponent->NOTE;
		$entity->emailAddresses = $this->importHasMany($entity, $entity->emailAddresses, $vcardComponent->EMAIL, EmailAddress::class, function($value) {
			$email = (string) $value;
			if(empty($email)) {
				return false;
			}
			return ['email' => $email];
		});

		if(empty($entity->name)) {
			if(!empty($entity->firstName)) {
				$entity->name = $entity->firstName;
				if(!empty($entity->lastName)) {
					$entity->name .= " " . $entity->lastName;
				}
			} else if(!empty($entity->lastName)) {
				$entity->name = $entity->lastName;
			} else if(!empty($entity->emailAddresses)) {
				$entity->name = $entity->emailAddresses[0]->email;
			}
		}

		if(!$entity->isOrganization && empty($entity->lastName) && empty($entity->firstName)) {
			$entity->firstName = $entity->name;
		}

		$entity->phoneNumbers = $this->importHasMany($entity, $entity->phoneNumbers, $vcardComponent->TEL, PhoneNumber::class, function($value) {
			$number = (string) $value;
			if(empty($number)) {
				return false;
			}
			return ['number' => (string) $number];
		});

		$entity->addresses = $this->importHasMany($entity, $entity->addresses, $vcardComponent->ADR, Address::class, function($value) {
			$a = $value->getParts();
			$addr = [];

			$addr['address'] = $a[2] ?? null;
			if(!empty($a[1])) {
				$addr['address'] .= "\n" . $a[1];
			}
			$addr['city'] = $a[3] ?? null;
			$addr['state'] = $a[4] ?? null;
			$addr['zipCode'] = $a[5] ?? null;
			$addr['country'] = $a[6] ?? null;
			return $addr;
		});

		$this->importPhoto($entity, $vcardComponent);

		if (!$entity->save()) {
			throw new SaveException($entity);
		}

		if(!$entity->isOrganization) {
			$this->importOrganizations($entity, $vcardComponent);
		}
		
		return $entity;
	}

	/**
	 * @throws Exception
	 */
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

	private function getVCardOrganizations(VCardComponent $vcard): array
	{
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
	 * @param string $name
	 * @return string[]
	 */
	private function splitOrganizationName(string $name): array {
		if(preg_match_all('/\[[0-9]+] ([^\[]*)/', $name, $matches)){
			return array_map('trim', $matches[1]);
		}
		
		return [$name];
	}

	/**
	 * @param Contact $contact
	 * @param VCardComponent $vcard
	 * @return void
	 * @throws Exception
	 */
	private function importOrganizations(Contact $contact, VCardComponent $vcard) {
		
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
					throw new Exception("Could not unlink organization " . $o->name);
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

	private static function convertType(string $vCardType): ?string {
		$types = explode(',', strtolower($vCardType));
		foreach($types as $type) {

			switch($type) {

				// we don't have a way to store pref (yet). See https://github.com/Intermesh/groupoffice/issues/1042
				case 'pref':
					break;

				case 'cell':
					return 'mobile';

				case 'internet':
					return null;

				default:
					return $type;
			}
		}

		return null;
	}

	public function getFileExtension(): string
	{
		return 'vcf';
	}

	/**
	 * @throws Exception
	 */
	protected function importEntity(): Contact {
		//not needed because of import file override
		$contact = $this->findOrCreateContact($this->card);

		$this->import($this->card, $contact);

		return $contact;

	}

	/**
	 * @var VCardComponent
	 */
	private $card;

	/**
	 * @throws ParseException
	 */
	protected function nextImportRecord(): bool
	{
		$this->card = $this->splitter->getNext();

		if (!isset($this->card->VERSION)) {
			ErrorHandler::log("VCARD error. Card without a version!");
			return false;
		}

		if ($this->card->VERSION != "3.0") {
			$this->card = $this->card->convert(SabreDocument::VCARD30);
		}

		return $this->card != false;

	}

	/**
	 * @var VCardSplitter
	 */
	private $splitter;

	protected $values;

	/**
	 * @throws Exception
	 */
	protected function initImport(File $file): void
	{
		/** @noinspection PhpParamsInspection */
		$this->splitter = new VCardSplitter(StringUtil::cleanUtf8($file->getContents()), Reader::OPTION_FORGIVING + Reader::OPTION_IGNORE_INVALID_LINES);
		if(!isset( $this->clientParams['values']))
		{
			$this->clientParams['values'] = [];
		}

		if(!isset($this->clientParams['values']['addressBookId'])) {
			$this->clientParams['values']['addressBookId'] = go()->getAuthState()->getUser(['addressBookSettings'])->addressBookSettings->getDefaultAddressBookId();
		}

	}

	protected function finishImport(): void
	{
		unset($this->splitter);
	}

	/**
	 *
	 * @param VCardComponent $vcardComponent
	 * @return Contact
	 * @throws Exception
	 */
	private function findOrCreateContact(VCardComponent $vcardComponent) {
		$contact = false;
			if(isset($vcardComponent->uid)) {
				$contact = Contact::find()->where(['addressBookId' => $this->clientParams['values']['addressBookId'], 'uid' => (string) $vcardComponent->uid])->single();
			}
			
			if(!$contact) {
				$contact = new Contact();
			}
			$contact->setValues($this->clientParams['values']);
			
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
	private function saveBlob(VCardComponent $vcardComponent): Blob
	{
		$blob = Blob::fromString($vcardComponent->serialize());
		$blob->type = 'text/vcard';
		$blob->name = ($vcardComponent->uid ?? 'nouid' ) . '.vcf';
		if(!$blob->save()) {
			throw new Exception("could not save vcard blob: " . $blob->getValidationErrorsAsString());
		}
		
		return $blob;
	}

	/**
	 * @inheritDoc
	 */
	public static function supportedExtensions(): array
	{
		return ['vcf'];
	}


}
