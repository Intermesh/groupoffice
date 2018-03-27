<?php
namespace go\modules\core\users\model;

use go\core;
use go\core\db\Query;

class Settings extends core\Settings {

	public function getModuleName() {
		return 'users';
	}
	
	public $defaultTimezone = "Europe/Amsterdam";
	
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
	
	
	public $defaultListSeparator = ';';
	
	public $defaultTextSeparator = '"';
	public $defaultThousandSeparator = '.';
	
	public $defaultDecimalSeparator = ',';
	
	
	
	protected $defaultGroups;
	
	public function getDefaultGroups() {		
		return empty($this->defaultGroups) ? [] : json_decode($this->defaultGroups, true);
	}
	
	public function setDefaultGroups($groups) {
	
		$this->defaultGroups = json_encode($groups);
	}
	
}
