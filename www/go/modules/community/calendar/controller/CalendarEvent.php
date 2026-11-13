<?php

namespace go\modules\community\calendar\controller;

use go\core\fs\Blob;
use go\core\jmap\Entity;
use go\core\jmap\EntityController;
use go\core\jmap\exception\InvalidArguments;
use go\core\exception\Forbidden;
use go\core\model\Acl;
use go\core\util\StringUtil;
use go\core\util\UUID;
use GO\Email\Model\Account;
use go\modules\community\addressbook\convert\VCard;
use go\modules\community\calendar\model;
use go\modules\community\calendar\model\ICalendarHelper;
use go\modules\community\calendar\Module;


/**
 * CalendarEvent controller.
 * NOTE: The extra get and set parameter in the docblocks are according to RFC and might not (yet) be implemented
 *
 * @extends EntityController<\go\modules\community\calendar\model\CalendarEvent>
 */
class CalendarEvent extends EntityController {
	
	/**
	 * The class name of the entity this controller is for.
	 * 
	 * @return string
	 */
	protected function entityClass(): string
	{
		return model\CalendarEvent::class;
	}

	protected function canCreate(Entity $entity): bool
	{
		return $entity->hasPermissionLevel(30 /* writeown */);
	}
	
	/**
	 * @param array $params
	 *
	 * With "expandRecurrences" the IDs are the occurrences within the period, sorted by start. An occurrence of a
	 * recurring event has the ID "<event id>/<recurrence id>", which CalendarEvent/get accepts. Without it, the query
	 * works as before and returns the IDs of the events.
	 *
	 * @apiParam bool expandRecurrences Return an ID per occurrence of recurring events instead of one per event. The
	 *   filter must then be a single condition with both "after" and "before", and only "start" can be sorted on.
	 *   Occurrence IDs look like "<event id>/<recurrence id>" and work in CalendarEvent/get. Defaults to false.
	 * @apiParam string timeZone Time zone of the "after" and "before" filter conditions, like "Europe/Amsterdam".
	 *   Defaults to the user's time zone.
	 */
	public function query($params) {
		if (empty($params['expandRecurrences'])) {
			return $this->defaultQuery($params);
		}
		return $this->expandedQuery($params);
	}

	/**
	 * Occurrences can't be counted without computing them, so a query with a long period is refused
	 */
	const MAX_OCCURRENCES = 1000;

	/**
	 * CalendarEvent/query with "expandRecurrences"
	 *
	 * The events in the period are found with the normal query and expanded here, so the filters and permissions are
	 * the same as without expanding.
	 *
	 * @throws InvalidArguments
	 */
	private function expandedQuery(array $params): \go\core\util\ArrayObject
	{
		$filter = $params['filter'] ?? null;
		if (!is_array($filter) || isset($filter['operator']) || isset($filter['conditions']) || !isset($filter['after'], $filter['before'])) {
			throw new InvalidArguments('With expandRecurrences the filter must be a single condition with "after" and "before"');
		}

		$timeZone = $this->queryTimeZone($params['timeZone'] ?? null);
		try {
			$from = new \DateTime($filter['after'], $timeZone);
			$until = new \DateTime($filter['before'], $timeZone);
		} catch (\Exception $e) {
			throw new InvalidArguments('Invalid "after" or "before": ' . $e->getMessage());
		}
		if ($until <= $from) {
			throw new InvalidArguments('"before" must be later than "after"');
		}

		$descending = false;
		foreach ($params['sort'] ?? [] as $sort) {
			if (($sort['property'] ?? null) !== 'start') {
				throw new InvalidArguments('With expandRecurrences the results can only be sorted by "start"');
			}
			$descending = isset($sort['isAscending']) && !$sort['isAscending'];
		}

		// The events in the period. A day wider, because the filters may read the dates in another time zone. The
		// occurrences are checked exactly below.
		$candidateParams = [
			'filter' => array_merge($filter, [
				'after' => (clone $from)->modify('-1 day')->format('Y-m-d\TH:i:s'),
				'before' => (clone $until)->modify('+1 day')->format('Y-m-d\TH:i:s')
			]),
			'accountId' => $params['accountId'] ?? null
		];
		$candidates = $this->defaultQuery($candidateParams);

		$occurrences = [];
		foreach ($candidates['ids'] as $id) {
			$event = model\CalendarEvent::findById($id);
			if (!$event) {
				continue;
			}
			$left = self::MAX_OCCURRENCES + 1 - count($occurrences);
			foreach (model\Occurrences::between($event, $from, $until, $timeZone, $left) as $occurrence) {
				$occurrences[] = $occurrence;
			}
			if (count($occurrences) > self::MAX_OCCURRENCES) {
				throw new InvalidArguments('The period has more than ' . self::MAX_OCCURRENCES . ' occurrences. Use a shorter period.');
			}
		}

		usort($occurrences, fn($a, $b) => $descending ? $b['start'] <=> $a['start'] : $a['start'] <=> $b['start']);

		$ids = array_column($occurrences, 'id');
		$total = count($ids);
		$position = max(0, (int) ($params['position'] ?? 0));
		$limit = max(0, (int) ($params['limit'] ?? 0));
		$ids = array_slice($ids, $position, $limit ?: null);

		$response = new \go\core\util\ArrayObject([
			'accountId' => $candidates['accountId'],
			'state' => $candidates['queryState'],
			'queryState' => $candidates['queryState'],
			'ids' => $ids,
			'notfound' => [],
			'canCalculateUpdates' => false,
			'position' => $position
		]);
		if (!empty($params['calculateTotal'])) {
			$response['total'] = $total;
		}
		if (!empty($params['calculateHasMore']) && $limit > 0) {
			$response['hasMore'] = $position + $limit < $total;
		}
		return $response;
	}

	/**
	 * The time zone of the "after" and "before" filter of an expanded query, and of events without a time zone
	 *
	 * @throws InvalidArguments
	 */
	private function queryTimeZone(?string $name): \DateTimeZone
	{
		$name = $name ?: (go()->getAuthState()->getUser(['timezone'])->timezone ?? 'UTC');
		try {
			return new \DateTimeZone($name);
		} catch (\Exception $e) {
			throw new InvalidArguments('Invalid timeZone: ' . $name);
		}
	}
	
	/**
	 * @param array $params
	 * @param ?string $recurrenceOverridesBefore: UTCDate|null If given, only recurrence overrides with a recurrence id
	 * 	before this date (when translated into UTC) will be returned.
	 *	@param ?string $recurrenceOverridesAfter: UTCDate|null If given, only recurrence overrides with a recurrence id
	 * 	on or after this date (when translated into UTC) will be returned.
	 *	@param boolean $reduceParticipants: Boolean (default: false) If true, only participants with the “owner” role
	 * 	or corresponding to the user’s participant identities will be returned in the “participants” property
	 * 	of the base event and any recurrence overrides. If false, all participants will be returned.
	 *	@param string $timeZone: String (default “Etc/UTC”) The time zone to use when calculating the utcStart/utcEnd
	 * 	property of floating events. This argument has no effect if those properties are not requested.
	 *
	 * Also accepts the occurrence IDs "<event id>/<recurrence id>" of CalendarEvent/query with "expandRecurrences". An
	 * occurrence is returned as a single event, with "recurrenceId" and without "recurrenceRule" and
	 * "recurrenceOverrides". Read permission is checked on the event.
	 */
	public function get($params) {
		$ids = $params['ids'] ?? null;
		if (!is_array($ids) || empty(array_filter($ids, fn($id) => is_string($id) && str_contains($id, '/')))) {
			return $this->defaultGet($params);
		}
		return $this->getWithOccurrences($params, $ids);
	}

	private function getWithOccurrences(array $params, array $ids): \go\core\util\ArrayObject
	{
		$eventIds = [];
		$occurrenceIds = [];
		foreach ($ids as $id) {
			if (is_string($id) && str_contains($id, '/')) {
				$occurrenceIds[] = $id;
			} else {
				$eventIds[] = $id;
			}
		}

		$response = $this->defaultGet(array_merge($params, ['ids' => $eventIds]));
		$properties = $params['properties'] ?? null;

		$list = $response['list'];
		$notFound = $response['notFound'];
		foreach ($occurrenceIds as $id) {
			[$eventId, $recurrenceId] = explode('/', $id, 2);
			$event = ctype_digit($eventId) ? $this->getEntity($eventId) : false;
			$occurrence = $event ? model\Occurrences::toArray($event, $recurrenceId, $properties) : null;
			if ($occurrence === null) {
				$notFound[] = $id;
			} else {
				$list[] = $occurrence;
			}
		}

		// In the order of the given IDs
		$order = array_flip(array_map('strval', $ids));
		usort($list, fn($a, $b) => ($order[(string) $a['id']] ?? PHP_INT_MAX) <=> ($order[(string) $b['id']] ?? PHP_INT_MAX));

		$response['list'] = $list;
		$response['notFound'] = array_values($notFound);
		return $response;
	}
	
	/**
	 * @param array $params
	 * @param bool $sendSchedulingMessages (default: false) If true then any changes to scheduled events will be sent
	 * to all the participants (if the user is an owner of the event) or back to the owners (otherwise). If false, the
	 * changes only affect this account and no scheduling messages will be sent.
	 */
	public function set($params) {
		if(!empty($params['sendSchedulingMessages'])) {
			model\CalendarEvent::$sendSchedulingMessages = true;
		}
		model\CalendarEvent::$fromClient = true;
		return $this->defaultSet($params);
	}
	
	
	/**
	 * Handles the Foo entity's Foo/changes command
	 * 
	 * @param array $params
	 */
	public function changes($params) {
		return $this->defaultChanges($params);
	}


	/**
	 * Read an event from an ICS file or e-mail attachment without saving it
	 *
	 * Reads the ICS data from a file, or else from a part of an e-mail message. The event is put in the personal
	 * calendar of the user, when the user has one.
	 *
	 * @apiParam int fileId ID of a file with the ICS data
	 * @apiParam int account_id E-mail account ID, when no fileId is given
	 * @apiParam string mailbox Mailbox of the message
	 * @apiParam int uid UID of the message
	 * @apiParam string number Number of the message part with the ICS data
	 * @apiParam string encoding Encoding of the message part
	 * @apiResponse {success: bool, data: CalendarEvent} The event. It is not saved.
	 * @apiPermission Checked in ActiveRecord::findByPk(): read permission on the folder of the file, or on the e-mail account. The event is parsed but not saved.
	 * @param array $params
	 * @return array
	 */
	public function loadICS( array $params) :array
	{
		if(!empty($params['fileId'])) {
			$file = \GO\Files\Model\File::model()->findByPk($params['fileId']);
			if(!$file) {
				throw new \go\core\exception\NotFound();
			}
			$data = $file->fsFile->getContents();
		} else {
			$account = Account::model()->findByPk($params['account_id'] ?? 0);
			if(!$account) {
				throw new \go\core\exception\NotFound();
			}
			$imap = $account->openImapConnection($params['mailbox']);
			$data = $imap->get_message_part_decoded($params['uid'], $params['number'], $params['encoding'], false, true, false);
		}

		$event = new model\CalendarEvent();
		$personalCalendar = go()->getAuthState()->getUser(['calendarPreferences'])->calendarPreferences->personalCalendarId;
		if(!empty($personalCalendar)) {
			$event->calendarId = $personalCalendar;
		}

		//$event = model\ICalendarHelper::fromICal($data, $event);
		$event = ICalendarHelper::parseVObject($data, $event);
		return ['success' => true, 'data' => $event];

	}

	/**
	 * @param $params array 'blobIds' and 'calendarId', 'ignoreUid' are required
	 * @throws \Exception
	 */
	public function import($params) {
		$calendar = model\Calendar::findById($params['calendarId']);
		if(!$calendar || !$calendar->hasPermissionLevel(Acl::LEVEL_CREATE)) {
			throw new Forbidden("You don't have permission to add events to this calendar");
		}

		set_time_limit(0);
		return model\ICalendarHelper::import(
			$params['blobIds'],
			$params['calendarId'],
			!empty($params['ignoreUid']) ? 'new':'check'
		);
	}

	/**
	 * Count the upcoming invitations in the calendars of the user that still need a response. Used for the counter
	 * badge.
	 *
	 * @apiNoParams
	 * @apiResponse int
	 * @apiPermission Module access: the "inbox" filter only counts events in calendars owned by the user.
	 */
	public function countMine(): int
	{

		//$defaultListId = go()->getAuthState()->getUser(['tasksSettings'])->tasksSettings->getDefaultTasklistId();

		$query = model\CalendarEvent::find(['id'])
			->selectSingleValue("IFNULL(count(*), 0)")
			->filter(["inbox" => true,]);

//		$query->removeJoin("tasks_task_user");
//		$query->removeJoin("pr2_hours");
		//$query->groupBy([]);

		return $query->single();
	}

//	public function processInvite($params) {
//		$account = \GO\Email\Model\Account::model()->findByPk($params['accountId']);
//		$message = \GO\Email\Model\ImapMessage::model()->findByUid($account, $params['mailbox'],$params['uid']);
//		$vcalendar = $message->getInvitationVcalendar();
//
//		$from = $message->from->getAddress();
//
//		$event = model\Scheduler::processMessage($vcalendar, $params['scheduleId'], (object)[
//			'email' => $from['email'],
//			'name' => $from['personal'],
//		]);
//
//		return ['success'=>$event->save(), 'validation'=>$event->getValidationErrors()];
//	}
}
