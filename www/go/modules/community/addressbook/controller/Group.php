<?php
namespace go\modules\community\addressbook\controller;

use go\core\jmap\EntityController;
use go\modules\community\addressbook\model;

/**
 * The controller for the Group entity
 *
 * @copyright (c) 2018, Intermesh BV http://www.intermesh.nl
 * @author Merijn Schering <mschering@intermesh.nl>
 * @license http://www.gnu.org/licenses/agpl-3.0.html AGPLv3
 */ 
class Group extends EntityController {
	
	/**
	 * The class name of the entity this controller is for.
	 * 
	 * @return string
	 */
	protected function entityClass() {
		return model\Group::class;
	}	
}

