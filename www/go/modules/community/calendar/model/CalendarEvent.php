<?php
/*
 * @copyright (c) 2016, Intermesh BV http://www.intermesh.nl
 * @author Michael de Hart <mdhart@intermesh.nl>
 * @license http://www.gnu.org/licenses/agpl-3.0.html AGPLv3
 */

namespace go\modules\community\calendar\model;

use go\core\acl\model\AclItemEntity;
use go\core\fs\Blob;
use go\core\model\User;
use go\core\orm\CustomFieldsTrait;
use go\core\orm\Mapping;
use go\core\orm\Query;
use go\core\orm\SearchableTrait;
use go\core\util\UUID;
use go\core\util\DateTime;

/**
 * This serves as an in between record for the event that is in a calendar.
 * It merged Attendee with Event
 * @property Event $event Event object for this calendar event
 * @property Calendar $calendar The calendar this is in
 */
class CalendarEvent extends AclItemEntity {

	use CustomFieldsTrait;
	use SearchableTrait;

	/* Status */
	const Confirmed = 'confirmed'; // default
	const Cancelled = 'cancelled';
	const Tentative = 'tentative';

	/* Privacy */
	const Public = 'public';
	const Private = 'private';
	const Secret = 'secret';
	// Properties shown to others when 'privacy' is set to private
	const PrivateProperties = ['created', 'due', 'duration', 'estimatedDuration', 'freeBusyStatus', 'privacy',
		'recurrenceOverrides', 'sequence', 'showWithoutTime', 'start', 'timeZone', 'timeZones', 'uid','updated'];

	const EventProperties = ['uid','isOrigin', 'prodId', 'sequence','title','description','locale', 'showWithoutTime', 'start',
		'timeZone','duration','priority','privacy','status', 'recurrenceRule','createdAt','modifiedAt','createdBy','modifiedBy'];

	const UserProperties = ['keywords', 'color', 'freeBusyStatus', 'useDefaultAlerts', 'alerts','veventBlobId'];

	// If any of this properties is in the recurrenceOverrides the Object most be ignored
	const IgnoredPropertiesInException = [
		'uid', 'links', 'method', 'privacy', 'prodId',
		'recurrenceId', 'recurrenceOverrides','recurrenceRule',
		'relatedTo','replyTo','sentBy','timeZones'];


	public $calendarId;
	public $eventId;
	/**
	 * @var boolean true when this event is created by this calendar system
	 * false if the event is imported from an invitation and the organizer is in another system
	 */
	public $isOrigin;

	public $responseStatus;
	public $role;
	public $email;

	public $id;
	public $prodId;
	public $timeZone;
	public $locale;
	public $priority;
	public $color;
	public $useDefaultAlerts;
	/**
	 * A unique identifier for the object.
	 * @var string
	 */
	public $uid;

	/**
	 * This is a revision number that is increased by 1 every time the organizer
	 * makes a change (except when only the "participants" property is changed)
	 * @var int
	 */
	public $sequence = 0;

	/**
	 * Time is ignored for this event when true
	 * @var bool
	 */
	public $showWithoutTime = false;

	/**
	 * The start time of the event
	 * @var DateTime
	 */
	public $start;

	/**
	 * The duration of the event (or the occurence)
	 * (optional, default: PT0S)
	 * @var \DateInterval
	 */
	public $duration;

	/**
	 * The title
	 * @var string
	 */
	public $title = '';

	/**
	 * free text that would describe the event
	 * @var string
	 */
	public $description = '';

	/**
	 * The location where the event takes place
	 * @var string
	 */
	public $location;

	/**
	 * Status of event (confirmed, canceled, tentative)
	 * @var int
	 */
	public $status = self::Confirmed;

	/**
	 * auto tagging to give the event some flair. See Resource folder
	 * @var string[bool]
	 */
	public $keywords;

	/**
	 * Public, Private, Secret
	 * @var int
	 */
	public $privacy = self::Public;

	/**
	 * Is event Transparent or Opaque
	 * @var boolean
	 */
	public $freeBusyStatus = 'busy';

	/**
	 * @var string
	 */
	protected $recurrenceRule;
	protected $veventBlobId;

	public $participants = [];
	public $recurrenceOverrides = [];
	public $alerts = [];

	public $modifiedAt;
	public $createdAt;
	public $modifiedBy;
	public $createdBy;

	protected $lastOccurrence;

	public static function customFieldsTableName(): string
	{
		return 'calendar_event_custom_fields';
	}

	protected static function defineMapping(): Mapping {
		return parent::defineMapping()
			->addTable('calendar_calendar_event', 'cce', ['id' => 'eventId'], ['id', 'calendarId'])
			->addTable('calendar_event', "eventdata", ['eventId' => 'id'], self::EventProperties)
			->addUserTable('calendar_event_user', 'eventuser', ['id' => 'eventId'],self::UserProperties, [], true)
			//->addHasOne('recurrenceRule', RecurrenceRule::class, ['id' => 'eventId'])
			->addMap('participants', Participant::class, ['id' => 'eventId'])
			->addMap('recurrenceOverrides', RecurrenceOverride::class, ['id'=>'fk'])
			->addMap('alerts', Alert::class, ['id' => 'eventId']);
			//->addMap('locations', Location::class, ['id' => 'eventId']);
	}

	public function currentUserParticipant(): Participant {
		foreach($this->participants as $k => $participant) {
			if($k == 'u'.go()->getUserId()) {
				$me = $participant;
				break;
			}
		}
		return $me;
	}

	//public function getRecurrenceOverrides() {
		// todo merge general overrides with the per-user override properties
		// the following properties should be overriden per user:
		// - keywords
		// - color
		// - freeBusyStatus
		// - useDefaultAlerts
		// - alerts
		//return $this->recurrenceOverrides;
	//}


	public function attachBlob($blobId) {
		$this->veventBlobId = $blobId;
	}

	public function icsBlob() {

		$blob = isset($this->veventBlobId) ? Blob::findById($this->veventBlobId) : null;
		if(!$blob || $blob->modifiedAt < $this->modifiedAt) {
			$blob = ICalendarHelper::makeBlob($this);
			$this->attachBlob($blob->id);

			if(!$this->isNew()) {
				// save the blobId without updating the state (this property is not part of the spec
				$stmt = go()->getDbConnection()->update('calendar_event_user',
					['veventBlobId'=>$blob->id,'modifiedAt'=>$blob->modifiedAt],
					(new Query)
						->join('calendar_calendar_event', 'cce', 'cce.eventId = t.eventId')
						->where(['userId' => go()->getUserId(), 'id' => $this->id]));
				$stmt->execute();
			}
		}
		return $blob;
	}

	public function dateTimeZone() {
		if(!empty($this->timeZone)) {
			return new \DateTimeZone($this->timeZone);
		}
		static $currentTZUser;
		if(empty($currentTZUser)) {
			$currentTZUser = go()->getAuthState()->getUser(['dateFormat', 'timezone', 'timeFormat' ]);
			if(empty($currentTZUser)) {
				$currentTZUser = User::findById(1, ['dateFormat', 'timezone', 'timeFormat'], true);
			}
		}
		return new \DateTimeZone($currentTZUser->timezone);
	}

	public function icsBlobId() {
		if(!empty($this->veventBlobId)) {
			return $this->veventBlobId;
		}
		return $this->icsBlob()->id;
	}

	/**
	 * @return RecurrenceRule | null
	 */
	public function getRecurrenceRule() {
		return !empty($this->recurrenceRule) ? json_decode($this->recurrenceRule): null;
	}

	public function setRecurrenceRule($rule) {
		$this->recurrenceRule = empty($rule) ? null : json_encode($rule);
	}

	public function getTitle() {
		return $this->title;
	}
	/**
	 * Set the tag property when the title contains a certain word
	 * @todo function is not called when the attributes of events are set relational
	 * @param string $value Title of event
	 */
	public function setTitle($value) {
		$tags = require(dirname(__FILE__) . '/../tags/nl.php'); //<-- @todo: use users language
		$this->tag = null;
		foreach($tags as $tag => $possibleMatches) {
			foreach($possibleMatches as $possibleMatch) {
				if (stripos($value, $possibleMatch) !== false) {
					$this->keywords[] = $tag;
					break 2;
				}
			}
		}
		$this->title = $value;
	}

	public function start($withoutTime = false) {
		return new \DateTime($this->start->format("Y-m-d". $withoutTime?:" H:i:s"), $this->timeZone());
	}

	public function end($withoutTime = false) {

		$end = new DateTime($this->start->format("Y-m-d". $withoutTime?:" H:i:s"),$this->timeZone()) ;
		$end->add(new \DateInterval($this->duration));
		return $end;
	}

	/**
	 * @todo analyze rowCount performance
	 * @return bool
	 */
	public function hasAlarms() {
		return $this->alarms->getRowCount() > 0;
	}


	public function addAlarms($alarms) {
		$alarms = (array)$alarms;

		foreach($alarms as $alarm) {
			$alarm->addTo($this);
		}
	}

	protected function internalSave() : bool {

		if(empty($this->uid)) {
			$this->uid = UUID::v4();
		}

		if($this->isRecurring()) {

			if(isset($this->instance) && !$this->fromHere) {
				$this->instance->applyPatch($this);
				return $this->instance->save();
			} else if(!$this->isFirstInSeries()) {
				return $this->saveNewSeries();
			}
		}
		if($this->isNew() || $this->isModified('start') || $this->isModified('recurrenceRule')) {
			$this->findLastOccurence();
		}

		// is modiified, but not calendarId, isDraft or modifiedAt, per-user prop, participants
		if($this->isModified(self::EventProperties) &&
			$this->isOrigin &&
			(!$this->isModified('sequence') || $this->sequence <= $this->getOldValue('sequence'))) {
			$this->sequence += 1;
		}

		$success = parent::internalSave();
		if($success) {
			Calendar::updateHighestModSeq($this->calendarId);
			if($this->isModified('calendarId')) {
				Calendar::updateHighestModSeq($this->getOldValue('calendarId'));
			}
		}
		return $success;
		 // save none recurring or complete series
	}

	protected static function internalDelete(Query $q): bool
	{
//		if($this->isRecurring()) {
//			if(isset($this->instance) && !$this->fromHere) {
//				$this->instance->applyException(); // EXDATE
//				return $this->instance->save();
//			}
//			else if(!$this->isFirstInSeries()) {
//				$this->recurrenceRule->stopBefore($this->getRecurrenceId());
//				return $this->recurrenceRule->save();
//			}
//		}
		//TODO: update all calendars highest modseq with id = calendarId of the deleted ids

//		self::$lastDeleteStmt = go()->getDbConnection()->delete('calendar_event_user', (new Query)
//			->where('userId', '=', go()->getUserId())
//			->where('eventId', 'in', $q)
//		);
//		if(!self::$lastDeleteStmt->execute()) {
//			return false;
//		}
//		return true;
		$calendarModSeq = clone $q;
		$calIds = $calendarModSeq->selectSingleValue('calendarId')->distinct()->all();
		// Garbage collector will delete event when last user instance is removed
		$success =  parent::internalDelete($q); // delete none recurring or complete series
		if($success) {
			Calendar::updateHighestModSeq($calIds);
		}
		return $success;
	}

	public function saveFromHere() {
		$this->fromHere = true;
		$success = $this->save();
		$this->fromHere = false;
		return $success;
	}

	public function deleteFromHere() {
		$this->fromHere = true;
		$success = $this->delete();
		$this->fromHere = false;
		return $success;
	}

	protected function isFirstInSeries() {
		if($this->isNew() || empty($this->instance)) {
			return true;
		}
		$startAt = $this->isModified('startAt') ? $this->getOldValue('startAt') : $this->startAt;
		return ($this->instance->recurrenceId == $startAt);
	}

	/**
	 * Set the until time of this recurrence rule and create a new event
	 * with the same recurring rule starting from The occurrence start time
	 * @todo: if startAt in new series changes the moved exceptions need the same diff
	 */
	private function saveNewSeries() {
		$calendar = Calendar::findById($this->calendarId);
		$newSeries = $calendar->newEvent();
		$newSeries->eventId = null;
		$newSeries->event = $this->event->cloneMe();
		$newSeries->event->uid = UUID::v4();
		$newSeries->event->setStartAt($this->instance->recurrenceId);
		$newSeries->event->setEndAt($this->instance->getEndAt());
		$rrule = $this->recurrenceRule->toArray();
		$newSeries->event->setValues(['recurrenceRule'=>$rrule]);
		$success = $newSeries->save();

		// Reattach instances to new series
		foreach($this->instances as $instance) {
			if($instance->recurrenceId < $this->getRecurrenceId()) {
				continue;
			}
			$instance->eventId = $newSeries->id;
			$success = $success && $instance->save();
		}

		return $success && $this->deleteFromHere();

	}

	public function isRecurring() {
		return !empty($this->recurrenceRule); // && !empty($this->recurrenceRule->frequency));
	}

	public function timeZone() {
		return $this->timeZone ? new \DateTimeZone($this->timeZone) : null;
	}

	private function findLastOccurence() {
		if($this->isRecurring()) {
			$this->lastOccurrence = null;
			$r = $this->getRecurrenceRule();
			if(isset($r->until)) {
				$this->lastOccurrence = new DateTime($r->until,$this->timeZone());
			} else if(isset($r->count)) {
				$it = ICalendarHelper::makeRecurrenceIterator($r);
				$maxDate = new \DateTime('2038-01-01');
				while ($it->valid() && $this->lastOccurrence < $maxDate) {
					$this->lastOccurrence = $it->current(); // will clone :(
					$it->next();
				}
			}
		} else {
			$this->lastOccurrence = $this->end();
		}
	}

	private function sendMeetingRequest($cancel = false, $date = null) {
		// should we include resources?

		// should we book the resources when the invite is sent?

		if($this->isRecurring()) {
			$occurence = $this; // find occurence, apply exception if any, then submit
			$this->submitMeetingRequest($occurence, $cancel);
		} else {
			$this->submitMeetingRequest($this, $cancel);
		}
	}

	private function submitMeetingRequest($event, $cancel){
		$ical = ICalendarHelper::toVObject($this);


		go()->getMailer()->compose()
			->setFrom($this->organizer->email, $this->organizer->name)
			->addTo($this->participants)
			->setSubject(go()->t("Cancellation",'community', "calendar").': '.$this->name)
			->setSender(go()->getSettings()->systemEmail)
			->attach($ical->makeAttachment())
			->send();
	}


	protected static function aclEntityClass(): string
	{
		return Calendar::class;
	}

	protected static function aclEntityKeys(): array
	{
		return ['calendarId' => 'id'];
	}

	protected function getSearchDescription(): string
	{
		$calendar = Calendar::findById($this->calendarId, ['name'], true);

		return $calendar->name .': '. $this->title . ' - '. $this->start->format('Y-m-d');
	}
}