<?php
/*
 * @copyright (c) 2016, Intermesh BV http://www.intermesh.nl
 * @author Michael de Hart <mdhart@intermesh.nl>
 * @license http://www.gnu.org/licenses/agpl-3.0.html AGPLv3
 */

namespace go\modules\community\calendar\model;

use go\core\acl\model\AclItemEntity;
use go\core\db\Criteria;
use go\core\fs\Blob;
use go\core\model\User;
use go\core\orm\CustomFieldsTrait;
use go\core\orm\Filters;
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

	const EventProperties = ['uid','isOrigin', 'prodId', 'sequence','title','description','locale', 'showWithoutTime',
		'start', 'timeZone','duration','priority','privacy','status', 'recurrenceRule','createdAt','modifiedAt',
		'createdBy','modifiedBy', 'lastOccurrence'];

	const UserProperties = ['keywords', 'color', 'freeBusyStatus', 'useDefaultAlerts', 'alerts', 'veventBlobId'];

	// If any of this properties is in the recurrenceOverrides the Object most be ignored
	const IgnoredPropertiesInException = [
		'uid', 'links', 'method', 'privacy', 'prodId',
		'recurrenceId', 'recurrenceOverrides','recurrenceRule',
		'relatedTo','replyTo','sentBy','timeZones'];


	static $sendSchedulingMessages = false;

	public $calendarId;
	public $eventId;
	/**
	 * @var boolean true when this event is created by this calendar system
	 * false if the event is imported from an invitation and the organizer is in another system
	 */
	public $isOrigin;

	/**
	 * @var string status is set after a scheduling action like sending a REPLY iTip to the organizer
	 */
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
	protected $end; // cached to query later

	/**
	 * The duration of the event (or the occurence)
	 * (optional, default: PT0S)
	 * @var string
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

	/** The end time of the last occurrence in the series. or end time if not recurring */
	protected $lastOccurrence;

	public static function customFieldsTableName(): string
	{
		return 'calendar_event_custom_fields';
	}

	protected static function defineMapping(): Mapping {
		return parent::defineMapping()
			->addTable('calendar_calendar_event', 'cce', ['id' => 'eventId'], ['id', 'calendarId'])
			->addTable('calendar_event', "eventdata", ['eventId' => 'id'], self::EventProperties)
			->addUserTable('calendar_event_user', 'eventuser', ['id' => 'id'],self::UserProperties, [], true)
			//->addHasOne('recurrenceRule', RecurrenceRule::class, ['id' => 'eventId'])
			->addMap('participants', Participant::class, ['id' => 'eventId'])
			->addMap('recurrenceOverrides', RecurrenceOverride::class, ['id'=>'fk'])
			->addMap('alerts', Alert::class, ['id' => 'eventId']);
			//->addMap('locations', Location::class, ['id' => 'eventId']);
	}
	protected static function defineFilters(): Filters
	{
		return parent::defineFilters()->add('inCalendars', function(Criteria $criteria, $value, Query $query) {
				if($value === 'subscribedOnly') {
					$query->join('calendar_calendar_user', 'ucal', 'ucal.id = cce.calendarId AND ucal.userId = '.go()->getAuthState()->getUserId())
						->where('ucal.isSubscribed','=', true);
				} else if(!empty($value)) {
					$query->andWhere('cce.calendarId', 'IN', $value);
				}
			}, 'subscribedOnly')
			->addDate('after', function(Criteria $crit, $comparator, $value) {
				$crit->where((new Criteria())->where('lastOccurrence', '>', $value)->orWhere('lastOccurrence', 'IS', null));
			})
			->addDate('before', function(Criteria $crit, $comparator, $value) {
				$crit->where('start', '<', $value);
			});
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
				$this->veventBlobId = $blob->id;
				$this->save();
				// save the blobId without updating the state (this property is not part of the spec
//				$stmt = go()->getDbConnection()->update('calendar_event_user',
//					['veventBlobId'=>$blob->id, 'modifiedAt'=>$blob->modifiedAt],
//					(new Query)
//						->join('calendar_calendar_event', 'cce', 'cce.eventId = t.id')
//						->where(['userId' => go()->getUserId(), 'cce.id' => $this->id]));
//				$stmt->execute();
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
		return new \DateTime($this->start->format("Y-m-d". ($withoutTime?'':" H:i:s")), $this->timeZone());
	}

	public function end($withoutTime = false) {

		$end = new DateTime($this->start->format("Y-m-d". ($withoutTime?'':" H:i:s")),$this->timeZone()) ;
		$end->add(new \DateInterval($this->duration));
		return $end;
	}

	public function humanReadableDate() {
		$start = $this->start;
		$end = $this->end();
		$oneDay = $start->format('Ymd') === $end->format('Ymd');
		$line1 = go()->t($oneDay ? 'At' : 'From') .' '.
			go()->t($start->format('l')).
			$start->format(' j ') . go()->t('full_months')[$start->format('m')].
			$start->format(' Y');
		if(!$oneDay) {
			if(!$this->showWithoutTime)
				$line1.= ', '.$start->format('H:i');
			$line1 .= ' ' . go()->t('until');
		}
		if($oneDay) {
			$line2 = $start->format('H:i').' - '.$end->format('H:i');
		} else {
			$line2 = go()->t($end->format('l')).
				$end->format(' j ') . go()->t('full_months')[$end->format('m')].
				$end->format(' Y');
			if(!$this->showWithoutTime) {
				$line2.= ', '.$end->format('H:i');
			}
		}
		return [$line1,$line2];
	}

	protected function internalSave() : bool {

		if(empty($this->uid)) {
			$this->uid = UUID::v4();
		}

		if($this->isNew() || $this->isModified(['start','duration','recurrenceRule'])) {
			$this->updateLastOccurence();
		}

		$this->resetICalBlob();

		// is modiified, but not calendarId, isDraft or modifiedAt, per-user prop, participants
		if($this->isModified(self::EventProperties) && $this->isOrigin) {
			if(!$this->isModified('sequence') || $this->sequence <= $this->getOldValue('sequence'))
				$this->sequence += 1;
		}

		if(self::$sendSchedulingMessages && !empty($this->participants) &&
			$this->lastOccurrence > new DateTime()) {
			ICalendarHelper::handleScheduling($this);
		}

		$success = parent::internalSave();

		if($success) {
			Calendar::updateHighestModSeq($this->calendarId);
			if($this->isModified('calendarId')) {
				// Event is put in a different calendar so update both modseqs
				Calendar::updateHighestModSeq($this->getOldValue('calendarId'));
			}
		}
		return $success;
	}

	/**
	 * Only send updates it deemed “essential” to avoid flooding the recipient’s email with changes they do not care about.
	 * @return boolean
	 */
//	private function changeWasEssential() {
//		if(
//			($this->lastOccurrence < new DateTime()) ||
//			(!$this->isOrigin && (!$this->calendarParticipant() || !$this->calendarParticipant->isModified('participantionStatus')))
//		) {
//			return false; // dont send schedule messages
//		}
//		return true;
//	}

	private $calendarParticipant = null;

	/**
	 * The participant in the event that owns the calendar this event is in
	 * @return false|Participant
	 */
	public function calendarParticipant() {
		if($this->calendarParticipant === null) {
			$scheduleId = Calendar::find(['id' => $this->calendarId])
				->join('core_user', 'u', 'calendar_calendar.ownerId = u.id')
				->selectSingleValue('u.email')->single();
			foreach ($this->participants as $p) {
				if ($scheduleId == $p->email) { // todo Use scheduleId when ParticipantIdentity is implemented
					$found = true;
					$this->calendarParticipant = $p;
					break;
				}
			}
			if (!isset($found)) {
				$this->calendarParticipant = false;
			}
		}
		return $this->calendarParticipant;
	}

	private function resetICalBlob() {
		// TODO: if !this.isOrigin we need to keep and update the vevent
		if(!$this->isNew() && ($this->isModified(self::EventProperties) || $this->isModified(self::UserProperties))) {
			if(!$this->isModified('veventBlobId')) {
				$this->veventBlobId = null;
			}
		}
	}

	protected static function internalDelete(Query $q): bool
	{
		$events = CalendarEvent::find()->mergeWith(clone $q);
		foreach($events as $event) {
			ICalendarHelper::handleScheduling($event, true);
		}
		//$q->andWhere(['isOrigin' => 1]);

		//$ids = $q->all();
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

	public function isRecurring() {
		return !empty($this->recurrenceRule); // && !empty($this->recurrenceRule->frequency));
	}

	public function timeZone() {
		return $this->timeZone ? new \DateTimeZone($this->timeZone) : null;
	}

	private function updateLastOccurence() {
		if($this->isRecurring()) {
			$this->lastOccurrence = null;
			$r = $this->getRecurrenceRule();
			if(isset($r->until)) {
				$this->lastOccurrence = new DateTime($r->until,$this->timeZone());
			} else if(isset($r->count)) {
				$it = ICalendarHelper::makeRecurrenceIterator($this);
				$maxDate = new \DateTime('2038-01-01');
				while ($it->valid() && $this->lastOccurrence < $maxDate) {
					$this->lastOccurrence = $it->current(); // will clone :(
					$it->next();
				}
			}
			$this->lastOccurrence->add(new \DateInterval($this->duration));
		} else {
			$this->lastOccurrence = $this->end();
		}
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