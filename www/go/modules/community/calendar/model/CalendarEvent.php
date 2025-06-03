<?php
/*
 * @copyright (c) 2016, Intermesh BV http://www.intermesh.nl
 * @author Michael de Hart <mdhart@intermesh.nl>
 * @license http://www.gnu.org/licenses/agpl-3.0.html AGPLv3
 */

namespace go\modules\community\calendar\model;

use DateTimeZone;
use Exception;
use go\core\acl\model\AclItemEntity;
use go\core\db\Criteria;
use go\core\ErrorHandler;
use go\core\exception\Forbidden;
use go\core\exception\JsonPointerException;
use go\core\fs\Blob;
use go\core\model\Acl;
use go\core\model\Alert as CoreAlert;
use go\core\model\User;
use go\core\orm\CustomFieldsTrait;
use go\core\orm\exception\SaveException;
use go\core\orm\Filters;
use go\core\orm\Mapping;
use go\core\orm\Query;
use go\core\orm\Relation;
use go\core\orm\SearchableTrait;
use go\core\util\JSON;
use go\core\util\UUID;
use go\core\util\DateTime;

class CalendarEvent extends AclItemEntity {

	use CustomFieldsTrait {
		customFieldsModelId as traitCustomFieldModelId;
	}

	use SearchableTrait;

	const PROD = '-//Intermesh//Group Office {VERSION}//EN';

	const MAX_RECUR = '2058-01-01';

	/* Status */
	const Confirmed = 'confirmed'; // default
	const Cancelled = 'cancelled';
	const Tentative = 'tentative';

	/* Privacy */
	const Public = 'public';
	const Private = 'private';
	const Secret = 'secret';
	// Properties shown to others when 'privacy' is set to private
//	const PrivateProperties = ['created', 'due', 'duration', 'estimatedDuration', 'freeBusyStatus', 'privacy',
//		'recurrenceOverrides', 'sequence', 'showWithoutTime', 'start', 'timeZone', 'timeZones', 'uid','updated'];

const OwnerOnlyProperties = ['uid','isOrigin','replyTo', 'prodId', 'title','description','locale','location', 'showWithoutTime',
'start', 'timeZone','duration','priority','privacy','status', 'recurrenceRule'

];

	const EventProperties = ['uid','isOrigin','replyTo', 'prodId', 'sequence','title','description','locale','location', 'showWithoutTime',
		'start', 'timeZone','duration','priority','privacy','status', 'recurrenceRule','createdAt','modifiedAt',
		'createdBy','modifiedBy', 'lastOccurrence','firstOccurrence','etag','uri', 'eventId', 'recurrenceId'];

	const UserProperties = ['keywords', 'color', 'freeBusyStatus', 'useDefaultAlerts', 'alerts', 'veventBlobId'];

//	// If any of this properties is in the recurrenceOverrides the Object most be ignored
//	const IgnoredPropertiesInException = [
//		'uid', 'links', 'method', 'privacy', 'prodId',
//		'recurrenceId', 'recurrenceOverrides','recurrenceRule',
//		'relatedTo','replyTo','sentBy','timeZones'];


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
	 * This is only set when somebody is invited to a single occurrence of a series.
	 *
	 * Als een participant is uitgenodigd dan wordt er een aparte event met UID . ‘_’. RECURRENCE-ID gemaakt.
	 *
	 * @var string|null
	 */
	public ?string $recurrenceId = null;

	public ?bool $excluded = false;

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

	/**
	 * @var string with CalDAV client the `etag` received from the server needs to be saved.
	 */
	protected $etag;
	/**
	 * @var string with CalDAV server the `uri` from the client needs to be persistent
	 */
	protected $uri;


	/**
	 * @var Participant[]
	 */
	public $participants = [];
	/**
	 * @var RecurrenceOverride[]
	 */
	public $recurrenceOverrides = [];
	/**
	 * @var Alert[]
	 */
	public $alerts = [];

	public $categoryIds = [];
	public $links = [];

	public $modifiedAt;
	public $createdAt;
	public $modifiedBy;
	public $createdBy;

	/** The end time of the last occurrence in the series. or end time if not recurring */
	protected $lastOccurrence;
	/** The start time of the first occurence in the series, or start time if not recurring */
	protected $firstOccurrence;
	protected $ownerId; // find calendar owner to see if Private events needs to be altered

	public static function customFieldsTableName(): string
	{
		return 'calendar_event_custom_fields';
	}

	public function customFieldsModelId() : string {
		return (string) $this->eventId;
	}

	protected static function defineMapping(): Mapping {
		return parent::defineMapping()
			->addTable('calendar_calendar_event', 'cce', ['eventId' => 'eventId'], ['id', 'calendarId'])
			->addQuery((new Query())->select("ownerId")->join('calendar_calendar', 'cal', 'cal.id=cce.calendarId'))
			->addTable('calendar_event', "eventdata", ['cce.eventId' => 'eventId'], self::EventProperties)
			->addUserTable('calendar_event_user', 'eventuser', ['cce.eventId' => 'eventId'],self::UserProperties)
			->add('participants',Relation::map(Participant::class)->keys(['eventId' => 'eventId']))
			->add('recurrenceOverrides',Relation::map(RecurrenceOverride::class)->keys(['eventId' => 'fk']))
			->add('alerts',Relation::map(Alert::class)->keys(['eventId' => 'fk']))
			->add('links',Relation::map(Link::class)->keys(['eventId' => 'eventId']))
			->add('categoryIds',Relation::scalar('calendar_event_category')->keys(['eventId' => 'eventId']));
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
			$query->join('core_user', 'u', 'u.id = cal.ownerId')
				->where(['u.email' => $userEmail]);
		}

		return $query;

	}

	public function isPrivate(){
		return $this->privacy !== self::Public;
	}

	protected static function defineFilters(): Filters
	{
		return parent::defineFilters()->add('inCalendars', function(Criteria $criteria, $value, Query $query) {
				if($value === 'subscribedOnly') {
					$query->join('calendar_calendar_user', 'ucal', 'ucal.id = cce.calendarId AND ucal.userId = '.go()->getAuthState()->getUserId())
						->where('ucal.isSubscribed','=', true);
				} else if(!empty($value)) {
					$query->andWhere('cce.calendarId', '=', $value);
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
				$crit->where('firstOccurrence', '<', $value);
			})->add('inbox', function(Criteria $crit, $value, Query $query) {
				// value must be true
				$query->join('calendar_participant', 'p', 'p.eventId = eventdata.eventId AND p.id = cal.ownerId');
				$crit->where('p.id', '=', go()->getUserId())
					->andWhere('p.rolesMask & 1 = 0') // !isOwner
					->andWhere('p.participationStatus', '=', 'needs-action')
					->andWhere('eventdata.status', '=', 'confirmed')
					->andWhere('eventdata.start', '>=', new DateTime());
			})->add('hideSecret', function(Criteria $criteria, $value, $query) {
				$query->andWhere((new Criteria())
					->where('privacy', '!=', 'secret')
					->orWhere('cal.ownerId','=',go()->getUserId())
				);
			},1);
	}


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

	public function attachBlob($blobId) {
		$this->veventBlobId = $blobId;
	}

	public function icsBlob() {

		$blob = isset($this->veventBlobId) ? Blob::findById($this->veventBlobId) : null;
		if(!$blob || $blob->modifiedAt < $this->modifiedAt || !$blob->getFile()->exists()) {
			$blob = ICalendarHelper::makeBlob($this);
			$this->veventBlobId = $blob->id;
			if(!$this->isNew()) {
//				$this->save();
				$this->saveTables();
			}
		}
		return $blob;
	}

	public function toVObject() {
		return ICalendarHelper::toVObject($this)->serialize();
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

		Calendar::updateHighestModSeq(self::find()->select('calendarId')->removeJoin('calendar_calendar')->where(['uid'=>$this->uid]));
		if($this->isModified('calendarId')) {
			// Event is put in a different calendar so update both modseqs
			$oldCalId = $this->getOldValue('calendarId');
			if(isset($oldCalId)) {
				Calendar::updateHighestModSeq($oldCalId);
			}
		}
	}

	public function uri($uri = null)
	{
		if ($uri !== null) {
			$this->uri = $uri;
		}

		return $this->uri;
	}

	public function etag($v = null) {
		if($v === null)
			return $this->etag;
		$this->etag = $v;
	}

	protected function init()
	{
		if($this->isNew()) {
			$this->uid = UUID::v4();
		}
	}


	/**
	 * Will create Psuedo CalendarEvent objects from a recurring event.
	 * All recurrence exceptions will be patched copies with the recurrenceId set
	 * @return \Generator | [recurrenceId:string]:CalendarEvent
	 */
	public function overrides($modifiedOnly = false) {
		if($this->isInstance()) {
			return; // yield nothing
		}

		if(isset($this->recurrenceOverrides)) {
			foreach ($this->recurrenceOverrides as $recurrenceId => $override) {
				if (!$modifiedOnly || $override->isModified()) {
					if($override->excluded) {
						yield $recurrenceId => $override;
					} else
						yield $recurrenceId => $this->patchedInstance($recurrenceId);
				}
			}
		}
	}

	/**
	 * @throws JsonPointerException
	 * @throws Exception
	 */
	public function patchedInstance(string $recurrenceId) : CalendarEvent {
		$patchArray = $this->recurrenceOverrides[$recurrenceId]->toArray();

		//if start is not patched then we must set the recurrence ID to set the right time
		if(!isset($patchArray['start'])) {
			$patchArray['start'] = new DateTime($recurrenceId);
		}

		$e = JSON::patch($this->copy(), $patchArray);
		$e->recurrenceId = $recurrenceId;
		unset($e->recurrenceRule, $e->recurrenceOverrides); // , $e->sentBy, $e->relatedTo,
		return $e;
		//return (new self())->setValues(array_merge($this->toArray(), $patch->toArray()));
	}

	public function isInstance() {
		return !empty($this->recurrenceId);
	}

	/**
	 * @param $blobIds
	 * @param $calendarId
	 * @param $uid string 'check', 'ignore', 'new'
	 * @return object
	 */
	static function import($blobIds, $calendarId, $uid='check') {
		$r = (object)[
			'saved'=>0,
			'failed'=>0,
			'skipped'=>0,
			'failureReasons'=>[]
		];
		foreach($blobIds as $blobId) {
			foreach(ICalendarHelper::calendarEventFromFile($blobId) as $ev) {
				if(is_array($ev)){
					$r->failureReasons[$r->failed] = 'Parse error '.$ev['vevent']->VEVENT[0]->UID. ': '. $ev['error']->getMessage();
					$r->failed++;
					continue;
				}
				$ev->calendarId = $calendarId;
				if($uid === 'new') {
					$ev->uid = UUID::v4();
				} else if($uid === 'check' && self::find()->selectSingleValue('cce.id')->where(['uid'=>$ev->uid])->single() !== null) {
					$r->skipped++;
					continue;
					// check if exists
				} // else use UID from ics file without checking.
				if($ev->save()){ // will fail if UID exists. We dont want to modify existing events like this
					$r->saved++;
				} else {
					$r->failureReasons[$r->failed] = 'Validate error '.$ev->uid. ': '. var_export($ev->getValidationErrors(),true);
					$r->failed++;
				}
			}
		}
		return $r;
	}
	protected function internalSave() : bool {

		if(empty($this->uri)) {
			$this->uri = strtr($this->uid, '+/=', '-_.') . '.ics';
		}

		if($this->isNew() || $this->isModified(['start','duration','recurrenceRule','recurrenceOverrides'])) {
			$this->updateOccurrenceSpan();
		}

		if(!$this->isNew() && $this->isRecurring() && $this->isModified('start')) {
			$this->reindexRecurrenceOverrides();
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
					if ($this->isModified(self::OwnerOnlyProperties)) {
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

		if(empty($this->prodId) || $this->prodId === 'Unknown') {
			$this->prodId = str_replace('{VERSION}', go()->getVersion(),self::PROD);
		}


		if(self::$sendSchedulingMessages) {
			Scheduler::handle($this);
		}

		$success = parent::internalSave();

		if($success) {
			$this->addToResourceCalendars();
			$this->updateAlerts(go()->getUserId());
			$this->changeEventsWithSameUID();
			$this->incrementCalendarModSeq();
		}
		return $success;
	}

	private function currentUserIsOwner() {
		return $this->ownerId === go()->getUserId() ||
		 ($this->ownerId === null && $this->createdBy === go()->getUserId());
	}

	public function toArray(array|null $properties = null): array|null
	{
		$arr =  parent::toArray($properties);
		$showAsPrivate = $this->isPrivate() && $this->getPermissionLevel() <= 30/*Write own*/;
		if($this->getPermissionLevel() === 30 && $this->currentUserIsOwner()) {
			$showAsPrivate = false;
		}
		if($showAsPrivate) {
			$arr['title'] = '';
			$arr['description'] = '';
			$arr['location'] = '';
		}
		unset($arr['recurrenceId'], $arr['excluded']);
		return $arr;
	}

	private function addToResourceCalendars() {
		if(empty($this->participants)) return;
		foreach($this->participants as $pid => $participant) {
			if($participant->kind == 'resource') {
				$resourceCalendar = Calendar::findById(str_replace('Calendar:', '', $pid));
				if(!empty($resourceCalendar)) {
					 go()->getDbConnection()->insertIgnore('calendar_calendar_event', [
						['calendarId'=>$resourceCalendar->id, 'eventId'=>$this->eventId]
					])->execute();
				}
			}
		}
	}
	private function updateAlerts($userId) {
		if(!CoreAlert::$enabled) {
			return;
		}

		if($this->isNew() ||
			$this->isModified(['useDefaultAlerts', 'alerts', 'start','duration','recurrenceOverrides']) ||
			($this->useDefaultAlerts && $this->isModified('calendarId'))
		) {
			CoreAlert::deleteByEntity($this, '1', $userId); // this will reschedule if recurring and existing
			foreach ($this->alerts() as $alert) {
				$alert->schedule($this);
			}
		}

	}

	/**
	 * @return Alert[]
	 */
	public function alerts() {
		if($this->useDefaultAlerts) {
			$calendar = Calendar::findById($this->calendarId, ['id', 'ownerId', $this->showWithoutTime?'defaultAlertsWithoutTime':'defaultAlertsWithTime']);
			return ($this->showWithoutTime ? $calendar->defaultAlertsWithoutTime : $calendar->defaultAlertsWithTime) ?? [];
		} else {
			return $this->alerts ?? [];
		}
	}

	/**
	 * @param \go\core\model\Alert[] $alerts
	 * @return void
	 */
	public static function dismissAlerts(array $coreAlerts) {
		foreach($coreAlerts as $coreAlert) {
			// create the next alert when dismissing a recurring alert. (if any)
			if(!empty($coreAlert->recurrenceId)) {
				//$alertId = $alert->tag;
				$event = self::findById($coreAlert->entityId);
				// we wont save $event but if the event isn't modified updateAlerts() wont work
				//$event->recurrenceOverrides[$calert->recurrenceId]['alerts/'.$calert->tag.'/acknowledged'] = true;
				if($event) {
					foreach ($event->alerts() as $newAlert) {
						$newAlert->schedule($event);
					}
				}
			}
		}
	}

	/**
	 * Update the modseq for every calendar this event is in.
	 */
	private function changeEventsWithSameUID() {

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
		if(empty($this->participants))
			return false;
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
		if($success && !empty($calIds)) {
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

	public function timeZone(): ?DateTimeZone
	{
		try {
			return $this->timeZone ? new DateTimeZone($this->timeZone) : null;
		}catch(Exception $e) {
			ErrorHandler::logException($e, "Failed to set timezone " . $this->timeZone. " for event" . $this->id);

			static $currentTZUser;
			if (empty($currentTZUser)) {
				$currentTZUser = go()->getAuthState()->getUser(['dateFormat', 'timezone', 'timeFormat']);
				if (empty($currentTZUser)) {
					$currentTZUser = User::findById(1, ['dateFormat', 'timezone', 'timeFormat'], true);
				}
			}
			try {
				return new DateTimeZone($currentTZUser->timezone);
			}catch(Exception $e) {
				ErrorHandler::logException($e, "Failed to fallback on user timezone " . $currentTZUser->timezone. " for event" . $this->id);
			}
			return null;

		}
	}

	/**
	 * @return DateTime[]
	 * @throws \DateMalformedStringException
	 */
	public function upcomingOccurrence() {
		$now = new DateTime('now', $this->timeZone());
		$recurrenceId = null;
		$nextOccurrence = null;
		if(!empty($this->recurrenceOverrides)) {
			foreach ($this->recurrenceOverrides as $recurrenceId => $o) {
				if (!empty($o->excluded)) continue;
				$start = $o->start($recurrenceId);
				if ($start >= $now) {
					$nextOccurrence = empty($nextOccurrence) ? $start : min($nextOccurrence, $start);
					$recurrenceId = $o->recurrenceId;
				}
			}
		}
		if(is_string($recurrenceId)) {
			$recurrenceId = new DateTime($recurrenceId, $this->timeZone());
		}
		if(is_string($nextOccurrence)) {
			$nextOccurrence = new DateTime($nextOccurrence, $this->timeZone());
		}
		$it = ICalendarHelper::makeRecurrenceIterator($this);
		$nextRecurrenceId = null;
		$rId = null;
		while ($it->valid()) {
			$rId = $it->current(); // will clone :(
			if($rId > $now && !isset($this->recurrenceOverrides[$rId->format("Y-m-d\TH:i:s")])) {
				$nextRecurrenceId = $rId;
				break;
			}
			$it->next();
		}
		if($nextRecurrenceId === null)
			return [$recurrenceId, $nextOccurrence];
		if($nextOccurrence === null)
			return [$nextRecurrenceId, $nextRecurrenceId];
		if($nextOccurrence < $nextRecurrenceId) {
			return [$recurrenceId, $nextOccurrence];
		} else {
			return [$nextRecurrenceId, $nextRecurrenceId];
		}
	}

	/**
	 * Finds the start if the first occurrence and end of the last occurrence.
	 * This wil just be start and end time for non-recurring events.
	 * It will take overrides into account the may be before the start or after
	 * the end of the series.
	 * @return void
	 * @throws Exception
	 */
	private function updateOccurrenceSpan(): void
	{
		if($this->isNew() || $this->isModified('start')) {
			$this->firstOccurrence = $this->start();
		}
		if(!$this->isRecurring()) {
			if($this->isNew() || $this->isModified(['start', 'duration'])) {
				$this->lastOccurrence = $this->end();
			}
			return;
		}

		$r = $this->getRecurrenceRule();
		if(isset($r->until)) {
			$until = (new DateTime($r->until,$this->timeZone()))->add(new \DateInterval($this->duration));
			if($this->lastOccurrence === null || $until > $this->lastOccurrence) {
				$this->lastOccurrence = $until;
			}
		} else if(isset($r->count)) {
			$it = ICalendarHelper::makeRecurrenceIterator($this);
			$maxDate = new \DateTime(self::MAX_RECUR);
			while ($it->valid()) {
				$dt = $it->current(); // will clone :(
				if($dt > $maxDate) break;
				if($dt > $this->lastOccurrence) $this->lastOccurrence = $dt;
				$it->next();
			}
			$this->lastOccurrence->add(new \DateInterval($this->duration));
		} else {
			$this->lastOccurrence = null;
		}

		if($this->isModified('recurrenceOverrides')) {
			foreach ($this->recurrenceOverrides as $recurrenceId => $override) {
				$this->firstOccurrence = min($this->firstOccurrence, $override->start($recurrenceId));
				if($this->lastOccurrence !== null)
					$this->lastOccurrence = max($this->lastOccurrence, $override->end($recurrenceId));
			}
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

	/**
	 * When the series start time changes all the recurrence id's must be recreated.
	 *
	 * @return void
	 * @throws \DateMalformedStringException
	 */
	private function reindexRecurrenceOverrides(): void
	{
		$old = $this->getOldValue('start');
		$oldHour = $old->format("H");
		$oldMin = $old->format("i");
		$newHour = $this->start->format("H");
		$newMin = $this->start->format("i");

		if($oldHour == $newHour && $newMin == $oldMin) {
			return;
		}

		foreach($this->recurrenceOverrides as $recurrenceId => $o) {
			$date = new DateTime($recurrenceId);
			$date->setTime($newHour, $newMin);
			$o->changeRecurrenceId($date);
		}

	}
}