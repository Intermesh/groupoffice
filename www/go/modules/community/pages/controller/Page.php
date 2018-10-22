<?php
namespace go\modules\community\pages\controller;

use go\core\jmap\EntityController;
use go\modules\community\pages\model;

/**
 * The controller for the Page entity
 *
 * @copyright (c) 2018, Intermesh BV http://www.intermesh.nl
 * @author Merijn Schering <mschering@intermesh.nl>
 * @license http://www.gnu.org/licenses/agpl-3.0.html AGPLv3
 */ 
class Page extends EntityController {
	
	/**
	 * The class name of the entity this controller is for.
	 * 
	 * @return string
	 */
	protected function entityClass() {
		return model\Page::class;
	}

	private function createEntitites($create, &$result) {
//	    foreach ($create as $clientId) {
//		if($clientId['pageName']){
//		    $create[$clientId]['slug'] = $clientId['pageName'];
//		}
//		if($clientId['content']){
//		    $create[$clientId]['plainContent'] = $clientId['content'];
//		}
//		$create[$clientId]['pageName'] = "test";
//	    }
	    parent::createEntitites();
		
	}	
}

