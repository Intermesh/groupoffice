<?php
namespace go\modules\core\users\model;

use go\core;
use go\core\db\Query;

class Settings extends core\Settings {

	public function getModuleName() {
		return 'users';
	}

	
	/**
	 * Default country
	 * @var string
	 */
	public $defaultCountry = "NL";
	
	public $defaultTimezone = "europe/amsterdam";
	
	public $defaultDateFormat = "d-m-Y";
	
	public $defaultTimeFormat = "G:i";
	
	public $defaultCurrency = "€";
	
	/**
	 * Default first week day
	 * 
	 * 0 = sunday
	 * 1 = monday
	 * 
	 * @var int 
	 */
	public $defaultFirstWeekday = 1;
	
	
	
	
	
	
	
	
}
