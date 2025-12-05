<?php

namespace go\modules\community\oidc\controller;


use go\core\jmap\EntityController;
use go\modules\community\oidc\model;

final class Client extends EntityController
{

	protected function authenticate()
	{
		return true;
	}

	public function entityClass(): string
	{
		return model\Client::class;
	}

	public function query(array $params)
	{
		return $this->defaultQuery($params);
	}

	public function get(array $params)
	{
		return $this->defaultGet($params);
	}

	public function set(array $params)
	{
		return $this->defaultSet($params);
	}

	public function changes(array $params)
	{
		return $this->defaultChanges($params);
	}
}