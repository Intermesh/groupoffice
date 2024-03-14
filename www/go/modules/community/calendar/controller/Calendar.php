<?php

namespace go\modules\community\calendar\controller;

use go\core\db\Query;
use go\core\jmap\EntityController;
use go\modules\community\calendar\model;


class Calendar extends EntityController {

	protected function entityClass(): string {
		return model\Calendar::class;
	}	

	public function query($params) {
		return $this->defaultQuery($params);
	}

	public function get($params) {
		return $this->defaultGet($params);
	}
	
	/**
	 * @param array $params
	 * @param bool $onDestroyRemoveEvents If false, any attempt to destroy a Calendar that still has CalendarEvents in
	 * 	it will be rejected with a calendarHasEvent SetError. If true, any CalendarEvents that were in the Calendar
	 * 	will be removed from it, and if in no other Calendars they will be destroyed. This SHOULD NOT send scheduling
	 * 	messages to participants or create CalendarEventNotification objects.
	 */
	public function set($params) {
		if(!empty($params['onDestroyRemoveEvents']) && isset($params['destroy'])) {
				go()->getDbConnection()->delete('calendar_calendar_event',
					(new Query())->where('calendarId', 'IN', $params['destroy']));
		}
		return $this->defaultSet($params);
	}

	public function changes($params) {
		return $this->defaultChanges($params);
	}
}
