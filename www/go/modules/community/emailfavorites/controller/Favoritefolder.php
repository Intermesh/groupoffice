<?php

namespace go\modules\community\emailfavorites\controller;

use go\core\jmap\EntityController;
use go\core\util\ArrayObject;

class Favoritefolder extends EntityController
{
	public function query(array $params): ArrayObject
	{
		return $this->defaultQuery($params);
	}

	public function get(array $params): ArrayObject
	{
		return $this->defaultGet($params);
	}

	public function set(array $params): ArrayObject
	{
		return $this->defaultSet($params);
	}

	public function changes(array $params): ArrayObject
	{
		return $this->defaultChanges($params);
	}
	protected function entityClass(): string
	{
		return \go\modules\community\emailfavorites\model\Favoritefolder::class;
	}
}