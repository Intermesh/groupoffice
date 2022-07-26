<?php

namespace go\modules\community\calendar\controller;

use go\core\jmap\EntityController;
use go\modules\community\calendar\model;


class Principal extends EntityController {

	/**
	 * The class name of the entity this controller is for.
	 *
	 * @return string
	 */
	protected function entityClass(): string
	{
		return model\Calendar::class;
	}

	/**
	 * Handles the Foo entity's Foo/query command
	 *
	 * @param array $params
	 * @see https://jmap.io/spec-core.html#/query
	 */
	public function getAvailability($params) {
		//todo
	}

}
