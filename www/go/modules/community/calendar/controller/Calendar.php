<?php

namespace go\modules\community\calendar\controller;

use go\core\db\Query;
use go\core\exception\Forbidden;
use go\core\jmap\Entity;
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

	/**
	 * @param $params
	 * @return array
	 * @throws \go\core\db\DbException
	 */
	public function first($params)
	{
		$user = go()->getAuthState()->getUser(['id', 'displayName', 'calendarPreferences']);
		if (!empty($user->calendarPreferences->defaultCalendarId)) {
			$calendar = model\Calendar::findById($user->calendarPreferences->defaultCalendarId);
		}
		if (empty($calendar)) {
			$calendar = model\Calendar::find()->where(['ownerId' => $user->id])->single();
			if (!empty($calendar)) {
				if ($calendar->getPermissionLevel() < 50) {
					$calendar = null;
				}
			}
		}
		if (empty($calendar)) {
			$calendar = new model\Calendar();
			$calendar->setValues([
				'name' => $user->displayName,
				'ownerId' => $user->id,
				'color' => model\Calendar::randomColor($user->displayName)
			]);
			$calendar->save();
		}

		//subscribe user
		go()->getDbConnection()->replace('calendar_calendar_user', [
			'userId'=>$user->id,
			'id'=>$calendar->id,
			'isSubscribed'=>true,
			'includeInAvailability'=>'all',
			'color'=>$calendar->getColor()
		])->execute();

		$user->calendarPreferences->defaultCalendarId = $calendar->id;

		return [
			'success'=>$user->save(),
			'calendarId'=>$calendar->id
		];

	}

	public function changes($params) {
		return $this->defaultChanges($params);
	}

	protected function canCreate(Entity $entity): bool
	{
		return $this->rights->mayChangeCalendars;
	}
}
