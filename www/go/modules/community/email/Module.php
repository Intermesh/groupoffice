<?php
namespace go\modules\community\email;

use go\core;

use go\core\orm\Mapping;
use go\core\orm\Query;
use go\core\model;
use go\modules\community\calendar\model\Calendar;
use go\modules\community\calendar\model\CalendarEvent;
use go\modules\community\davclient\cron;
use go\modules\community\davclient\model\DavAccount;

class Module extends core\Module
{

	public function getAuthor(): string
	{
		return "Intermesh BV <mdhart@intermesh.nl>";
	}


	public static function getTitle(): string
	{
		return 'E-Mail';
	}

	protected function rights(): array
	{
		return [
			'mayChangeAccount', // allows EmailAccount/set
		];
	}

}