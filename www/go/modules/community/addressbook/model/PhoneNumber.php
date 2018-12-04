<?php
namespace go\modules\community\addressbook\model;
						
use go\core\orm\Property;
						
/**
 * Phone model
 *
 * @copyright (c) 2018, Intermesh BV http://www.intermesh.nl
 * @author Merijn Schering <mschering@intermesh.nl>
 * @license http://www.gnu.org/licenses/agpl-3.0.html AGPLv3
 */

class PhoneNumber extends Property {
	
	const TYPE_HOME = "home";
	const TYPE_MOBILE = "mobile";
	const TYPE_WORK = "work";
	const TYPE_FAX = "fax";
	const TYPE_OTHER = "other";

	
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
	public $number;

	protected static function defineMapping() {
		return parent::defineMapping()
						->addTable("addressbook_phone_number");
	}

}