<?php

namespace go\modules\community\webpush\controller;

use go\core\jmap\EntityController;
use go\modules\community\webpush\model;

class PushSubscription extends EntityController {

	protected function entityClass(): string
	{
		return model\PushSubscription::class;
	}

	// TODO: this function should not exist but GOUI JMAP datasource does not know how to handle a /get without ids
	public function query($params)
	{
		return $this->defaultQuery($params);
	}
	public function get($params)
	{
		return $this->defaultGet($params);
	}

	public function set($params)
	{
		return $this->defaultSet($params);
	}
}