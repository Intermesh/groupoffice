<?php

namespace go\modules\community\privacy\model;

use go\core;
use go\core\orm\Mapping;

final class Settings extends core\SettingsEntity
{
	protected $id = 1;
	public $warnXDaysBeforeDeletion;

	public $monitorAddressBooks;

	public $trashAddressBook;

	public $trashAfterXDays;

	protected static function defineMapping(): Mapping
	{
		return parent::defineMapping()
			->addTable('community_privacy_settings');
	}

}