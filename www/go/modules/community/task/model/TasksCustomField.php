<?php
namespace go\modules\community\task\model;
						
use go\core\orm\Property;
						
/**
 * TasksCustomField model
 *
 * @copyright (c) 2019, Intermesh BV http://www.intermesh.nl
 * @author Merijn Schering <mschering@intermesh.nl>
 * @license http://www.gnu.org/licenses/agpl-3.0.html AGPLv3
 */

class TasksCustomField extends Property {
	
	/**
	 * 
	 * @var int
	 */							
	public $id;

	protected static function defineMapping() {
		return parent::defineMapping()
						->addTable("task_tasks_custom_field", "taskscustomfield");
	}

}