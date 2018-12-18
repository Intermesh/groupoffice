<?php
namespace go\modules\community\addressbook\controller;

use go\core\jmap\EntityController;
use go\modules\community\addressbook\model;

/**
 * The controller for the Contact entity
 *
 * @copyright (c) 2018, Intermesh BV http://www.intermesh.nl
 * @author Merijn Schering <mschering@intermesh.nl>
 * @license http://www.gnu.org/licenses/agpl-3.0.html AGPLv3
 */ 
class Contact extends EntityController {
	
	/**
	 * The class name of the entity this controller is for.
	 * 
	 * @return string
	 */
	protected function entityClass() {
		return model\Contact::class;
	}	
	
	
	
	protected function transformSort($sort) {
		$sort = parent::transformSort($sort);
		
		//merge sort on start to beginning of array
		return array_merge(['s.starred' => 'DESC'], $sort);
	}
}

