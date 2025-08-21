<?php
namespace go\modules\community\addressbook\model;
						
use go\core\orm\Mapping;
use go\core\orm\Property;
						
/**
 * EmailAddress model
 *
 * @copyright (c) 2018, Intermesh BV http://www.intermesh.nl
 * @author Merijn Schering <mschering@intermesh.nl>
 * @license http://www.gnu.org/licenses/agpl-3.0.html AGPLv3
 */

class EmailAddress extends Property {
	
	
	const TYPE_WORK = "work";
	const TYPE_HOME = "home";
	const TYPE_BILLING = "billing";

	/**
	 * 
	 * @var int
	 */							
	protected int $contactId;

	/**
	 * 
	 * @var ?string
	 */							
	public ?string $type = 'work';

	/**
	 * 
	 * @var string
	 */							
	public string $email;

	protected static function defineMapping(): Mapping
	{
		return parent::defineMapping()
						->addTable("addressbook_email_address");
	}

	//For easier usage in templates
	public function __toString () {
		return $this->email;
	}

}