<?php

namespace go\modules\community\calendar\controller;

use go\core\exception\Forbidden;
use go\core\jmap\EntityController;
use go\modules\community\calendar\model;


class ResourceGroup extends EntityController {
	
	/**
	 * The class name of the entity this controller is for.
	 * 
	 * @return string
	 */
	protected function entityClass(): string
	{
		return model\ResourceGroup::class;
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
		if(!$this->rights->mayChangeResources)
			throw new Forbidden('Permission denied');
		return $this->defaultGet($params);
	}
	
	/**
	 * Handles the Foo entity's Foo/set command
	 * 
	 * @see https://jmap.io/spec-core.html#/set
	 * @param array $params
	 * @param bool $onDestroyRemoveEvents If false, any attempt to destroy a Calendar that still has CalendarEvents in
	 * 	it will be rejected with a calendarHasEvent SetError. If true, any CalendarEvents that were in the Calendar
	 * 	will be removed from it, and if in no other Calendars they will be destroyed. This SHOULD NOT send scheduling
	 * 	messages to participants or create CalendarEventNotification objects.
	 */
	public function set($params) {
		if(!$this->rights->mayChangeResources)
			throw new Forbidden('Permission denied');
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
