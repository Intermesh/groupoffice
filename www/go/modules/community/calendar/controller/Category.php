<?php

namespace go\modules\community\calendar\controller;

use go\core\exception\Forbidden;
use go\core\jmap\Entity;
use go\core\jmap\EntityController;
use go\core\model\Acl;
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
		return $this->defaultSet($params);
	}

	public function changes($params) {
		return $this->defaultChanges($params);
	}

	protected function canCreate(Entity $entity): bool
	{
		if($entity->calendarId) {
			$cal = model\Calendar::findById($entity->calendarId);
			return $cal->hasPermissionLevel(Acl::LEVEL_MANAGE);
		}
		return $this->rights->mayChangeCategories;
	}
}