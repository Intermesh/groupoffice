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
	 * Filters that don't say anything about the details of an event. Only with these, and sorted by start, a query
	 * returns the busy events of calendars with free / busy permission too. Other filters, like "text", would tell
	 * the user what the events are about by which ones come back.
	 */
	const BUSY_ONLY_FILTERS = ['incalendars', 'after', 'before', 'hidecancelled', 'hidesecret', 'permissionlevel'];

	/**
	 * The events are limited by {@see model\CalendarEvent::applyVisibleToQuery()} instead of the default read
	 * permission. Also when the client passes a "permissionLevel" filter, so a lower level can't find more events.
	 */
	protected function getQueryQuery(\go\core\util\ArrayObject $params): \go\core\orm\Query
	{
		$query = parent::getQueryQuery($params);
		model\CalendarEvent::applyVisibleToQuery($query,
			self::onlyBusyOnlyFilters($params['filter'] ?? []) && self::onlyStartSort($params['sort'] ?? []));
		return $query;
	}

	protected function applyPermissionLevelToQueryQuery(\go\core\orm\Query $query)
	{
		// Done in getQueryQuery()
	}

	/**
	 * Check if a JMAP filter only has {@see BUSY_ONLY_FILTERS}. Empty values don't filter, so they're allowed.
	 */
	private static function onlyBusyOnlyFilters(mixed $filter): bool
	{
		if(!is_array($filter)) {
			return empty($filter);
		}
		if(isset($filter['operator']) || isset($filter['conditions'])) {
			foreach($filter['conditions'] ?? [] as $condition) {
				if(!self::onlyBusyOnlyFilters($condition)) {
					return false;
				}
			}
			return true;
		}
		foreach($filter as $name => $value) {
			if(!in_array(strtolower((string) $name), self::BUSY_ONLY_FILTERS) && !($value === null || $value === '' || $value === [])) {
				return false;
			}
		}
		return true;
	}

	/**
	 * Check if a JMAP sort is empty or only by "start"
	 */
	private static function onlyStartSort(mixed $sort): bool
	{
		if(!is_array($sort)) {
			return empty($sort);
		}
		foreach($sort as $s) {
			if(($s['property'] ?? null) !== 'start') {
				return false;
			}
		}
		return true;
	}

	/**
	 * Read permission, or free / busy permission for the busy events ({@see model\CalendarEvent::busyCriteria()}).
	 * Their details are left out by {@see model\CalendarEvent::toArray()}.
	 */
	protected function canRead(Entity $entity): bool
	{
		/** @var model\CalendarEvent $entity */
		$level = $entity->getPermissionLevel();
		if($level >= Acl::LEVEL_READ) {
			return true;
		}
		return $level >= model\Calendar::LEVEL_FREEBUSY && $entity->isVisibleAsBusy();
	}

	/**
	 * Users that may only read the free / busy information may change nothing, also not their own properties like
	 * alerts. An alert would show the title.
	 */
	protected function canUpdate(Entity $entity): bool
	{
		/** @var model\CalendarEvent $entity */
		if(!$entity->hasPermissionLevel(Acl::LEVEL_READ) || !parent::canUpdate($entity)) {
			return false;
		}
		// The admin of a resource may only answer a private booking, see canUpdateBeforePatch()
		return !$entity->showAsPrivate() || $entity->onlyResourceAnswerModified();
	}

	/**
	 * The properties of an event that belong to the user, which may change on a private event of someone else
	 */
	const OWN_PROPERTIES = ['color', 'keywords', 'freeBusyStatus', 'useDefaultAlerts', 'alerts'];

	/**
	 * A private event of someone else is read only, because the user doesn't get its details, like the participants.
	 * Only the user's own properties, like the color and alerts, may change. Checked on the event as it is stored, so
	 * making it public in the same update doesn't help.
	 *
	 * The admins of a resource may also answer a private booking for the resource. canUpdate() checks that the
	 * participation status of the resource is the only change to the participants.
	 */
	protected function canUpdateBeforePatch(Entity $entity, array $properties): bool
	{
		/** @var model\CalendarEvent $entity */
		if(!$entity->showAsPrivate()) {
			return true;
		}
		$allowed = $entity->managesResource() ? [...self::OWN_PROPERTIES, 'participants'] : self::OWN_PROPERTIES;
		foreach(array_keys($properties) as $path) {
			if(!in_array(explode('/', (string) $path)[0], $allowed)) {
				return false;
			}
		}
		return true;
	}

	/**
	 * A private event of someone else is read only, see {@see canUpdateBeforePatch()}
	 */
	protected function canDestroy(Entity $entity): bool
	{
		/** @var model\CalendarEvent $entity */
		return !$entity->showAsPrivate() && parent::canDestroy($entity);
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

		// In the order of the given IDs. Listeners may add objects, like the leave days of the leavedays module.
		$order = array_flip(array_map('strval', $ids));
		$position = fn($item) => $order[(string) (is_array($item) ? $item['id'] : $item->id)] ?? PHP_INT_MAX;
		usort($list, fn($a, $b) => $position($a) <=> $position($b));

		$response['list'] = $list;
		$response['notFound'] = array_values($notFound);
		return $response;
	}
	
	/**
	 * @param array $params
	 *
	 * @apiParam bool sendSchedulingMessages Send the changes of events with participants by e-mail (iTIP): invitations,
	 *   updates and cancellations to the participants when the user is the organizer, or a reply to the organizer
	 *   when the user is a participant. Without it, the changes are only saved and nobody is notified. Defaults to
	 *   false.
	 */
	public function set($params) {
		// Restore the flags afterwards: one request (a JMAP batch or MCP call) may run more than one set
		$sendSchedulingMessages = model\CalendarEvent::$sendSchedulingMessages;
		$fromClient = model\CalendarEvent::$fromClient;
		if(!empty($params['sendSchedulingMessages'])) {
			model\CalendarEvent::$sendSchedulingMessages = true;
		}
		model\CalendarEvent::$fromClient = true;
		try {
			return $this->defaultSet($params);
		} finally {
			model\CalendarEvent::$sendSchedulingMessages = $sendSchedulingMessages;
			model\CalendarEvent::$fromClient = $fromClient;
		}
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
	 * calendar of the user, when the user has one. When the event is already in a calendar the user owns, that event
	 * is returned with its id.
	 *
	 * @apiParam int fileId ID of a file with the ICS data
	 * @apiParam int account_id E-mail account ID, when no fileId is given
	 * @apiParam string mailbox Mailbox of the message
	 * @apiParam int uid UID of the message
	 * @apiParam string number Number of the message part with the ICS data
	 * @apiParam string encoding Encoding of the message part
	 * @apiResponse {success: bool, data: CalendarEvent} The event. It is not saved, unless it was already in a calendar of the user.
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

		return ['success' => true, 'data' => $this->eventFromIcs($data)];
	}

	/**
	 * The event of the ICS data. When the event is already in a calendar of the user, for example an invitation that
	 * the user received, that event is returned, so the user responds to it instead of adding a copy.
	 */
	protected function eventFromIcs(string $data): model\CalendarEvent
	{
		$event = new model\CalendarEvent();
		$personalCalendar = go()->getAuthState()->getUser(['calendarPreferences'])->calendarPreferences->personalCalendarId;
		if(!empty($personalCalendar)) {
			$event->calendarId = $personalCalendar;
		}

		$event = ICalendarHelper::parseVObject($data, $event);

		if(!empty($event->uid)) {
			$existing = model\CalendarEvent::findForUser($event->uid, go()->getUserId())
				->andWhere('recurrenceId', '=', $event->recurrenceId)
				->single();
			if($existing) {
				return $existing;
			}
		}
		return $event;

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
