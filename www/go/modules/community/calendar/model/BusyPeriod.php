<?php
namespace go\modules\community\calendar\model;


use go\core\util\DateTime;

class BusyPeriod {

	public $debug;
	public $utcStart;
	public $utcEnd;
	public $busyStatus;

	private $includeInAvailability;

	public function __construct($event) {
		$this->debug = $event->title;
		$this->utcStart = $event->start; // todo to utc
		$this->utcEnd = $event->lastOccurrence; // todo to utc
		$this->busyStatus = $event->busyStatus;
	}

	/**
	 * Relevent events expanding recurring events and where the following is TRUE
	 *
	 * The principal is subscribed to the calendar.
	 * The “includeInAvailability” property of the calendar for the principal is “all” or “attending”.
	 * The user has the “mayReadFreeBusy” permission for the calendar.
	 * The event finishes after the “utcStart” argument and starts before the “utcEnd” argument.
	 * The event’s “privacy” property is not “secret”.
	 * The “freeBusyStatus” property of the event is “busy” (or omitted, as this is the default).
	 * The “status” property of the event is not “cancelled”.
	 * If the “includeInAvailability” property of the calendar is “attending”, then the principal is a participant of the event, and has a “participationStatus” of “accepted” or “tentative”.
	 * @return BusyPeriod[]
	 */
	static function fetch($id, $start, $end) {
//		$query = CalendarEvent::find();
//		$aclAlias = CalendarEvent::joinAclEntity($query); // if has ACL should have mayReadFreeBusy?
		$query = go()->getDbConnection()->select(['cce.eventId', 'start','duration','lastOccurrence','recurrenceRule',
			'par.participationStatus as busyStatus', 'calu.includeInAvailability as includeInAvailability', 'title', 'cal.ownerId', 'par.id as participantId'])
			->from('calendar_calendar_event', 'cce')
			->join('calendar_event', 'e', 'cce.eventId = e.eventId')
			->join('calendar_event_user', 'eu', 'eu.eventId = e.eventId AND eu.userId = '.(int)$id, 'LEFT')
			->join('calendar_calendar', 'cal', 'cal.id = cce.calendarId')
			->join('calendar_calendar_user', 'calu', 'cal.id = calu.id AND calu.userId = '.(int)$id) // if not found, p not subscribed
			->join('calendar_participant', 'par', 'par.eventId = cce.eventId AND par.id = '.(int)$id, 'LEFT')
			->where('cal.ownerId', '=', [$id, null])
			->andWhere('calu.isSubscribed', '=', 1)
			->andWhere('lastOccurrence', '>', new DateTime($start))
			->andWhere('start', '<', new DateTime($end))
			->andWhere('privacy', '!=', 'secret')
			->andWhere('freeBusyStatus', '=', ['busy',null])
			->andWhere('status', '!=', 'cancelled')
			->fetchMode(\PDO::FETCH_OBJ);

		$stmt = $query->execute();
		$list = [];
		foreach($stmt as $period) {
			if(!empty($period->recurrenceRule)) {

				self::expand($period);

			} else if(!empty($period->ownerId) || !empty($event->participantId)) {
				$list[$period->eventId] = new self($period);
			}
		}
		//ksort($list);

		return [
			'sql' => (string)$stmt,
			'list' => array_values($list)
		];
	}

	private static function expand($p) {

	}

}