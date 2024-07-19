<?php
/*
 * @copyright (c) 2016, Intermesh BV http://www.intermesh.nl
 * @author Michael de Hart <mdhart@intermesh.nl>
 * @license http://www.gnu.org/licenses/agpl-3.0.html AGPLv3
 */

namespace go\modules\community\calendar\model;

use Exception;
use go\core\acl\model\AclItemEntity;
use go\core\db\Criteria;
use go\core\exception\Forbidden;
use go\core\exception\JsonPointerException;
use go\core\fs\Blob;
use go\core\model\Alert as CoreAlert;
use go\core\model\User;
use go\core\orm\CustomFieldsTrait;
use go\core\orm\exception\SaveException;
use go\core\orm\Filters;
use go\core\orm\Mapping;
use go\core\orm\Query;
use go\core\orm\SearchableTrait;
use go\core\util\JSON;
use go\core\util\UUID;
use go\core\util\DateTime;

class CalendarEvent extends AclItemEntity {

	use CustomFieldsTrait;
	use SearchableTrait;

	const PROD = '-//Intermesh//Group Office {VERSION}//EN';

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

	const EventProperties = ['uid','isOrigin','replyTo', 'prodId', 'sequence','title','description','locale','location', 'showWithoutTime',
		'start', 'timeZone','duration','priority','privacy','status', 'recurrenceRule','createdAt','modifiedAt',
		'createdBy','modifiedBy', 'lastOccurrence', 'eventId'];

	const UserProperties = ['keywords', 'color', 'freeBusyStatus', 'useDefaultAlerts', 'alerts', 'veventBlobId'];

	// If any of this properties is in the recurrenceOverrides the Object most be ignored
	const IgnoredPropertiesInException = [
		'uid', 'links', 'method', 'privacy', 'prodId',
		'recurrenceId', 'recurrenceOverrides','recurrenceRule',
		'relatedTo','replyTo','sentBy','timeZones'];


	static $sendSchedulingMessages = false;
	static $fromClient = false;

	public $calendarId;
	protected $eventId;
	/**
	 * @var boolean true when this event is created by this calendar system
	 * false if the event is imported from an invitation and the organizer is in another system
	 */
	public $isOrigin;
	/**
	 * When isOrigin is false this is the organizers email
	 * @var string email
	 */
	public $replyTo;
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

	public $utcStart;
	public $utcEnd;

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
	/**
	 * @var RecurrenceOverride[]
	 */
	public $recurrenceOverrides = [];
	public $alerts = [];

	public $categoryIds = [];
	public $links = [];

	public $modifiedAt;
	public $createdAt;
	public $modifiedBy;
	public $createdBy;

	/** The end time of the last occurrence in the series. or end time if not recurring */
	protected $lastOccurrence;
	protected $ownerId; // find calendar owner to see if Private events needs to be altered

	public static function customFieldsTableName(): string
	{
		return 'calendar_event_custom_fields';
	}

	protected static function defineMapping(): Mapping {
		return parent::defineMapping()
			->addTable('calendar_calendar_event', 'cce', ['eventId' => 'eventId'], ['id', 'calendarId'])
			->addTable('calendar_event', "eventdata", ['eventId' => 'eventId'], self::EventProperties)
			//->addTable('calendar_calendar','cal',['cce.calendarId' => 'id'], ['ownerId']) // fetch calendar ownerId
			->addUserTable('calendar_event_user', 'eventuser', ['cce.eventId' => 'eventId'],self::UserProperties)
			//->addHasOne('recurrenceRule', RecurrenceRule::class, ['id' => 'eventId'])
			->addMap('participants', Participant::class, ['eventId' => 'eventId'])
			->addMap('recurrenceOverrides', RecurrenceOverride::class, ['eventId'=>'fk'])
			->addMap('alerts', Alert::class, ['eventId' => 'eventId'])
			->addMap('links', Link::class, ['eventId' => 'eventId'])
			->addScalar('categoryIds', 'calendar_event_category', ['eventId' => 'eventId']);
			//->addMap('locations', Location::class, ['id' => 'eventId']);
	}

	/**
	 * Find an event by UID
	 *
	 * @param string $uid
	 * @param string|null $userEmail
	 * @return Query<self>
	 * @throws Exception
	 */
	static function findByUID(string $uid, ?string $userEmail = null) : Query {
		$query =  self::find()
			->where(['eventdata.uid'=>$uid])
			->filter(['permissionLevel' => 25]); // rsvp

		if(isset($userEmail)) {
			$query->join('calendar_calendar', 'cal', 'cal.id = cce.calendarId', 'LEFT')
				->join('core_user', 'u', 'u.id = cal.ownerId')
				->where(['u.email' => $userEmail]);
		}

		return $query;

	}

	public function isPrivate(){
		return $this->privacy === self::Private;
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
			->add('inCategories', function(Criteria $criteria, $value, Query $query) {
				if(!empty($value)) {
					$query->join('calendar_event_category', 'cat', 'cat.eventId = cce.eventId')
						->andWhere('cat.categoryId', 'IN', $value)
						->groupBy(['cce.id']);
				}
			},[])
			->add('calendarid', function(Criteria $criteria, $value) {
				$criteria->andWhere('cce.calendarId', '=', $value);
			})
			->addDate('after', function(Criteria $crit, $comparator, $value) {
				$crit->where((new Criteria())->where('lastOccurrence', '>', $value)->orWhere('lastOccurrence', 'IS', null));
			})
			->addDate('before', function(Criteria $crit, $comparator, $value) {
				$crit->where('start', '<', $value);
			})->add('inbox', function(Criteria $crit, $value, Query $query) {
				// value must be true
				$query->join('calendar_calendar','cal', 'cal.id = cce.calendarId')
					->join('calendar_participant', 'p', 'p.eventId = eventdata.eventId AND p.id = cal.ownerId');
				$crit->where('p.id', '=', go()->getUserId())
					->andWhere('p.rolesMask & 1 = 0') // !isOwner
					->andWhere('p.participationStatus', '=', 'needs-action')
					->andWhere('eventdata.status', '=', 'confirmed')
					->andWhere('eventdata.start', '>=', new DateTime());
			});
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

	public function categoryNames() {
			return go()->getDbConnection()
				->select('name')
				->from('calendar_category')
				->where('id','IN', $this->categoryIds)
			->fetchMode(\PDO::FETCH_COLUMN, 0)->all();
	}
	public function categoryIdsByName($names) {
		$this->categoryIds = [];
		foreach($names as $name) {
			$id = go()->getDbConnection()->selectSingleValue('id')->from('calendar_category')
				->where('name', '=', $name)
				->andWhere((new Criteria())->where('ownerId','=', go()->getUserId())->orWhere('ownerId','IS', null))
				->andWhere((new Criteria())->where('calendarId', '=', $this->calendarId)->orWhere('calendarId', 'IS', null))->single();
			if(!empty($id)) {
				$this->categoryIds[] = $id;
			}
		}
	}

	/**
	 * @throws JsonPointerException
	 * @throws Exception
	 */
	public function copyPatched($patch, string $recurrenceId) {
		$patchArray = $patch->toArray();

		//if start is not patched then we must set the recurrence ID to set the right time
		if(!isset($patchArray['start'])) {
			$patchArray['start'] = new DateTime($recurrenceId);
		}

		$e = JSON::patch($this->copy(), $patchArray);

		unset($e->recurrenceRule, $e->recurrenceOverrides, $e->replyTo); // , $e->sentBy, $e->relatedTo,
		return $e;
		//return (new self())->setValues(array_merge($this->toArray(), $patch->toArray()));
	}

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

//	public function getTitle() {
//		return $this->title;
//	}
	/**
	 * Set the tag property when the title contains a certain word
	 * @todo function is not called when the attributes of events are set relational
	 * @param string $value Title of event
	 */
//	public function setTitle($value) {
//		$tags = require(dirname(__FILE__) . '/../tags/nl.php'); //<-- @todo: use users language
//		$this->tag = null;
//		foreach($tags as $tag => $possibleMatches) {
//			foreach($possibleMatches as $possibleMatch) {
//				if (stripos($value, $possibleMatch) !== false) {
//					$this->keywords[] = $tag;
//					break 2;
//				}
//			}
//		}
//		$this->title = $value;
//	}

	public function start($withoutTime = false) {
		return new \DateTime($this->start->format("Y-m-d". ($withoutTime?'':" H:i:s")), $this->timeZone());
	}

	public function end($withoutTime = false) {
		if(empty($this->start)) {
			$end = new DateTime();
		} else {
			$end = new DateTime($this->start->format("Y-m-d" . ($withoutTime ? '' : " H:i:s")), $this->timeZone());
		}
		if(!empty($this->duration))
			$end->add(new \DateInterval($this->duration));
		return $end;
	}

	/**
	 * Used in iMip mails for display
	 * Would be 2 datetimes, 1 date and 2 times if same day, or just 1 or 2 dates when full-day(s)
	 * @return string[] 2 lines of human readable text
	 */
	public function humanReadableDate()
	{
		$start = $this->start;
		$end = $this->end();
		$oneDay = $start->format('Ymd') === $end->format('Ymd');
		$line1 = go()->t($oneDay ? 'At' : 'From') . ' ' .
			go()->t($start->format('l')) .
			$start->format(' j ') . go()->t('full_months')[$start->format('n')] .
			$start->format(' Y');
		if (!$oneDay) {
			if (!$this->showWithoutTime)
				$line1 .= ', ' . $start->format('H:i');
			$line1 .= ' ' . go()->t('until');
		}
		if ($oneDay) {
			$line2 = $start->format('H:i') . ' - ' . $end->format('H:i');
		} else {
			$line2 = go()->t($end->format('l')) .
				$end->format(' j ') . go()->t('full_months')[$end->format('n')] .
				$end->format(' Y');
			if (!$this->showWithoutTime) {
				$line2 .= ', ' . $end->format('H:i');
			}
		}
		return [$line1, $line2];
	}

	private function incrementCalendarModSeq() {

		Calendar::updateHighestModSeq(self::find()->select('calendarId')->where(['uid'=>$this->uid]));
		if($this->isModified('calendarId')) {
			// Event is put in a different calendar so update both modseqs
			Calendar::updateHighestModSeq($this->getOldValue('calendarId'));
		}
	}

	protected function internalSave() : bool {

		if(empty($this->uid)) {
			$this->uid = UUID::v4();
		}

		if($this->isNew() || $this->isModified(['start','duration','recurrenceRule'])) {
			$this->updateLastOccurence();
		}

		$this->resetICalBlob();

		if(!$this->isNew()) {
			// is modified, but not calendarId, isDraft or modifiedAt, per-user prop, participants
			if($this->isModified(self::EventProperties) && $this->isOrigin) {
				if(!$this->isModified('sequence') || $this->sequence <= $this->getOldValue('sequence'))
					$this->sequence += 1;
			}
			if(self::$fromClient) {
				if (!$this->isOrigin && $this->isModified(self::EventProperties)) {
					// properties may only change by ITip. client is not allowed to change properties of invites
					throw new Forbidden('Not allowed to edit anything other than per-user properties when isOrigin is false');
				}

				$currPart = $this->calendarParticipant();
				if ($currPart && !$currPart->isOwner()) { // not owner
					if ($this->isModified(self::EventProperties)) {
						throw new Forbidden('Trying to change properties but not the organizer');
					} else if ($this->isModified(['participants'])) {
						// we may only set our own participationStatus
						// todo: loop participants, comparare modified to $currPart, check is participationStatus is the only modified property
					}
				}
			}

			if ($this->isModified('status') && $this->status == self::Cancelled) {
				// Remove alert when event is cancelled
				CoreAlert::deleteByEntity($this);
			}
		}

		if(!empty($this->participants) && empty($this->replyTo)) {
			$owner = $this->organizer();
			if(!empty($owner)) {
				$this->replyTo = $owner->email;
			}
		}

		if(empty($this->prodId)) {
			$this->prodId = str_replace('{VERSION}', go()->getVersion(),self::PROD);
		}


		if(self::$sendSchedulingMessages) {
			Scheduler::handle($this);
		}

		$success = parent::internalSave();

		if($success) {
			$this->addToResourceCalendars();
			$this->updateAlerts();
			$this->changeEventsWithSameUID();
			$this->incrementCalendarModSeq();
		}
		return $success;
	}

	private function addToResourceCalendars() {
		if(empty($this->participants)) return;
		foreach($this->participants as $pid => $participant) {
			if($participant->kind == 'resource') {
				$resourceCalendar = Calendar::findById(str_replace('Calendar:', '', $pid));
				if(!empty($resourceCalendar)) {
					Calendar::addEvent($this,$resourceCalendar->id);
				}
			}
		}
	}
	private function updateAlerts() {
		if(!CoreAlert::$enabled) {
			return;
		}

		$modified = $this->isModified(['alerts']);
		if(!empty($modified)) {
			if (isset($modified[1])) {
				foreach ($modified[1] as $model) {
					if (!isset($modified[0]) || !in_array($model, $modified[0])) {
						$this->deleteAlert($model->id);
					}
				}
			}

			if (isset($this->alerts)) {
				foreach ($this->alerts as $alert) {
					$coreAlert = $this->createAlert($alert->at(), $alert->id);
					if (!$coreAlert->save()) {
						throw new Exception(var_export($coreAlert->getValidationErrors(), true));
					}
				}
			}
		}

		$calendar = Calendar::findById($this->calendarId, ['id', 'ownerId']);
		// create an alert if someone else created a task in your default list
		if($this->isNew()) {
			$alert = $this->createAlert(new \DateTime(), 'createdforyou', $calendar->ownerId)
				->setData(['type' => 'assigned', 'creator' => $this->createdBy]);

			if (!$alert->save()) {
				throw new SaveException($alert);
			}


		} else if($this->modifiedBy != $calendar->ownerId){
			$this->deleteAlert('createdforyou', $this->modifiedBy);
		}
	}

	/**
	 * Update the modseq for every calendar this event is in.
	 */
	private function changeEventsWithSameUID() {
//		$changes = CalendarEvent::find()
//			->select("cce.id, cal.aclId, '0'")
//			->fetchMode(\PDO::FETCH_ASSOC)
//			->join("calendar_calendar", "cal", "cal.id = cce.calendarId")
//			->where('cce.eventId', '=', $this->eventId)
//			->all();
//		if(!empty($changes))
//			CalendarEvent::entityType()->changes($changes);

		$changes = CalendarEvent::find()
			->where('cce.eventId', '=', $this->eventId);

		foreach($changes as $change) {
			if($change->getPermissionLevel()) {
				\go\modules\community\calendar\controller\CalendarEvent::onEntitySave($change);
			}
			CalendarEvent::entityType()->change($change);
		}
	}

	private $calendarParticipant = null;

	/**
	 * The participant in the event that owns the calendar this event is in
	 * @return false|Participant
	 */
	public function calendarParticipant() {
		if($this->calendarParticipant === null && !empty($this->participants)) {
			$scheduleId = Calendar::find()
				->join('core_user', 'u', 'calendar_calendar.ownerId = u.id')
				->where(['id' => $this->calendarId])
				->selectSingleValue('u.email')->single();
			$this->calendarParticipant = $this->participantByScheduleId($scheduleId);
//			foreach ($this->participants as $p) {
//				if ($scheduleId == $p->email) {
//					$this->calendarParticipant = $p;
//					break;
//				}
//			}
//			if (!isset($this->calendarParticipant)) {
//				$this->calendarParticipant = false;
//			}
		}
		return $this->calendarParticipant;
	}

	/**
	 * @param $email string the scheduleId
	 * @return false|Participant
	 */
	public function participantByScheduleId($email) {
		foreach($this->participants as $participant) {
			if($participant->email === $email) { // todo Use scheduleId when ParticipantIdentity is implemented
				return $participant;
			}
		}
		return false;
	}

	public function generatedOrganizer($principal = null) {
		$principal = $principal ?? \go\core\model\Principal::currentUser();
		$p = (new Participant($this))->setValues([
			'email' => $principal->email,
			'name' => $principal->name,
			'participationStatus' => Participant::Accepted,
			'expectReply' => false,
			'roles' => ['attendee'=>true, 'owner'=>true],
			'kind' => Participant::Individual
		]);
		$this->participants[$principal->id] = $p;
		return $p;
	}

	public function organizer() : ?Participant {
		foreach($this->participants as $participant) {
			if($participant->isOwner()) {
				return $participant;
			}
		}
		return null;
	}

	private function resetICalBlob() {
		// TODO: if !this.isOrigin we need to keep and update the vevent
		if(!$this->isNew() && ($this->isModified(self::EventProperties) || $this->isModified(self::UserProperties))) {
			if(!$this->isModified('veventBlobId')) {
				$this->veventBlobId = null;
			}
		}
	}

	protected static function internalDelete(Query $query): bool
	{
		if(self::$sendSchedulingMessages) {
			$events = CalendarEvent::find()->mergeWith(clone $query);
			foreach ($events as $event) {
				Scheduler::handle($event, true);
			}
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
		$calendarModSeq = clone $query;
		$calIds = $calendarModSeq->selectSingleValue('calendarId')->distinct()->all();
		// Garbage collector will delete event when last user instance is removed
		$success =  parent::internalDelete($query); // delete none recurring or complete series
		if($success) {
			Calendar::updateHighestModSeq($calIds);
		}
		return $success;
	}

	public function isInPast() {
		if($this->lastOccurrence === null)
			return false;
		return $this->lastOccurrence <= new DateTime();
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
				$this->lastOccurrence = (new DateTime($r->until,$this->timeZone()))
					->add(new \DateInterval($this->duration));
			} else if(isset($r->count)) {
				$it = ICalendarHelper::makeRecurrenceIterator($this);
				$maxDate = new \DateTime('2058-01-01');
				while ($it->valid() && $this->lastOccurrence < $maxDate) {
					$this->lastOccurrence = $it->current(); // will clone :(
					$it->next();
				}
				$this->lastOccurrence->add(new \DateInterval($this->duration));
			}
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