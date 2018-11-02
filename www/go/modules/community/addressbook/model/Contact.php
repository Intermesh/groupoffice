<?php
namespace go\modules\community\addressbook\model;

use go\core\acl\model\AclItemEntity;
use go\core\db\Query;
use go\core\orm\CustomFieldsTrait;
use go\core\orm\SearchableTrait;
use go\core\validate\ErrorCode;
use go\modules\core\links\model\Link;
						
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
	 * @var \IFW\Util\DateTime
	 */							
	public $createdAt;

	/**
	 * 
	 * @var \IFW\Util\DateTime
	 */							
	public $modifiedAt;

	/**
	 * Prefixes like 'Sir'
	 * @var string
	 */							
	public $prefixes = '';

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
	 * name field for companies and contacts. It should be the display name of first, middle and last name
	 * @var string
	 */							
	public $name;

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
	 * @var ContactOrganization[]
	 */
	public $employees = [];
	
	
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
	
	
	public $starred = false;

	protected static function aclEntityClass(): string {
		return AddressBook::class;
	}

	protected static function aclEntityKeys(): array {
		return ['addressBookId' => 'id'];
	}
	
	protected static function defineMapping() {
		return parent::defineMapping()
						->addTable("addressbook_contact", 'c')
						->addUserTable("addressbook_contact_star", "s", ['id' => 'contactId'])
						->addRelation('dates', Date::class, ['id' => 'contactId'])
						->addRelation('phoneNumbers', PhoneNumber::class, ['id' => 'contactId'])
						->addRelation('emailAddresses', EmailAddress::class, ['id' => 'contactId'])
						->addRelation('addresses', Address::class, ['id' => 'contactId'])
						->addRelation('urls', Url::class, ['id' => 'contactId'])
						->addRelation('groups', ContactGroup::class, ['id' => 'contactId']);
	}
	
	public static function filter(Query $query, array $filter) {
		if (isset($filter['addressBookId'])) {
			$query->andWhere('addressBookId', '=', $filter['addressBookId']);
		}
		
		if (isset($filter['groupId'])) {
			$query->join('addressbook_contact_group', 'g', 'g.contactId = c.id')
							->andWhere('g.groupId', '=', $filter['groupId']);
		}
		
		if (isset($filter['isOrganization'])) {
			$query->andWhere('isOrganization', '=', $filter['isOrganization']);
		}	
		
		return parent::filter($query, $filter);
	}
	
	protected static function searchColumns() {
		return ['name'];
	}
	
	protected function internalValidate() {		
		
		if($this->isModified('addressBookId') || $this->isModified('groups')) {
			//verify groups and address book match
			
			foreach($this->groups as $group) {
				$group = Group::findById($group->groupId);
				if($group->addressBookId != $this->addressBookId) {
					$this->setValidationError('groups', ErrorCode::INVALID_INPUT, "The contact groups must match with the addressBookId. Group ID: ".$group->id." belongs to ".$group->addressBookId." and the contact belongs to ". $this->addressBookId);
				}
			}
		}
		
		return parent::internalValidate();
	}
	
	public function getOrganizationIds() {
		$query = Link::find()->selectSingleValue('toId');
		Link::filter($query, [
				'entityId' => $this->id,
				'entity' => "Contact",
				'entities' => [
						['name' => "Contact", "filter" => "isOrganization"]
				]
		]);
		return array_map("intval", $query->all());
	}

	protected function getSearchDescription() {
		$addressBook = AddressBook::findById($this->addressBookId);
		
		$orgStr = "";
		
		if(!$this->isOrganization) {
			$organizationIds = $this->getOrganizationIds();
			
			if(!empty($organizationIds)) {
				$orgStr = ': '.implode(', ', Contact::find()->selectSingleValue('name')->where(['id' => $organizationIds])->all());
			}
		}
		return $addressBook->name . $orgStr;
	}

	protected function getSearchName() {
		return $this->name;
	}

	protected function getSearchFilter() {
		return $this->isOrganization ? 'isOrganization' : 'isContact';
	}
	
	/**
	 * Because we've implemented the getter method "getOrganizationIds" the contact 
	 * modSeq must be incremented when a link between two contacts is deleted or 
	 * created.
	 * 
	 * @param Link $link
	 */
	public static function onLinkSaveOrDelete(Link $link) {
		if($link->getToEntity() !== "Contact" || $link->getFromEntity() !== "Contact") {
			return;
		}
		
		$ids = [$link->toId, $link->fromId];
		
		Contact::getType()->changes(
					(new \go\core\db\Query)
					->select('c.id AS entityId, a.aclId, "0" AS destroyed')
					->from('addressbook_contact', 'c')
					->join('addressbook_addressbook', 'a', 'a.id = c.addressBookId')					
					->where('c.id', 'IN', $ids)
					);
		
	}
}