<?php

namespace go\core\controller;

use go\core\App;
use go\core\jmap\EntityController;
use go\core\model;
use go\core\orm\EntityType;
use go\core\orm\Query;
use go\core\util\ArrayObject;


class Link extends EntityController {
	
	/**
	 * The class name of the entity this controller is for.
	 * 
	 * @return string
	 */
	protected function entityClass(): string
	{
		return model\Link::class;
	}
	
	protected function paramsQuery(array $params): ArrayObject
	{
		$p =  parent::paramsQuery($params);
		
		if(empty($p['sort'])) {
			$p['sort'] = [
				["property" => "search.modifiedAt", "isAscending"=> false]
			];
		}
		
		return $p;
	}

	protected function getQueryQuery(ArrayObject $params): Query
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

	protected function getEntity(string $id, array $properties = [])
	{
		if(!is_numeric($id)) {
			//Support "Task-1-Contact-2" for identifying a task link
			$idParts = explode("-", $id);
			list($fromEntity, $fromId, $toEntity, $toId) = $idParts;

			$fromEntityTypeId = EntityType::findByName($fromEntity)->getId();
			$toEntityTypeId = EntityType::findByName($toEntity)->getId();

			$link = model\Link::find()
				->where([
					'fromEntityTypeId' => $fromEntityTypeId,
					'fromId' => $fromId,
					'toEntityTypeId' => $toEntityTypeId,
					'toId' => $toId
				])->single();

			if(!$link) {
				return false;
			}

			if(!$link->hasPermissionLevel(model\Acl::LEVEL_READ)) {
				App::get()->debug("Forbidden: Link: ".$id);
				return false; //not found
			}
			return $link;
		} else {
			return parent::getEntity($id, $properties);
		}
	}
}
