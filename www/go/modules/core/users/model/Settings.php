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
	
	/**
	 * Default setting for users to have short date and times in lists.
	 * @var boolean
	 */
	public $defaultShortDateInList = true;
	
	/**
	 * New users will be member of these default groups
	 * 
	 * @return int[]
	 */
	public function getDefaultGroups() {		
		return array_map("intval", (new core\db\Query)
						->selectSingleValue('groupId')
						->from("core_user_default_group")
						->all());
	}
	
	/**
	 * Set default groups for new users
	 * 
	 * @param int[]
	 */
	public function setDefaultGroups($groups) {	
		core\db\Table::getInstance("core_user_default_group")->truncate();
		
		foreach($groups as $groupId) {
			if(!GO()->getDbConnection()->insert("core_user_default_group", ['groupId' => $groupId])->execute()) {
				throw new Exception("Could not save group id ".$groupId);
			}
		}
	}
	
}
