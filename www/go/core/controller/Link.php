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
			$p['sort'] = [
				["property" => "search.modifiedAt", "isAscending"=> false]
			];
		}
		
		return $p;
	}

	protected function getQueryQuery($params)
	{
		$q = parent::getQueryQuery($params)
			->groupBy([])
			->distinct();

		$order = $q->getOrderBy();
		if(empty($order['eTo.name'])) {
			$q->removeJoin('core_entity', 'eTo');
		}

		if(empty($order['eFrom.name'])) {
			$q->removeJoin('core_entity', 'eFrom');
		}

		return $q;
	}

	/**
	 * Handles the Foo entity's Foo/query command
	 * 
	 * @param array $params
	 * @see https://jmap.io/spec-core.html#/query
	 */
	public function query($params) {
		return $this->defaultQuery($params);
	}
	
	/**
	 * Handles the Foo entity's Foo/get command
	 * 
	 * @param array $params
	 * @see https://jmap.io/spec-core.html#/get
	 */
	public function get($params) {
		return $this->defaultGet($params);
	}
	
	/**
	 * Handles the Foo entity's Foo/set command
	 * 
	 * @see https://jmap.io/spec-core.html#/set
	 * @param array $params
	 */
	public function set($params) {
		return $this->defaultSet($params);
	}
	
	
	/**
	 * Handles the Foo entity's Foo/changes command
	 * 
	 * @param array $params
	 * @see https://jmap.io/spec-core.html#/changes
	 */
	public function changes($params) {
		return $this->defaultChanges($params);
	}
}
