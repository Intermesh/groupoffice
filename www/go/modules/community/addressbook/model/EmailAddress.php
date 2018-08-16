<?php
namespace go\modules\community\addressbook\model;
						
use go\core\orm\Property;
						
/**
 * EmailAddress model
 *
 * @copyright (c) 2018, Intermesh BV http://www.intermesh.nl
 * @author Merijn Schering <mschering@intermesh.nl>
 * @license http://www.gnu.org/licenses/agpl-3.0.html AGPLv3
 */

class EmailAddress extends Property {
	
	/**
	 * 
	 * @var int
	 */							
	public $id;

	/**
	 * 
	 * @var int
	 */							
	public $contactId;

	/**
	 * 
	 * @var string
	 */							
	public $type;

	/**
	 * 
	 * @var string
	 */							
	public $email;

	protected static function defineMapping() {
		return parent::defineMapping()
						->addTable("addressbook_email_address");
	}

}