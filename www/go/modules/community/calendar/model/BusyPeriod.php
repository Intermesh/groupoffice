<?php
namespace go\modules\community\calendar\model;


use go\core\db\Criteria;
use go\core\util\DateTime;

class BusyPeriod {

	public $debug;
	public $utcStart;
	public $utcEnd;
	public $busyStatus;

	private $includeInAvailability;

	public function __construct($event) {
		//$this->debug = $event->title;
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
		$query = go()->getDbConnection()->select(['cce.eventId', 'start','duration','lastOccurrence','recurrenceRule','e.timeZone',
			'par.participationStatus as busyStatus', 'title', 'cal.ownerId','cal.groupId', 'cce.calendarId', 'par.id as participantId'])
			->from('calendar_calendar_event', 'cce')
			->join('calendar_event', 'e', 'cce.eventId = e.eventId')
			->join('calendar_calendar', 'cal', 'cal.id = cce.calendarId');

		if(substr($id, 0, 9) == 'Calendar:') {
			$calendarId = str_replace('Calendar:', '', $id);
			$ownerId = 0;
		} else {
			$ownerId = $id;
			$calendarId = 0;
			$query->join('calendar_calendar_user', 'calu', 'cal.id = calu.id AND calu.userId = '.(int)$ownerId) // if not found, p not subscribed
				->andWhere('calu.isSubscribed', '=', 1)
				->select(['calu.includeInAvailability as includeInAvailability'], true);
		}

		$query
			->join('calendar_event_user', 'evu', 'evu.eventId = e.eventId AND evu.userId = '.(int)$ownerId, 'LEFT')
			->join('calendar_participant', 'par', 'par.eventId = cce.eventId AND par.id = '.(int)$ownerId, 'LEFT')
			->where((new Criteria())
				->where('cal.ownerId', '=', [$ownerId, null])
				->orWhere('cal.id', '=',$calendarId)
			)
			->andWhere((new Criteria())
				->where('lastOccurrence', '>', new DateTime($start))
				->orWhere('lastOccurrence', 'IS',null)
			)
			->andWhere('start', '<', new DateTime($end))
			->andWhere('privacy', '!=', 'secret')
			->andWhere('freeBusyStatus', '=', ['busy',null])
			->andWhere('e.status', '!=', 'cancelled')
			->fetchMode(\PDO::FETCH_OBJ);

		$stmt = $query->execute();
		$list = [];
		foreach($stmt as $period) {
			if((!empty($period->ownerId) && $period->ownerId == $ownerId && empty($period->groupId)) || // my calendar
				!empty($event->participantId) || // participating
				$calendarId == $period->calendarId // resource
			) {
				if(!empty($period->recurrenceRule)) {
					foreach(self::expand($period, $start, $end) as $rId => $instance) {
						$list[$period->eventId.'/'.$rId] = $instance;
					}
				} else {
					$list[$period->eventId] = new self($period);
				}
			}
		}
		//ksort($list);

		return [
			//'sql' => (string)$stmt,
			'list' => array_values($list)
		];
	}

	static function expand($p, string $from, string $until) {
		$it = ICalendarHelper::makeRecurrenceIterator((new CalendarEvent())->setValues([
			'start'=>new \DateTime($p->start),
			'recurrenceRule'=>json_decode($p->recurrenceRule),
			'timeZone'=>$p->timeZone
		]));
		$it->fastForward(new DateTime($from));
		if(!empty($p->lastOccurrence)) {
			$until = min($until, $p->lastOccurrence);
		}
		$maxDate = new \DateTime($until);
		while ($it->valid() && $it->current() < $maxDate) {
			$recurrenceId = $it->current();
			$instance = clone $p;
			$instance->utcStart = $recurrenceId->format('Y-m-d H:i:s');
			$o = @$p->recurrenceOverrides[$recurrenceId];
			$duration = $p->duration;
			if(isset($o)) {
				if($o->excluded) {
					$it->next();
					continue;
				}
				if($o->start) {
					$instance->utcStart = $o->start;
				}
				if($o->duration) {
					$duration = $o->duration;
				}
			}

			$end = new DateTime($instance->utcStart);
			$end->add(new \DateInterval($duration));
			$instance->utcEnd = $end->format('Y-m-d H:i:s');

			yield $recurrenceId->format('Y-m-d\TH:i:s') => $instance;
			$it->next();
		}
	}

}