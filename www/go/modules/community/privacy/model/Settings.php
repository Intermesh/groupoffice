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

	public $trashAfterXMonths;

	protected static function defineMapping(): Mapping
	{
		return parent::defineMapping()
			->addTable('community_privacy_settings');
	}
//
//	public function getMonitorAddressBooks(): array
//	{
//		return !empty($this->monitorAddressBooks) ? explode(',', $this->monitorAddressBooks) : [];
//	}
//
//	public function setMonitorAddressBooks(array $v)
//	{
//		$this->monitorAddressBooks = implode(",", $v);
//	}
}