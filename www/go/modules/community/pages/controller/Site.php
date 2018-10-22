<?php
namespace go\modules\community\pages\controller;

use go\core\jmap\EntityController;
use go\modules\community\pages\model;

/**
 * The controller for the Site entity
 *
 * @copyright (c) 2018, Intermesh BV http://www.intermesh.nl
 * @author Merijn Schering <mschering@intermesh.nl>
 * @license http://www.gnu.org/licenses/agpl-3.0.html AGPLv3
 */ 
class Site extends EntityController {
	
	/**
	 * The class name of the entity this controller is for.
	 * 
	 * @return string
	 */
	protected function entityClass() {
		return model\Site::class;
	}	
}

