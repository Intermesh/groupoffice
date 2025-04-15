<?php

namespace go\modules\community\email\controller;

use go\core\jmap\EntityController;
use go\modules\community\email\model;


class EmailAccount extends EntityController {
	
	/**
	 * The class name of the entity this controller is for.
	 * 
	 * @return string
	 */
	protected function entityClass(): string
	{
		return model\EmailAccount::class;
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

	public function fill($params){
		$account = model\EmailAccount::findById($params['accountId']);
		$backend = $account->connect();
		if ($backend) {
			return $backend->fill();
		}
	}

	public function sync($params) {

		$account = model\EmailAccount::findById($params['accountId']);
		$backend = $account->connect();
		if ($backend) {
			return $backend->sync($params['mailboxId']);
		}
	}
}
