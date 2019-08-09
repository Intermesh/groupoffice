<?php
namespace go\modules\community\task\model;
						
use go\core\jmap\Entity;

/**
 * Category model
 *
 * @copyright (c) 2019, Intermesh BV http://www.intermesh.nl
 * @author Merijn Schering <mschering@intermesh.nl>
 * @license http://www.gnu.org/licenses/agpl-3.0.html AGPLv3
 */

class Category extends Entity {
	
	/**
	 * 
	 * @var int
	 */							
	public $id;

	/**
	 * 
	 * @var string
	 */							
	public $name;

	/**
	 * 
	 * @var int
	 */							
	public $createdBy;

	protected static function defineMapping() {
		return parent::defineMapping()
						->addTable("task_category", "category");
	}

	public static function getClientName() {
		return "TasksCategory";
	}

}