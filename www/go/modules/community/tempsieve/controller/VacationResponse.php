<?php

namespace go\modules\community\tempsieve\controller;

use go\core\jmap\EntityController;
use go\core\util\ArrayObject;
use go\modules\community\tempsieve\model;

class VacationResponse extends EntityController
{
	use SieveControllerTrait;
	/**
	 * @inheritDoc
	 */
	protected function entityClass(): string
	{
		return model\VacationResponse::class;
	}

	public function get(array $params): ArrayObject
	{
		return new ArrayObject($params); // stub
	}

	public function set(array $params): ArrayObject
	{
		return new ArrayObject($params); // stub
	}

}