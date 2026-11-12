<?php

namespace go\modules\community\calendar\controller;

use go\core\db\Query;
use go\core\exception\Forbidden;
use go\core\jmap\Entity;
use go\core\jmap\EntityController;
use go\core\model\Acl;
use go\modules\community\calendar\model;


/**
 * @extends EntityController<\go\modules\community\calendar\model\Calendar>
 */
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

	/**
	 * Get the default calendar of the user and create it when it doesn't exist
	 *
	 * @apiNoParams
	 * @apiResponse {success: bool, calendarId: string, calendar: Calendar}
	 * @apiPermission Module access: returns or creates the default calendar owned by the user.
	 * @param $params
	 * @return array
	 * @throws \go\core\db\DbException
	 */
	public function first($params)
	{
		$user = go()->getAuthState()->getUser(['id', 'displayName', 'calendarPreferences']);
		$calendar = model\Calendar::createDefault($user);

		return [
			'success'=> true,
			'calendarId'=>$calendar->id,
			'calendar'=> $calendar->toArray()
		];

	}

	/**
	 * Import the events of a webcal subscription again, replacing the events in the calendar
	 *
	 * @apiParam string calendarId Required. ID of the calendar. Requires write permission.
	 * @apiPermission Write permission on the calendar.
	 * @param array $params
	 * @throws Forbidden
	 */
	public function reload($params) {
		$cal = model\Calendar::findById($params['calendarId']);
		if($cal) {

			if(!$cal->hasPermissionLevel(Acl::LEVEL_WRITE)) {
				throw new Forbidden("You do not have permission to read this calendar");
			}

			return $cal->importWebcal();
		}
		return ['success' => false];
	}

	public function changes($params) {
		return $this->defaultChanges($params);
	}

	protected function canCreate(Entity $entity): bool
	{
		return $this->rights->mayChangeCalendars;
	}

	protected function canDestroy(Entity $entity): bool
	{
		return $this->rights->mayChangeCalendars && $entity->hasPermissionLevel(Acl::LEVEL_DELETE);
	}
}
