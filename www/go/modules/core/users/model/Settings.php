<?php
namespace go\modules\core\users\model;

use go\core;
use go\core\db\Query;

class Settings extends core\Settings {
	
	/**
	 * Default time zone for users
	 * 
	 * @var string
	 */
	public $defaultTimezone = "Europe/Amsterdam";
	
	/**
	 * Default date format for users
	 * 
	 * @link https://secure.php.net/manual/en/function.date.php
	 * @var string
	 */
	public $defaultDateFormat = "d-m-Y";
	
	/**
	 * Default time format for users
	 * 
	 * @link https://secure.php.net/manual/en/function.date.php
	 * @var string 
	 */
	public $defaultTimeFormat = "G:i";
	
	/**
	 * Default currency
	 * @var string
	 */
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
	
	
	/**
	 * Default list separator for import and export
	 * 
	 * @var string
	 */
	public $defaultListSeparator = ';';
	
	/**
	 * Default text separator for import and export
	 * 
	 * @var string
	 */
	public $defaultTextSeparator = '"';
	
	/**
	 * Default thousands separator for numbers
	 * @var string
	 */
	public $defaultThousandSeparator = '.';
	
	/**
	 * Default decimal separator for numbers
	 * 
	 * @var string
	 */
	public $defaultDecimalSeparator = ',';
	
	
	
	protected $defaultGroups;
	
	public function getDefaultGroups() {		
		return empty($this->defaultGroups) ? [] : json_decode($this->defaultGroups, true);
	}
	
	public function setDefaultGroups($groups) {
	
		$this->defaultGroups = json_encode($groups);
	}
	
}
