<?php

namespace go\core\controller;

use go\core\jmap\EntityController;
use go\core\model;


class Link extends EntityController {
	
	/**
	 * The class name of the entity this controller is for.
	 * 
	 * @return string
	 */
	protected function entityClass() {
		return model\Link::class;
	}
	
	protected function paramsQuery(array $params) {
		$p =  parent::paramsQuery($params);
		
		if(empty($p['sort'])) {
			$p['sort'] = ['eTo.name ASC', 'createdAt DESC'];
		}
		
		return $p;
	}
}
