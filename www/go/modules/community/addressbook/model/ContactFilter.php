<?php
namespace go\modules\community\addressbook\model;
						
use go\core\orm\Property;
						
/**
 * ContactGroup model
 *
 * @copyright (c) 2018, Intermesh BV http://www.intermesh.nl
 * @author Merijn Schering <mschering@intermesh.nl>
 * @license http://www.gnu.org/licenses/agpl-3.0.html AGPLv3
 */

class ContactFilter extends \go\core\acl\model\AclOwnerEntity {
	
	/**
	 * 
	 * @var int
	 */							
	public $id;

	/**
	 * 
	 * @var int
	 */							
	public $createdBy;
	
	public $name;
	
	protected $filter;
	
	public $aclId;

	protected static function defineMapping() {
		return parent::defineMapping()
						->addTable("addressbook_contact_filter");
	}
	
	public function getFilter() {
		return empty($this->filter) ? [] : json_decode($this->filter);
	}
	
	public function setFilter($filter) {
		$this->filter = json_encode($filter);
	}

}
