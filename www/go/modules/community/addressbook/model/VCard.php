<?php
namespace go\modules\community\addressbook\model;

use go\core\orm\Entity;
use Sabre\VObject\Reader;

class VCard extends \go\core\acl\model\AclItemEntity {
	public $contactId;
	public $data;
	public $uid;
	public $modifiedAt;
	
	protected static function defineMapping() {
		return parent::defineMapping()
						->addTable('addressbook_vcard');
	}
	
	/**
	 * Get the VCard VObject component
	 * 
	 * @return \Sabre\VObject\Component\VCard
	 */
	public function toVObject() {
		return Reader::read($this->data, Reader::OPTION_FORGIVING + Reader::OPTION_IGNORE_INVALID_LINES);
	}

	protected static function aclEntityClass() {
		return Contact::class;
	}

	protected static function aclEntityKeys() {
		return ['contactId' => 'id'];
	}

}
