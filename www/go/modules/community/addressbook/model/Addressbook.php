<?php
namespace go\modules\community\addressbook\model;

use go\core\acl\model\AclEntity;
						
/**
 * Address book model
 *
 * @copyright (c) 2018, Intermesh BV http://www.intermesh.nl
 * @author Merijn Schering <mschering@intermesh.nl>
 * @license http://www.gnu.org/licenses/agpl-3.0.html AGPLv3
 */

class Addressbook extends AclEntity {
	
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
	public $acid;

	/**
	 * 
	 * @var int
	 */							
	public $createdBy;

	protected static function defineMapping() {
		return parent::defineMapping()
						->addTable("addressbook_addressbook");
	}

}