<?php
namespace go\modules\community\addressbook\model;

use go\core\acl\model\AclItemEntity;
use go\core\db\Criteria;
use go\core\db\Query;
use go\modules\community\addressbook\model\AddressBook;
use go\modules\community\addressbook\model\Contact;

						
/**
 * Group model
 *
 * @copyright (c) 2018, Intermesh BV http://www.intermesh.nl
 * @author Merijn Schering <mschering@intermesh.nl>
 * @license http://www.gnu.org/licenses/agpl-3.0.html AGPLv3
 */

class Group extends AclItemEntity {
	
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
	 * 
	 * @var string
	 */							
	public $name;

	protected static function defineMapping() {
		return parent::defineMapping()
						->addTable("addressbook_group");
	}

	protected static function aclEntityClass() {
		return AddressBook::class;
	}

	protected static function aclEntityKeys() {
		return ['addressBookId' => 'id'];
	}
	
	public static function getClientName() {
		return "AddressBookGroup";
	}

	protected function internalSave() {
		
		//modseq increase because groups is a property too.
		if($this->isNew()) {
			AddressBook::entityType()->change(AddressBook::findById($this->addressBookId));
		}
		
		return parent::internalSave();
	}
	
	protected function internalDelete() {

		$addressBook = AddressBook::findById($this->addressBookId);

		// When address book is being deleted then these don't need to fire.
		// if(!$addressBook->isDeleting()) {
			//modseq increase because groups is a property too.
			AddressBook::entityType()->change($addressBook);
			
			//mark contact as changed because they have a "groups" property that changes.
			Contact::entityType()->changes(
						(new Query)
						->select('c.id AS entityId, a.aclId, "0" AS destroyed')
						->from('addressbook_contact', 'c')
						->join('addressbook_addressbook', 'a', 'a.id = c.addressBookId')
						->join('addressbook_contact_group', 'g', 'c.id = g.contactId')
						->where('g.groupId', '=', $this->id)
						);
		// }
		
		return parent::internalDelete();
	}

	protected static function defineFilters() {
		return parent::defineFilters()->add("addressBookId", function(Criteria $criteria, $value) {
			$criteria->andWhere(['addressBookId' => $value]);			
		});
	}
}