<?php
namespace go\modules\community\history\model;
use go\core;


class Settings extends core\Settings {
		
	/**
	 * Delete history after this number of days
	 * 
	 * @var int
	 */
	public $deleteAfterDays = 365;

}
