<?php
namespace go\modules\community\addressbook\model;

use Exception;
use go\core\acl\model\AclItemEntity;
use go\core\db\Column;
use go\core\db\Criteria;
use go\core\mail\Address as MailAddress;
use go\core\model\Link;
use go\core\model\Principal;
use go\core\model\User;
use go\core\orm\CustomFieldsTrait;
use go\core\orm\Entity;
use go\core\orm\Filters;
use go\core\orm\Mapping;
use go\core\orm\PrincipalTrait;
use go\core\orm\Query;
use go\core\orm\SearchableTrait;
use go\core\util\ArrayObject;
use go\core\util\DateTime;
use go\core\util\StringUtil;
use go\core\validate\ErrorCode;
use go\modules\business\automation\action\Email;
use go\modules\community\addressbook\convert\Spreadsheet;
use go\modules\community\addressbook\convert\VCard;
use function GO;
use go\core\mail\Message;
use go\core\TemplateParser;
use go\core\fs\File;
use go\core\model\Acl;
use GO\Files\Model\Folder;

/**
 * Class Contact
 *
 * Represents a contact entity.
 */

class Contact extends AclItemEntity {
	
	use CustomFieldsTrait;
	
	use SearchableTrait;

	use PrincipalTrait;

	/**
	 * 
	 * @var int
	 */							
	public $id;

	/**
	 * 
	 * @var int
	 */							
	public $addressBookId;
	
	/**
	 * If this contact belongs to a user then this is set to the user ID.
	 * 
	 * @var int 
	 */
	public $goUserId;

	/**
	 * 
	 * @var int
	 */							
	public $createdBy;
	
	/**
	 *
	 * @var int 
	 */
	public $modifiedBy;

	/**
	 * 
	 * @var DateTime
	 */							
	public $createdAt;

	/**
	 * 
	 * @var DateTime
	 */							
	public $modifiedAt;

	/**
	 * Prefixes like 'Sir'
	 * @var string
	 */							
	public $prefixes = '';
	
	/**
	 * @var string
	 */
	public $initials = '';
	
	/**
	 * 
	 * @var string
	 */							
	public $firstName = '';

	/**
	 * 
	 * @var string
	 */							
	public $middleName = '';

	/**
	 * 
	 * @var string
	 */							
	public $lastName = '';

	/**
	 * Suffixes like 'Msc.'
	 * @var string
	 */							
	public $suffixes = '';

	/**
	 * M for Male, F for Female or null for unknown
	 * @var string
	 */							
	public $gender;

	/**
	 * 
	 * @var string
	 */							
	public $notes;

	/**
	 *
	 * @var bool
	 */
	public $isOrganization = false;
	
	/**
	 * The job title
	 * 
	 * @var string 
	 */
	public $jobTitle;

	/**
	 * The department
	 *
	 * @var string
	 */
	public $department;

	/**
	 * name field for companies and contacts. It should be the display name of first, middle and last name
	 * @var string
	 */							
	public $name;

	/**
	 * name of the bank for this contact
	 * @var string
	 */
	public $nameBank = '';

	/**
	 *
	 * @var string
	 */
	public $BIC = '';

	/**
	 * 
	 * @var string
	 */							
	public $IBAN = '';

	/**
	 * Company trade registration number
	 * @var string
	 */							
	public $registrationNumber = '';

	/**
	 * International Code Designator (ISO/IEC 6523)
	 *
	 * @link https://en.wikipedia.org/wiki/ISO/IEC_6523
	 * @link https://docs.peppol.eu/edelivery/codelists/
	 * @var string
	 */
	public ?string $icd;

	/**
	 * 
	 * @var string
	 */							
	public $vatNo;
	
	/**
	 * Don't charge VAT in sender country
	 * 
	 * @var boolean
	 */							
	public $vatReverseCharge = false;

	/**
	 * The debtor number.
	 *
	 * @var string
	 */
	public $debtorNumber;

	/**
	 * 
	 * @var string
	 */							
	public $photoBlobId;

	/**
	 * 
	 * @var string
	 */							
	public $language;
	
	/**
	 *
	 * @var int
	 */
	public $filesFolderId;

	/**
	 * Holds an array of email addresses.
	 *
	 * @var EmailAddress[]
	 */
	 public $emailAddresses = [];

	/**
	 * An array to hold phone numbers.
	 *
	 * @var PhoneNumber[]
	 */
	public $phoneNumbers = [];

	/**
	 * Holds an array of dates.
	 *
	 * @var Date[];
	 */
	public $dates = [];
	
	/**
	 *
	 * @var Url[]
	 */
	public $urls = [];	
	
	
	/**
	 *
	 * @var Address[]
	 */
	public $addresses = [];

	/**
	 *
	 * @var int[]
	 */
	public $groups = [];

  /**
   * Color in hex
   *
   * @var string
   */
	public $color;
	
	
	/**
	 * Starred by the current user or not.
	 *
	 * Should not be false but null for ordering. Records might be missing.
	 *
	 * @var boolean
	 */
	protected $starred = null;


	/**
	 * Indicates whether the contact has may receive newsletters
	 *
	 * @var bool $newsletterAllowed
	 */
	public bool $newsletterAllowed = true;



	public ?DateTime $lastContactAt = null;

	public ?DateTime $actionAt = null;

	public function getStarred(): bool
	{
		return !!$this->starred;
	}

	public function setStarred($starred) {
		$this->starred = empty($starred) ? null : true;
	}

	protected static function internalRequiredProperties(): array
	{
		return ['isOrganization'];
	}

	public function buildFilesPath(): string
	{
		if($this->isOrganization) {
			$new_folder_name = File::stripInvalidChars($this->name).' ('.$this->id.')';
		} else{
			$new_folder_name = File::stripInvalidChars($this->lastName .", ". $this->firstName).' ('.$this->id.')';
		}
		$last_part = empty($this->name) ? '' : strtoupper(mb_substr($new_folder_name,0,1,'UTF-8'));

		$addressBook = AddressBook::findById($this->addressBookId);		

		$folder = Folder::model()->findForEntity($addressBook);

		$addressBookPath = $folder->path;

		$new_path = $addressBookPath . '/';

		if($this->isOrganization) {
			$new_path .= 'companies';
		} else{
			$new_path .= 'contacts';
		}

		if(!empty($last_part)) {
			$new_path .= '/'.$last_part;
		}else {
			$new_path .= '/0 no last name';
		}
					
		$new_path .= '/'.$new_folder_name;
		return $new_path;
	}

	/**
	 * Returns properties that affect the files returned in "buildFilesPath()"
	 * When these properties change the system will move the folder to the new location.
	 * 
	 * @return string[]
	 */
	protected static function filesPathProperties(): array
	{
		return ['addressBookId', 'name', 'lastName', 'firstName'];
	}
	
	
	/**
	 * Universal unique identifier.
	 * 
	 * Either set by sync clients or generated by group-office "<id>@<hostname>"
	 * 
	 * @var string 
	 */
	protected $uid;
	
	/**
	 * Blob ID of the last generated vcard
	 * 
	 * @var string 
	 */
	public $vcardBlobId;	
	
	/**
	 * CardDAV uri for the contact
	 * 
	 * @var string
	 */
	protected $uri;
	
	
	protected static function aclEntityClass(): string
	{
		return AddressBook::class;
	}

	protected static function aclEntityKeys(): array
	{
		return ['addressBookId' => 'id'];
	}

  /**
   * @inheritDoc
   */
	protected static function defineMapping(): Mapping
	{
		return parent::defineMapping()
			->addTable("addressbook_contact", 'c')
			->addUserTable("addressbook_contact_star", "s", ['id' => 'contactId'])
			->addArray('dates', Date::class, ['id' => 'contactId'])
			->addArray('phoneNumbers', PhoneNumber::class, ['id' => 'contactId'])
			->addArray('emailAddresses', EmailAddress::class, ['id' => 'contactId'])
			->addArray('addresses', Address::class, ['id' => 'contactId'])
			->addArray('urls', Url::class, ['id' => 'contactId'])
			->addScalar('groups', 'addressbook_contact_group', ['id' => 'contactId']);
	}
	
	public function setNameFromParts() {
		$this->name = $this->firstName;
		if(!empty($this->middleName)) {
			$this->name .= " ".$this->middleName;
		}
		if(!empty($this->lastName)) {
			$this->name .= " ".$this->lastName;
		}
		
		$this->name = trim($this->name);
	}

  /**
   * Find contact for user ID.
   *
   * A contact can optionally be connected to a user. It's not guaranteed that
   * the contact is present.
   *
   * @param int $userId
   * @param array[] $properties
   * @return ?static
   */

	public static function findForUser(int $userId, array $properties = []) {
		return static::find($properties)->where('goUserId', '=', $userId)->single();
	}

  /**
   * Find contact by e-mail address
   *
   * @param string|string[] $email
   * @return static[]|Query
   * @throws Exception
   */
	public static function findByEmail($email, $properties = []) {
		return static::find($properties)
						->join("addressbook_email_address", "e", "e.contactId = c.id")
						->groupBy(['c.id'])
						->where(['e.email' => $email]);
	}


  /**
   * Find contact by e-mail address
   *
   * @param string|string[] $number
   * @return Query
   * @throws Exception
   */
	public static function findByPhone($number): Query
	{
		return static::find()
						->join("addressbook_phone_number", "e", "e.contactId = c.id")
						->groupBy(['c.id'])
						->where(['e.number' => $number]);
	}
	
	protected static function defineFilters(): Filters
	{

		return parent::defineFilters()
										->add("addressBookId", function(Criteria $criteria, $value) {
											$criteria->andWhere('addressBookId', '=', $value);
										})
										->add("starred", function(Criteria $criteria, $value) {
											$criteria->andWhere('starred', '=', $value);

										})
										->add("addressBookIds", function(Criteria $criteria, $value) {
											if(count($value) > 0) {
												$criteria->andWhere('addressBookId IN (' .  implode(',',$value). ')');

											}
										})

										->add("groupId", function(Criteria $criteria, $value, Query $query) {
											$query->join('addressbook_contact_group', 'g', 'g.contactId = c.id');
											
											$criteria->andWhere('g.groupId', '=', $value);
										})
                    ->add("isInGroup", function(Criteria $criteria, $value, Query $query) {
                      $not = $value ? '' : 'NOT';
                      $criteria->andWhere('c.id ' . $not . ' IN (SELECT contactId FROM addressbook_contact_group)');
                    })
										->add("isOrganization", function(Criteria $criteria, $value) {
											if($value === null) {
												return;
											}
											$criteria->andWhere('isOrganization', '=', (bool) $value);
										})
										->add("hasEmailAddresses", function(Criteria $criteria, $value, Query $query) {
											$criteria->andWhere('c.id in (select contactId from addressbook_email_address)');
										})
										->add("hasPhoneNumbers", function(Criteria $criteria, $value, Query $query) {
											$criteria->andWhere('c.id in (select contactId from addressbook_phone_number)');
										})
										->add("hasOrganizations", function(Criteria $criteria, $value, Query $query) {

											$sub = Contact::find()
												->selectSingleValue('org.id')
												->tableAlias('org')
												->where('isOrganization', '=', true)
												->join('core_link', 'l',
													'c.id=l.fromId AND org.id=l.toId and l.fromEntityTypeId = '.self::entityType()->getId() . ' AND l.toEntityTypeId=' . self::entityType()->getId(), 'INNER');


											$criteria->andWhereExists($sub, empty($value));

										})

										->addText("email", function(Criteria $criteria, $comparator, $value, Query $query) {
											if(!$query->isJoined('addressbook_email_address', 'em')) {
												$query->join('addressbook_email_address', 'em', 'em.contactId = c.id');
											}

											$criteria->where('em.email', $comparator, $value);
										})
										->addText("name", function(Criteria $criteria, $comparator, $value) {											
											$criteria->where('name', $comparator, $value);
										})
										->addText("firstName", function(Criteria $criteria, $comparator, $value) {
											$criteria->where('firstName', $comparator, $value);
										})
										->addText("lastName", function(Criteria $criteria, $comparator, $value) {
											$criteria->where('firstName', $comparator, $value);
										})
										->addText("jobTitle", function(Criteria $criteria, $comparator, $value) {
											$criteria->where('jobTitle', $comparator, $value);
										})
										->addText("department", function(Criteria $criteria, $comparator, $value) {
											$criteria->where('department', $comparator, $value);
										})
										->addText("notes", function(Criteria $criteria, $comparator, $value) {											
											$criteria->where('notes', $comparator, $value);
										})
										->addText("phone", function(Criteria $criteria, $comparator, $value, Query $query) {												
											if(!$query->isJoined('addressbook_phone_number', 'phone')) {
												$query->join('addressbook_phone_number', 'phone', 'phone.contactId = c.id', "LEFT");
											}
											
											$criteria->where('phone.number', $comparator, $value);
											
										})
										->addText("country", function(Criteria $criteria, $comparator, $value, Query $query) {												
											if(!$query->isJoined('addressbook_address', 'adr')) {
												$query->join('addressbook_address', 'adr', 'adr.contactId = c.id', "LEFT");
											}
											
											$criteria->where('adr.country', $comparator, $value);
											
										})
										->addText("state", function(Criteria $criteria, $comparator, $value, Query $query) {
											if(!$query->isJoined('addressbook_address', 'adr')) {
												$query->join('addressbook_address', 'adr', 'adr.contactId = c.id', "LEFT");
											}

											$criteria->where('adr.state', $comparator, $value);

										})
										->addText("zip", function(Criteria $criteria, $comparator, $value, Query $query) {
											if(!$query->isJoined('addressbook_address', 'adr')) {
												$query->join('addressbook_address', 'adr', 'adr.contactId = c.id', "LEFT");
											}

											$criteria->where('adr.zipCode', $comparator, $value);

										})
										->addText("org", function(Criteria $criteria, $comparator, $value, Query $query) {												
											if( !$query->isJoined('addressbook_contact', 'org')) {
												$query->join('core_link', 'l', 'c.id=l.fromId and l.fromEntityTypeId = '.self::entityType()->getId() . ' AND l.toEntityTypeId=' . self::entityType()->getId(), 'LEFT')
													->join('addressbook_contact', 'org', 'org.id = l.toId AND org.isOrganization=true', 'LEFT');
											}
											$criteria->where('org.name', $comparator, $value);
										})

										->addDate("lastContactAt", function(Criteria $criteria, $comparator, ?DateTime $value){
										 	$criteria->where('lastContactAt', $comparator, $value);
										})
//
//										->add("orgFilter", function(Criteria $criteria, $value, Query $query){
//											if( !$query->isJoined('addressbook_contact', 'orgFilter')) {
//												$query->join('core_link', 'lOrgFilter', 'c.id = lOrgFilter.fromId and lOrgFilter.fromEntityTypeId = '.self::entityType()->getId() . ' AND lOrgFilter.toEntityTypeId=' . self::entityType()->getId(), 'LEFT');
//											}
//
//											$orgs = Contact::find(['id'])
//												->selectSingleValue('id')
//												->where('isOrganization', '=', true)
//												->filter($value);
//
//											$query->where('lOrgFilter.id', 'IN', $orgs);
//										})

										->addText("orgCity", function(Criteria $criteria, $comparator, $value, Query $query) {
											if( !$query->isJoined('addressbook_contact', 'org')) {
												$query->join('core_link', 'l', 'c.id=l.fromId and l.fromEntityTypeId = '.self::entityType()->getId() . ' AND l.toEntityTypeId=' . self::entityType()->getId() , 'LEFT')
													->join('addressbook_contact', 'org', 'org.id = l.toId AND org.isOrganization=true', 'LEFT');
											}
											if(!$query->isJoined('addressbook_address', 'orgAdr')) {
												$query->join('addressbook_address', 'orgAdr', 'orgAdr.contactId = org.id', "LEFT");
											}
											$criteria->where('orgAdr.city', $comparator, $value);
										})

										->addText("orgCountry", function(Criteria $criteria, $comparator, $value, Query $query) {
											if( !$query->isJoined('addressbook_contact', 'org')) {
												$query->join('core_link', 'l', 'c.id=l.fromId and l.fromEntityTypeId = '.self::entityType()->getId() . ' AND l.toEntityTypeId=' . self::entityType()->getId(), 'LEFT')
													->join('addressbook_contact', 'org', 'org.id = l.toId AND org.isOrganization=true', 'LEFT');
											}
											if(!$query->isJoined('addressbook_address', 'orgAdr')) {
												$query->join('addressbook_address', 'orgAdr', 'orgAdr.contactId = org.id', "LEFT");
											}
											$criteria->where('orgAdr.country', $comparator, $value);
										})

										->addText("city", function(Criteria $criteria, $comparator, $value, Query $query) {
											if(!$query->isJoined('addressbook_address', 'adr')) {
												$query->join('addressbook_address', 'adr', 'adr.contactId = c.id', "LEFT");
											}
											
											$criteria->where('adr.city', $comparator, $value);
										})
										->addText("address", function(Criteria $criteria, $comparator, $value, Query $query) {
											if(!$query->isJoined('addressbook_address', 'adr')) {
												$query->join('addressbook_address', 'adr', 'adr.contactId = c.id', "LEFT");
											}

											$criteria->where('adr.address', $comparator, $value);
										})

										//Street is deprecated. Alias for "address"
										->addText("street", function(Criteria $criteria, $comparator, $value, Query $query) {
											if(!$query->isJoined('addressbook_address', 'adr')) {
												$query->join('addressbook_address', 'adr', 'adr.contactId = c.id', "LEFT");
											}
											
											$criteria->where('adr.address', $comparator, $value);
										})
                    ->addText("zip", function(Criteria $criteria, $comparator, $value, Query $query) {
                      if(!$query->isJoined('addressbook_address', 'adr')) {
                        $query->join('addressbook_address', 'adr', 'adr.contactId = c.id', "LEFT");
                      }

                      $criteria->where('adr.zipCode', $comparator, $value);
                    })
										->addNumber("age", function(Criteria $criteria, $comparator, $value, Query $query) {
											
											if(!$query->isJoined('addressbook_date', 'date')) {
												$query->join('addressbook_date', 'date', 'date.contactId = c.id', "LEFT");
											}
											
											$criteria->where('date.type', '=', Date::TYPE_BIRTHDAY);					
											$tag = ':age'.uniqid();
											$criteria->andWhere('TIMESTAMPDIFF(YEAR,date.date, CURDATE()) ' . $comparator . $tag)->bind($tag, $value);
											
										})
										->add('gender', function(Criteria $criteria, $value) {
											$criteria->andWhere(['gender' => $value, 'isOrganization'=> false]);
										})
										->addDateTime("dateofbirth", function(Criteria $criteria, $comparator, $value, Query $query) {
											if(!$query->isJoined('addressbook_date', 'dob')) {
												$query->join('addressbook_date', 'dob', 'dob.contactId = c.id');
											}
											$criteria->where('dob.type', '=', Date::TYPE_BIRTHDAY)
												->andWhere('dob.date',$comparator, $value);
										})
										->addDateTime("actionDate", function(Criteria $criteria, $comparator, $value, Query $query) {

											$criteria->where('actionAt',$comparator, $value);
										})
										->addDateTime("birthday", function(Criteria $criteria, $comparator, $value, Query $query) {
											if(!$query->isJoined('addressbook_date', 'bdate')) {
												$query->join('addressbook_date', 'bdate', 'bdate.contactId = c.id AND bdate.type = "'.Date::TYPE_BIRTHDAY .'"');
											}

											$date = $value->format(Column::DATE_FORMAT);
											$year = $value->format("Y");

											$query->select("IF (STR_TO_DATE(CONCAT('$year', '/', MONTH(bdate.date), '/', DAY(bdate.date)),'%Y/%c/%e') >= '$date', "
												."STR_TO_DATE(CONCAT('$year', '/', MONTH(bdate.date), '/', DAY(bdate.date)),'%Y/%c/%e') , "
												."STR_TO_DATE(CONCAT('$year' + 1,'/', MONTH(bdate.date), '/', DAY(bdate.date)),'%Y/%c/%e')) as upcomingBirthday", true);

											$query->having('upcomingBirthday '. $comparator .' "' . $date . '"');
											$query->orderBy(['upcomingBirthday' => 'ASC']);

											// normal count query will fail with the above select overwritten with count(*)
											$query->calcFoundRows();

										})
										->addText("vatNo", function(Criteria $criteria, $comparator, $value, Query $query) {
											$criteria->where('c.vatNo', $comparator, $value);
										})

										->add('userGroupId', function(Criteria $criteria, $value, Query $query) {
											$query->join('core_user_group', 'ug', 'ug.userId = c.goUserId');
											$criteria->where(['ug.groupId' => $value]);
										})->add('isUser', function(Criteria $criteria, $value, Query $query) {
											if(is_bool($value)) {
												$criteria->where('c.goUserId', empty($value) ? '=' : '!=', null);
											} else{
												$criteria->where('c.goUserId',  '=' , $value);
											}
											
										})->add('duplicate', function(Criteria $criteria, $value, Query $query) {

											$dupQuery = static::find();

											$props = [];
											foreach($value as $property) {
												switch($property) {
													case 'emailAddresses':
													if(!$query->isJoined('addressbook_email_address', 'e')) {
														$query->join('addressbook_email_address', 'e', 'e.contactId = c.id', 'LEFT');
														$dupQuery->join('addressbook_email_address', 'e', 'e.contactId = c.id', 'LEFT');
													}
													$props[] = 'e.email';
													break;

													case 'phoneNumbers':
													if(!$query->isJoined('addressbook_phone_number', 'p')) {
														$query->join('addressbook_phone_number', 'p', 'p.contactId = c.id', 'LEFT');
														$dupQuery->join('addressbook_phone_number', 'p', 'p.contactId = c.id', 'LEFT');
													}
													$props[] = 'p.number';
													break;

													default:
														$props[] = 'c.' . $property;
													break;
												}
											}

											$on = implode(' AND ', array_map(function($prop){
												return $prop . ' <=> dup.' . substr($prop, strpos($prop, '.') + 1);
											}, $props));

											$query->join(
												$dupQuery
												->select($props)
												->select('count(DISTINCT c.id) as n', true)												
												->filter(['permissionLevel' => Acl::LEVEL_DELETE])
												->groupBy($props)
												->having('n > 1')
												,
												'dup',
												$on
											);


											// echo $query;
											
										});
													
										
	}

	public function historyLog(): bool|array
	{
		$log = parent::historyLog();

		unset($log['vcardBlobId']);

		return $log;
	}

	public static function sort(Query $query, ArrayObject $sort): Query
	{
		if(isset($sort['firstName'])) {
			$sort->renameKey('firstName', 'name');
		}

		if(isset($sort['birthday'])) {
			$query->join('addressbook_date', 'birthdaySort', 'birthdaySort.contactId = c.id and birthdaySort.type="birthday"', 'LEFT');
			$sort->renameKey('birthday', 'birthdaySort.date');
		};

		if(isset($sort['addressBook'])) {
			$query->join('addressbook_addressbook', 'abSort', 'abSort.id = c.addressBookId', 'INNER');
			$sort->renameKey('addressBook', 'abSort.name');
		}
		
		return parent::sort($query, $sort);
	}

	/**
	 * @inheritDoc
	 */
	public static function converters(): array
	{
		return array_merge(parent::converters(), [VCard::class, Spreadsheet::class]);
	}
	
	public function getUid(): ?string
	{
		
		if(empty($this->uid)) {
			if(!isset($this->id)) {
				return null;
			}

			$this->uid = $this->generateUid();
			if(empty($this->uri)) {
				$this->uri = $this->uid . '.vcf';
			}

			if(!empty($this->id)) {
				$this->saveUri();
			}
		}

		if(empty($this->uri)) {
			$this->uri = $this->uid . '.vcf';

			if(!empty($this->id)) {
				$this->saveUri();
			}
		}

		return $this->uid;		
	}

	private function generateUid(): string
	{
		$url = trim(go()->getSettings()->URL ?? "", '/');
		$uid = substr($url, strpos($url, '://') + 3);
		$uid = str_replace(['/', ':'], ['-', '-'], $uid );

		return $this->id . '-' . $uid;
	}

	public function setUid($uid) {
		$this->uid = $uid;
	}

	public function hasUid(): bool
	{
		return !empty($this->uid);
	}

	public function getUri(): ?string
	{
		if(empty($this->uri)) {
			$uid = $this->getUid(); //generates uri as well
			if(!isset($uid)) {
				return null;
			}
		}

		return $this->uri;
	}

	private function saveUri(): bool
	{
		return go()->getDbConnection()
			->update('addressbook_contact',
				['uid' => $this->uid, 'uri' => $this->uri],
				['id' => $this->id])
			->execute();
	}

	public function setUri($uri) {
		$this->uri = $uri;
	}
		
	protected function internalSave(): bool
	{
		if(!parent::internalSave()) {
			return false;
		}

		if(empty($this->uid) || empty($this->uri)) {
			//We need the auto increment ID for the UID so we need to save again if this is a new contact
			$this->getUid();
			$this->getUri();
		}

		if($this->isOrganization && $this->isModified(['name']) && !$this->updateEmployees()) {
			return false;
		}

		$this->updateUser();

		return $this->saveOriganizationIds();

	}

	private function updateUser() {
		if(isset($this->goUserId) && $this->isModified([
				'name',
				'emailAddresses',
			])) {

			$user = User::findById($this->goUserId,['email', 'displayName']);
			$user->displayName = $this->name;
			if(isset($this->emailAddresses[0])) {
				$user->email = $this->emailAddresses[0]->email;
			}
			if($user->isModified()) {
				$user->save();
			}
		}
	}

	private function updateEmployees(): bool
	{
		$employees = $this->findEmployees(['jobTitle', 'addressBookId', 'id', 'name']);
		foreach($employees as $e) {
			if(!$e->saveSearch(true)) {
				go()->error("Saving search cache of employee with ID: " . $e->id . " failed");
				return false;
			}
		}

		return true;
	}

	public function findEmployees($properties = []) {
		return static::findByLink($this, $properties)->andWhere(['isOrganization' => false]);
	}

	protected function internalValidate() {

		if($this->isOrganization) {
			$this->firstName =  $this->middleName = $this->prefixes = $this->suffixes = null;

			// We set last name here because when users choose to sort on last name it will be easier.
			$this->lastName = mb_substr($this->name, 0, 100);
		} else if(empty($this->name) || (!$this->isModified(['name']) && $this->isModified(['firstName', 'middleName', 'lastName']))) {
			$this->setNameFromParts();
		}
		
		if($this->isNew() && !isset($this->addressBookId)) {
			$this->addressBookId = go()->getAuthState()->getUser(['addressBookSettings'])->addressBookSettings->getDefaultAddressBookId();
		}
		
		if($this->isModified('addressBookId') || $this->isModified('groups')) {
			//verify groups and address book match
			
			foreach($this->groups as $groupId) {
				$group = Group::findById($groupId);
				if($group->addressBookId != $this->addressBookId) {
					$this->setValidationError('groups', ErrorCode::INVALID_INPUT, "The contact groups must match with the addressBookId. Group ID: ".$group->id." belongs to ".$group->addressBookId." and the contact belongs to ". $this->addressBookId);
				}
			}
		}
		
		parent::internalValidate();
	}

	/**
	 * Find all linked organizations
	 *
	 * @param array $properties
	 * @return self[]|Query
	 * @throws Exception
	 */
	public function findOrganizations(array $properties = [])
	{
		return self::findByLink($this, $properties)
			->andWhere('c.isOrganization = true');
	}

	private static $organizationIdsStmt;

	private static function prepareFindOrganizations() {
		$stmt = go()->getDbConnection()->getCachedStatment('contact-organizations');
		if(!$stmt) {
			$stmt = self::find()
				->selectSingleValue('c.id')
				->join('core_link', 'l', 'c.id=l.toId and l.toEntityTypeId = ' . self::entityType()->getId())
				->where('fromId = :contactId')
				->andWhere('fromEntityTypeId = ' . self::entityType()->getId())
				->andWhere('c.isOrganization = true')
				->createStatement();

			go()->getDbConnection()->cacheStatement('contact-organizations', $stmt);
		}
		return $stmt;
	}
	
	private $organizationIds;
	private $setOrganizationIds;
	
	public function getOrganizationIds() {

		if(!isset($this->organizationIds)) {			
			if($this->isNew()) {
				$this->organizationIds = [];
			} else {
				$stmt = $this->prepareFindOrganizations();
				$stmt->bindValue(':contactId', $this->id);	
				$stmt->execute();
				$this->organizationIds = $stmt->fetchAll();
				$stmt->closeCursor();
			}
		}		
		
		return $this->organizationIds;
	}

	/**
	 * Used in templates. Not returned to API by default.
	 *
	 * @return Contact[]
	 */
	public function getOrganizations() {
		return $this->findOrganizations()->all();
	}

	public static function atypicalApiProperties() : array
	{
		return array_merge(parent::atypicalApiProperties(), ['organizations']);
	}

	public function setOrganizationIds($ids) {		
		$this->setOrganizationIds = $ids;				
	}

	/**
	 * @throws \go\core\orm\exception\SaveException
	 */
	private function saveOriganizationIds(){
		if(!isset($this->setOrganizationIds)) {
			return true;
		}
		$current = $this->getOrganizationIds();
		
		$remove = array_diff($current, $this->setOrganizationIds);
		if(count($remove)) {
			Link::deleteLinkWithIds($remove, Contact::entityType()->getId(), $this->id, Contact::entityType()->getId());
		}
		
		$add = array_diff($this->setOrganizationIds, $current);
		
		foreach($add as $orgId) {
			$org = self::findById($orgId);
			if(!Link::create($this, $org)) {
				throw new Exception("Failed to link organization: ". $orgId);
			}
		}

		$this->organizationIds = $this->setOrganizationIds;
		return true;
	}

	protected function getSearchDescription(): string
	{
		$addressBook = AddressBook::findById($this->addressBookId, ['name'], true);

		$orgStr = "";	
		
		if(!$this->isOrganization) {
			$orgs = $this->findOrganizations()->selectSingleValue('name')->all();
			if(!empty($orgs)) {
				$orgStr = ' - '.implode(', ', $orgs);			
			}
		}

		$jobTitle = "";
		if(!empty($this->jobTitle)) {
			$jobTitle = ' - ' . $this->jobTitle;
		}
		return $addressBook->name . $jobTitle . $orgStr;
	}

	public function title(): string
	{
		return $this->name;
	}

	protected function getSearchFilter(): ?string
	{
		return $this->isOrganization ? 'isOrganization' : 'isContact';
	}

	protected function getSearchKeywords(): ?array
	{
		$keywords = [$this->name, $this->debtorNumber, $this->jobTitle];
		foreach($this->emailAddresses as $e) {
			$keywords[] = $e->email;
		}
		foreach($this->phoneNumbers as $e) {
			$santiziedNumber = preg_replace("/[^0-9+]/", "", $e->number);
			$keywords = array_merge($keywords, StringUtil::numberToKeywords($santiziedNumber));
		}
		if(!$this->isOrganization) {
			$keywords = array_merge($keywords, $this->findOrganizations()->selectSingleValue('name')->all());
		}

		foreach($this->addresses as $address) {
			if(!empty($address->country)) {
				$keywords[] = $address->country;
			}

			if(!empty($address->state)) {
				$keywords[] = $address->state;
			}

			if(!empty($address->city)) {
				$keywords[] = $address->city;
			}

			if(!empty($address->zipCode)) {
				$keywords[] = $address->zipCode;
			}
		}

		foreach($this->urls as $url) {
			$keywords[] = $url->url;
		}

		if(!empty($this->notes)) {
			$keywords[] = $this->notes;
		}

		if(!empty($this->vatNo)) {
			$keywords[] = $this->vatNo;
		}

		return $keywords;
	}

	protected $salutation;

	public function getSalutation() 
	{
		if(!empty($this->salutation)) {
			return $this->salutation;
		}

		if($this->isNew()) {
			return null;
		}

		if($this->isOrganization) {
			return go()->t("Dear sir/madam");
		}

		//re fetch in case this object is not complete
		$contact= Contact::findById($this->id, ['firstName', 'lastName', 'middleName', 'name', 'gender', 'prefixes', 'suffixes', 'language']);
		$tpl = new TemplateParser();
		$tpl->addModel('contact', $contact);

		$addressBook = AddressBook::findById($this->addressBookId, ['salutationTemplate']);

		$this->salutation = $tpl->parse($addressBook->salutationTemplate);
		if(empty($this->salutation)) {
			$this->salutation = go()->t("Dear sir/madam");
		}
		
		go()->getDbConnection()->update('addressbook_contact', ['salutation' => $this->salutation], ['id' => $this->id])->execute();
		
		return $this->salutation;
	}
	
	public function setSalutation($v) {
		$this->salutation = $v;
	}

	/**
	 * Find a birthday, calculate diff in years
	 *
	 * @return int
	 * @throws Exception
	 */
	public function getAge(): int
	{
		$bday = $this->getBirthday();
		if (empty($bday)) {
			return 0;
		}
		$date = new DateTime($bday);
		$diff = $date->diff(new DateTime());
		return $diff->y;
	}

	/**
	 * @return DateTime|string
	 */
	public function getBirthday()
	{
		$oBDay = $this->findDateByType(Date::TYPE_BIRTHDAY, false);
		if($oBDay) {
			return $oBDay->date;
		}
		return '';
	}

	/**
	 * Because we've implemented the getter method "getOrganizationIds" the contact
	 * modSeq must be incremented when a link between two contacts is deleted or
	 * created.
	 *
	 * @param Link $link
	 * @throws Exception
	 */
	public static function onLinkSave(Link $link) {

		if($link->isBetween("Contact", "Contact")) {


			$to = Contact::findById($link->toId);
			$from = Contact::findById($link->fromId);

			//Save contact as link to organizations affect the search entities too.
			if (!$to->isOrganization) {
				$to->saveSearch();
				Contact::entityType()->change($to);
			}

			if (!$from->isOrganization) {
				$from->saveSearch();
				Contact::entityType()->change($from);
			}
		} else if($link->isBetween("Contact", "LinkedEmail")) {
			if($link->getToEntity() == "Contact") {
				$contact = Contact::findById($link->toId);
				$email = \GO\Savemailas\Model\LinkedEmail::model()->findByPk($link->fromId);
			} else {
				$contact = Contact::findById($link->fromId);
				$email = \GO\Savemailas\Model\LinkedEmail::model()->findByPk($link->toId);
			}

			if($contact && $email) {
				$contact->lastContactAt = new DateTime("@" . $email->time);
				$contact->save();
			}
		}
	}


	/**
	 * Because we've implemented the getter method "getOrganizationIds" the contact
	 * modSeq must be incremented when a link between two contacts is deleted or
	 * created.
	 *
	 * @param Query $links
	 * @throws Exception
	 */
	public static function onLinkDelete(Query $links) {
		
		$query = clone $links;

		$query->groupWhere() // makes sure clauses with OR are grouped with ()
			->andWhere('(toEntityTypeId = :e1 AND fromEntityTypeId = :e2)')
			->bind([':e1'=> static::entityType()->getId(), ':e2'=> static::entityType()->getId()]);

		$contactLinks = Link::find()->mergeWith($query);

		foreach($contactLinks as $link) {
			$to = Contact::findById($link->toId);
			$from = Contact::findById($link->fromId);
			
			//Save contact as link to organizations affect the search entities too.
			if(!$to->isOrganization) {			
				$to->saveSearch();
				Contact::entityType()->change($to);
			}
			
			if(!$from->isOrganization) {			
				$from->saveSearch();
				Contact::entityType()->change($from);
			}
		}
		
	}
	
	
	/**
	 * Find URL by type
	 * 
	 * @param string $type
	 * @param boolean $returnAny
	 * @return EmailAddress|boolean
	 */
	public function findUrlByType(string $type, bool $returnAny = true) {
		return $this->findPropByType("urls", $type, $returnAny);
	}

	
	/**
	 * Find email address by type
	 * 
	 * @param string $type
	 * @param boolean $returnAny
	 * @return EmailAddress|boolean
	 */
	public function findEmailByType(string $type, bool $returnAny = true) {
		return $this->findPropByType("emailAddresses", $type, $returnAny);
	}
	
	/**
	 * Find phoneNumber by type
	 * 
	 * @param string $type
	 * @param boolean $returnAny
	 * @return PhoneNumber|boolean
	 */
	public function findPhoneNumberByType(string $type, bool $returnAny = true) {
		return $this->findPropByType("phoneNumbers", $type, $returnAny);
	}
	
	/**
	 * Find street address by type
	 * 
	 * @param string $type
	 * @param boolean $returnAny
	 * @return Address|boolean
	 */
	public function findAddressByType(string $type, bool $returnAny = true) {
		return $this->findPropByType("addresses", $type, $returnAny);
	}
	
	/**
	 * Find date by type
	 * 
	 * @param string $type
	 * @param boolean $returnAny
	 * @return Date|boolean
	 */
	public function findDateByType(string $type, bool $returnAny = true) {
		return $this->findPropByType("dates", $type, $returnAny);
	}
	
	private function findPropByType($propName, $type, $returnAny) {
		foreach($this->$propName as $prop) {
			if($prop->type === $type) {
				return $prop;
			}
		}
		
		if(!$returnAny) {
			return false;
		}
		
		return $this->$propName[0] ?? false;
	}

  /**
   * Decorate the message for newsletter sending.
   * This function should at least add the to address.
   *
   * @param Message $message
   * @return bool
   */
	public function decorateMessage(Message $message) : bool {
		if(!isset($this->emailAddresses[0])) {
			return false;
		}
		$message->setTo(new MailAddress($this->emailAddresses[0]->email, $this->name));
		return true;
	}

	/**
	 * @param Contact $entity
	 * @param string $name
	 * @param array $p
	 * @throws Exception
	 */
  protected function mergeProp(Entity $entity, string $name, array $p)
  {
  	//Groups can't be merged if addressbook is different.
  	if($name == "groups" && $entity->addressBookId != $this->getOldValue("addressBookId")) {
  		$this->groups = $entity->groups;
	  }

	  parent::mergeProp($entity, $name, $p);
  }

  public static function check()
  {
  	//fix missing uri or uid
  	$contacts = Contact::find(['id', 'uri', 'uid', 'addressBookId'])->where('uid is null OR uri is null');
  	foreach($contacts as $contact) {
  		$contact->save();
	  }
	  parent::check();
  }

	protected function principalAttrs(): array {
		return [
			'name' => $this->name,
			'email' => $this->emailAddresses[0]->email,
			'avatarId' =>$this->photoBlobId,
			'description' => go()->t($this->isOrganization?'Organization': 'Contact'). ' '. implode(' ', array_filter([$this->department,$this->jobTitle])),
		];
	}

	protected function isPrincipal(): bool {
		return isset($this->emailAddresses[0]); // must at least have 1 email
	}

	protected function isPrincipalModified(): bool {
		return $this->isModified(['name', 'emailAddresses', 'photoBlobId','jobTitle', 'department','isOrganization','addressBookId']); // addressBookId for ACL
	}

	protected function principalType(): string
	{
		return Principal::Individual;
	}
}
