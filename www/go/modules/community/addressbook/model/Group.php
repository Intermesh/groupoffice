<?php
namespace go\modules\community\addressbook\model;
						
use go\core\orm\Property;
						
/**
 * Group model
 *
 * @copyright (c) 2018, Intermesh BV http://www.intermesh.nl
 * @author Merijn Schering <mschering@intermesh.nl>
 * @license http://www.gnu.org/licenses/agpl-3.0.html AGPLv3
 */

class Group extends \go\core\acl\model\AclItemEntity {
	
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
		if($this->isNew() && !AddressBook::findById($this->addressBookId)->save()) {
			return false;
		}
		
		return parent::internalSave();
	}
	
	protected function internalDelete() {
		//modseq increase because groups is a property too.
		if(!AddressBook::findById($this->addressBookId)->save()) {
			return false;
		}
		
		return parent::internalDelete();
	}
}