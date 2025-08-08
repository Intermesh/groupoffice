<?php
namespace go\modules\community\addressbook\model;
						
use go\core\orm\Mapping;
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
	const TYPE_MOBILE = "cell";
	const TYPE_WORK_MOBILE = "workcell";
	const TYPE_WORK = "work";
	const TYPE_FAX = "fax";
	const TYPE_WORK_FAX = "workfax";

	protected int $contactId;
	public ?string $type = 'cell';
	public string $number;

	protected static function defineMapping(): Mapping
	{
		return parent::defineMapping()
						->addTable("addressbook_phone_number");
	}

	//For easier usage in templates
	public function __toString () {
		return $this->number;
	}

}