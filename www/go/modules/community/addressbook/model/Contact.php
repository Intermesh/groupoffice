<?php
namespace go\modules\community\addressbook\model;

use Exception;
use go\core\acl\model\AclItemEntity;
use go\core\data\convert\Xlsx;
use go\core\db\Column;
use go\core\db\Criteria;
use go\core\model\Link;
use go\core\orm\CustomFieldsTrait;
use go\core\orm\Query;
use go\core\orm\SearchableTrait;
use go\core\util\DateTime;
use go\core\validate\ErrorCode;
use go\modules\community\addressbook\convert\Spreadsheet;
use go\modules\community\addressbook\convert\VCard;
use function GO;
use go\core\mail\Message;
use go\core\TemplateParser;
use go\core\db\Expression;
use go\core\fs\File;
use go\core\model\Acl;
use GO\Files\Model\Folder;

/**
 * Contact model
 *
 * @copyright (c) 2018, Intermesh BV http://www.intermesh.nl
 * @author Merijn Schering <mschering@intermesh.nl>
 * @license http://www.gnu.org/licenses/agpl-3.0.html AGPLv3
 */

class Contact extends AclItemEntity {
	
	use CustomFieldsTrait;
	
	use SearchableTrait;

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
	 *
	 * @var EmailAddress[]
	 */
	 public $emailAddresses = [];
	
	/**
	 *
	 * @var PhoneNumber[]
	 */
	public $phoneNumbers = [];
	
	/**
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
	 * @var ContactGroup[] 
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

	public function getStarred() {
		return !!$this->starred;
	}

	public function setStarred($starred) {
		$this->starred = empty($starred) ? null : true;
	}

	protected static function internalRequiredProperties() {
		return ['isOrganization'];
	}

	public function buildFilesPath() {
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
	protected static function filesPathProperties() {
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
	
	
	protected static function aclEntityClass(): string {
		return AddressBook::class;
	}

	protected static function aclEntityKeys(): array {
		return ['addressBookId' => 'id'];
	}

  /**
   * @inheritDoc
   */
	protected static function defineMapping() {
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
   * @param array $properties
   * @return static|false
   * @throws Exception
   */
	public static function findForUser($userId, $properties = []) {
		if(empty($userId)) {
			return false;
		}
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
	public static function findByPhone($number) {
		return static::find()
						->join("addressbook_phone_number", "e", "e.contactId = c.id")
						->groupBy(['c.id'])
						->where(['e.number' => $number]);
	}
	
	protected static function defineFilters() {

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
//
//											if(!$query->isJoined('addressbook_email_address', 'e')) {
//												$query->join('addressbook_email_address', 'e', 'e.contactId = c.id', "LEFT")
//													->groupBy(['c.id']);
//											}

											$criteria->andWhere('c.id in (select contactId from addressbook_email_address)');

//											$criteria->andWhere('e.email', $value ? 'IS NOT' : 'IS', null);
										})

										->addText("email", function(Criteria $criteria, $comparator, $value, Query $query) {
											if(!$query->isJoined('addressbook_email_address', 'em')) {
												$query->join('addressbook_email_address', 'em', 'em.contactId = c.id', "INNER");
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
										->addText("street", function(Criteria $criteria, $comparator, $value, Query $query) {
											if(!$query->isJoined('addressbook_address', 'adr')) {
												$query->join('addressbook_address', 'adr', 'adr.contactId = c.id', "LEFT");
											}
											
											$criteria->where('adr.street', $comparator, $value);
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
										->addDate("dateofbirth", function(Criteria $criteria, $comparator, $value, Query $query) {
											if(!$query->isJoined('addressbook_date', 'dob')) {
												$query->join('addressbook_date', 'dob', 'dob.contactId = c.id', "INNER");
											}
											$criteria->where('dob.type', '=', Date::TYPE_BIRTHDAY)
												->andWhere('dob.date',$comparator, $value);
										})
										->addDate("actionDate", function(Criteria $criteria, $comparator, $value, Query $query) {
											if(!$query->isJoined('addressbook_date', 'actionDate')) {
												$query->join('addressbook_date', 'actionDate', 'actionDate.contactId = c.id', "INNER");
											}
											$criteria->where('actionDate.type', '=', Date::TYPE_ACTION)
												->andWhere('actionDate.date',$comparator, $value);
										})
										->addDate("birthday", function(Criteria $criteria, $comparator, $value, Query $query) {
											if(!$query->isJoined('addressbook_date', 'bdate')) {
												$query->join('addressbook_date', 'bdate', 'bdate.contactId = c.id AND bdate.type = "'.Date::TYPE_BIRTHDAY .'"', "INNER");
											}

											$date = $value->format(Column::DATE_FORMAT);

											$query->select("IF (STR_TO_DATE(CONCAT(YEAR('$date'), '/', MONTH(bdate.date), '/', DAY(bdate.date)),'%Y/%c/%e') >= '$date', "
												."STR_TO_DATE(CONCAT(YEAR('$date'), '/', MONTH(bdate.date), '/', DAY(bdate.date)),'%Y/%c/%e') , "
												."STR_TO_DATE(CONCAT(YEAR('$date') + 1,'/', MONTH(bdate.date), '/', DAY(bdate.date)),'%Y/%c/%e')) as upcomingBirthday", true);

											$query->having('upcomingBirthday '. $comparator .' "' . $date . '"');
											$query->orderBy(['upcomingBirthday' => 'ASC']);

										})->add('userGroupId', function(Criteria $criteria, $value, Query $query) {
											$query->join('core_user_group', 'ug', 'ug.userId = c.goUserId');
											$criteria->where(['ug.groupId' => $value]);
										})->add('isUser', function(Criteria $criteria, $value, Query $query) {
											$criteria->where('c.goUserId', empty($value) ? '=' : '!=', null);
											
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

	public static function sort(Query $query, array $sort)
	{
		if(isset($sort['firstName'])) {
			$sort['name'] = $sort['firstName'];
			unset($sort['firstName']);
		}
//		if(isset($sort['lastName'])) {
//			$dir = $sort['lastName'] == 'ASC' ? 'ASC' : 'DESC';
//			$sort[] = new Expression("IF(c.isOrganization, c.name, c.lastName) " . $dir);
//			unset($sort['lastName'], $sort['lastName']);
//			$sort['firstName'] = $dir;
//		}

		if(isset($sort['birthday'])) {
			$query->join('addressbook_date', 'birthdaySort', 'birthdaySort.contactId = c.id and birthdaySort.type="birthday"', 'LEFT');
			$sort['birthdaySort.date'] = $sort['birthday'];
			unset($sort['birthday']);
		};

		if(isset($sort['addressBook'])) {
			$query->join('addressbook_addressbook', 'abSort', 'abSort.id = c.addressBookId', 'INNER');
			$sort['abSort.name'] = $sort['addressBook'];
			unset($sort['addressBook']);
		}

		if(isset($sort['actionDate'])) {
			$query->join('addressbook_date', 'actionDateSort', 'actionDateSort.contactId = c.id and actionDateSort.type="action"', 'LEFT');
			$sort['actionDateSort.date'] = $sort['actionDate'];
			unset($sort['actionDate']);
		};
		
		return parent::sort($query, $sort);
	}

	/**
	 * @inheritDoc
	 */
	public static function converters() {
		return array_merge(parent::converters(), [VCard::class, Spreadsheet::class]);
	}
	
	public function getUid() {
		
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

	private function generateUid() {
		$url = trim(go()->getSettings()->URL, '/');
		$uid = substr($url, strpos($url, '://') + 3);
		$uid = str_replace('/', '-', $uid );

		return $this->id . '@' . $uid;
	}

	public function setUid($uid) {
		$this->uid = $uid;
	}

	public function hasUid() {
		return !empty($this->uid);
	}

	public function getUri() {
		if(empty($this->uri)) {
			$uid = $this->getUid(); //generates uri as well
			if(!isset($uid)) {
				return null;
			}
		}

		return $this->uri;
	}

	private function saveUri() {
		return go()->getDbConnection()
			->update('addressbook_contact',
				['uid' => $this->uid, 'uri' => $this->uri],
				['id' => $this->id])
			->execute();
	}

	public function setUri($uri) {
		$this->uri = $uri;
	}
		
	protected function internalSave() {
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
		
		return $this->saveOriganizationIds();
		
	}

	private function updateEmployees() {
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
			$this->lastName = $this->name;
		} else if(empty($this->name) || (!$this->isModified(['name']) && $this->isModified(['firstName', 'middleName', 'lastName']))) {
			$this->setNameFromParts();
		}
		
		if($this->isNew() && !isset($this->addressBookId)) {
			$this->addressBookId = go()->getAuthState()->getUser(['addressBookSettings'])->addressBookSettings->defaultAddressBookId;
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
		
		return parent::internalValidate();
	}

	/**
	 * Find all linked organizations
	 *
	 * @param array $properties
	 * @return self[]
	 * @throws Exception
	 */
	public function findOrganizations($properties = []){
		return self::findByLink($this, $properties)
			->andWhere('c.isOrganization = true');
	}

	private static $organizationIdsStmt;

	private static function prepareFindOrganizations() {
		if(!isset(self::$organizationIdsStmt)) {
			self::$organizationIdsStmt = self::find()
				->selectSingleValue('c.id')
				->join('core_link', 'l', 'c.id=l.toId and l.toEntityTypeId = ' . self::entityType()->getId())
				->where('fromId = :contactId')
				->andWhere('fromEntityTypeId = ' . self::entityType()->getId())
				->andWhere('c.isOrganization = true')
				->createStatement();
		}
		return self::$organizationIdsStmt;
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
	 * @throws Exception
	 */
	public function getOrganizations() {
		return $this->findOrganizations()->all();
	}

	public static function atypicalApiProperties()
	{
		return array_merge(parent::atypicalApiProperties(), ['organizations']);
	}

	public function setOrganizationIds($ids) {		
		$this->setOrganizationIds = $ids;				
	}
	
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

	public function getSearchDescription() {
		$addressBook = AddressBook::findById($this->addressBookId, ['name']);

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

	public function title() {
		return $this->name;
	}

	protected function getSearchFilter() {
		return $this->isOrganization ? 'isOrganization' : 'isContact';
	}

	protected function getSearchKeywords()
	{
		$keywords = [$this->name, $this->debtorNumber, $this->jobTitle];
		foreach($this->emailAddresses as $e) {
			$keywords[] = $e->email;
		}
		foreach($this->phoneNumbers as $e) {
			$keywords[] = preg_replace("/[^0-9+]/", "", $e->number);
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

		if(!empty($this->notes)) {
			$keywords[] = $this->notes;
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
	 */
	public function getAge() {
		$bday = $this->getBirthday();
		if($bday === '') {
			return 0;
		}
		$date = new DateTime($bday);
		$diff = $date->diff(new DateTime());
		return $diff->y;
	}

	/**
	 * @return string
	 */
	public function getAddressBook() {
		return AddressBook::findById($this->addressBookId)->name;
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

		if(!$link->isBetween("Contact", "Contact")) {
			return;
		}
		
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


	/**
	 * Because we've implemented the getter method "getOrganizationIds" the contact 
	 * modSeq must be incremented when a link between two contacts is deleted or 
	 * created.
	 * 
	 * @param Link $link
	 */
	public static function onLinkDelete(Query $links) {
		
		$query = clone $links;
		$query->andWhere('(toEntityTypeId = :e1 AND fromEntityTypeId = :e2)')->bind([':e1'=> static::entityType()->getId(), ':e2'=> static::entityType()->getId()]);

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
	public function findUrlByType($type, $returnAny = true) {
		return $this->findPropByType("urls", $type, $returnAny);
	}

	
	/**
	 * Find email address by type
	 * 
	 * @param string $type
	 * @param boolean $returnAny
	 * @return EmailAddress|boolean
	 */
	public function findEmailByType($type, $returnAny = true) {
		return $this->findPropByType("emailAddresses", $type, $returnAny);
	}
	
	/**
	 * Find phoneNumber by type
	 * 
	 * @param string $type
	 * @param boolean $returnAny
	 * @return PhoneNumbers|boolean
	 */
	public function findPhoneNumberByType($type, $returnAny = true) {
		return $this->findPropByType("phoneNumbers", $type, $returnAny);
	}
	
	/**
	 * Find street address by type
	 * 
	 * @param string $type
	 * @param boolean $returnAny
	 * @return Address|boolean
	 */
	public function findAddressByType($type, $returnAny = true) {
		return $this->findPropByType("addresses", $type, $returnAny);
	}
	
	/**
	 * Find date by type
	 * 
	 * @param string $type
	 * @param boolean $returnAny
	 * @return Date|boolean
	 */
	public function findDateByType($type, $returnAny = true) {
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
		
		return isset($this->$propName[0]) ? $this->$propName[0] : false;
	}

  /**
   * Decorate the message for newsletter sending.
   * This function should at least add the to address.
   *
   * @param Message $message
   * @return bool
   */
	public function decorateMessage(Message $message) {
		if(!isset($this->emailAddresses[0])) {
			return false;
		}
		$message->setTo($this->emailAddresses[0]->email, $this->name);
	}

//
//	private static $colors =  [
//    'C62828',
//    'AD1457',
//    '6A1B9A',
//    '4527A0',
//    '283593',
//    '1565C0',
//    '0277BD',
//    '00838F',
//    '00695C',
//    '2E7D32',
//    '558B2F',
//    '9E9D24',
//    'F9A825',
//    'FF8F00',
//    'EF6C00',
//    '424242'
//  ];
//
//	public function getColor() {
//    if(isset($this->color)) {
//      return $this->color;
//    }
//
//    $index = Settings::get()->lastContactColorIndex;
//
//    if(!isset(self::$colors[$index])) {
//      $index = 0;
//    }
//
//    $this->color = self::$colors[$index];
//    $index++;
//    Settings::get()->lastContactColorIndex = $index;
//    Settings::get()->save();
//
//    go()->getDbConnection()->update(self::getMapping()->getPrimaryTable()->getName(), ['color' => $this->color], ['id' => $this->id])->execute();
//
//    return $this->color;
//  }
//
//  public function setColor($v) {
//	  $this->color = $v;
//  }

	/**
	 * @inheritDoc
	 */
  protected function mergeProp($entity, $name, $p)
  {
  	//Groups can't be merged if addressbook is different.
  	if($name == "groups" && $entity->addressBookId != $this->getOldValue("addressBookId")) {
  		$this->groups = $entity->groups;
	  }

	  return parent::mergeProp($entity, $name, $p);
  }

  public static function check()
  {
  	//fix missing uri or uid
  	$contacts = Contact::find(['id', 'uri', 'uid', 'addressBookId'])->where('uid is null OR uri is null');
  	foreach($contacts as $contact) {
  		$contact->save();
	  }
	  return parent::check();
  }

}
