<?php

namespace go\modules\community\calendar\controller;

use go\core\exception\Forbidden;
use go\core\jmap\EntityController;
use go\modules\community\calendar\model;


class Category extends EntityController {
	

	protected function entityClass(): string
	{
		return model\Category::class;
	}	

	public function query($params) {
		return $this->defaultQuery($params);
	}

	public function get($params) {
		return $this->defaultGet($params);
	}

	public function set($params) {
		if(!$this->rights->mayChangeCategories)
			throw new Forbidden('Permission denied');
		return $this->defaultSet($params);
	}

	public function changes($params) {
		return $this->defaultChanges($params);
	}
}