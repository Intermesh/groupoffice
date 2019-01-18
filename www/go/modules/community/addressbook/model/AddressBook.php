<?php
namespace go\modules\community\addressbook\model;
						
use go\core\orm\Property;
						
/**
 * Address book model
 *
 * @copyright (c) 2018, Intermesh BV http://www.intermesh.nl
 * @author Merijn Schering <mschering@intermesh.nl>
 * @license http://www.gnu.org/licenses/agpl-3.0.html AGPLv3
 */

class AddressBook extends \go\core\acl\model\AclOwnerEntity {
	
	/**
	 * 
	 * @var int
	 */							
	public $id;

	/**
	 * 
	 * @var string
	 */							
	public $name;

	/**
	 * 
	 * @var int
	 */							
	public $aclId;

	/**
	 * 
	 * @var int
	 */							
	public $createdBy;
	
	protected static function defineMapping() {
		return parent::defineMapping()
						->addTable("addressbook_addressbook", "a");
	}
	
	/**
	 * Get the group ID's
	 * 
	 * @return int[]
	 */
	public function getGroups() {
		return (new \go\core\db\Query)
						->selectSingleValue('id')
						->from("addressbook_group")
						->where(['addressBookId' => $this->id])
						->all();
						
	}
	
	public static function filter(\go\core\orm\Query $query, array $filter) {
		if(!empty($filter['q'])) {
			$query->andWhere("name", "LIKE", $filter['q'] . "%");			
		}
		
		return parent::filter($query, $filter);
	}

}