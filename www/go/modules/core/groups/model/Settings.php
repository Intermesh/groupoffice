<?php
namespace go\modules\core\groups\model;

use Exception;
use go\core;
use function GO;

class Settings extends core\Settings {
	

	/**
	 * New groups will be shared with these default groups
	 * 
	 * @return int[]
	 */
	public function getDefaultGroups() {		
		return array_map("intval", (new core\db\Query)
						->selectSingleValue('groupId')
						->from("core_group_default_group")
						->all());

	}
	
	/**
	 * Set default groups for new groups
	 * 
	 * @param array eg [['groupId' => 1]]
	 */
	public function setDefaultGroups($groups) {	
		core\db\Table::getInstance("core_group_default_group")->truncate();
		
		foreach($groups as $groupId) {
			if(!GO()->getDbConnection()->insert("core_group_default_group", ['groupId' => $groupId])->execute()) {
				throw new Exception("Could not save group id ".$groupId);
			}
		}
	}
	
}

