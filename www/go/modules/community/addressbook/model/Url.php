<?php
namespace go\modules\community\addressbook\model;
						
use go\core\orm\Mapping;
use go\core\orm\Property;
						
/**
 * Url model
 *
 * @copyright (c) 2018, Intermesh BV http://www.intermesh.nl
 * @author Merijn Schering <mschering@intermesh.nl>
 * @license http://www.gnu.org/licenses/agpl-3.0.html AGPLv3
 */

class Url extends Property {
	
	const TYPE_HOMEPAGE = "homepage";
	const TYPE_TWITTER = "twitter";
	const TYPE_FACEBOOK = "facebook";
	const TYPE_LINKEDIN = "linkedin";
	const TYPE_INSTAGRAM = "instagram";
	const TYPE_TIKTOK = "tiktok";

	protected int $contactId;
	public string $type;
	public string $url;

	protected static function defineMapping(): Mapping
	{
		return parent::defineMapping()
						->addTable("addressbook_url");
	}	

}