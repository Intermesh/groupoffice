<?php
namespace go\modules\community\addressbook\model;
						
use go\core\orm\Mapping;
use go\core\orm\Property;
						
/**
 * Date model
 *
 * @copyright (c) 2018, Intermesh BV http://www.intermesh.nl
 * @author Merijn Schering <mschering@intermesh.nl>
 * @license http://www.gnu.org/licenses/agpl-3.0.html AGPLv3
 */

class Date extends Property {
	
	const TYPE_BIRTHDAY = 'birthday';
	
	const TYPE_ANNIVERSARY = 'anniversary';

	const TYPE_ACTION = 'action';

	protected int $contactId;

	public ?string $type = self::TYPE_BIRTHDAY;

	public \DateTime $date;

	protected static function defineMapping(): Mapping
	{
		return parent::defineMapping()
						->addTable("addressbook_date");
	}

}