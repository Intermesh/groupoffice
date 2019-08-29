<?php
namespace go\modules\community\task\model;
						
use go\core\orm\Property;
						
/**
 * Alert model
 *
 * @copyright (c) 2019, Intermesh BV http://www.intermesh.nl
 * @author Merijn Schering <mschering@intermesh.nl>
 * @license http://www.gnu.org/licenses/agpl-3.0.html AGPLv3
 */

class Alert extends Property {
	
	/**
	 * 
	 * @var int
	 */							
	public $id;

	/**
	 * 
	 * @var int
	 */							
	public $taskId;

	/**
	 * 
	 * @var \go\core\util\DateTime
	 */							
	public $remindDate;

	/**
	 * 
	 * @var \go\core\util\Time
	 */							
	public $remindTime;

	protected static function defineMapping() {
		return parent::defineMapping()
						->addTable("task_alert", "alert");
	}

}