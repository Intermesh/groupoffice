<?php
namespace go\modules\community\task\model;
						
use go\core\jmap\Entity;
						
/**
 * PortletTasklist model
 *
 * @copyright (c) 2019, Intermesh BV http://www.intermesh.nl
 * @author Merijn Schering <mschering@intermesh.nl>
 * @license http://www.gnu.org/licenses/agpl-3.0.html AGPLv3
 */

class PortletTasklist extends Entity {
	
	/**
	 * 
	 * @var int
	 */							
	public $createdBy;

	/**
	 * 
	 * @var int
	 */							
	public $tasklistId;

	protected static function defineMapping() {
		return parent::defineMapping()
						->addTable("task_portlet_tasklist", "portlettasklist");
	}

	public static function getClientName() {
		return "PortletTasklist";
	}

}