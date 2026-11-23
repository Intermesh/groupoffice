<?php
/*
 * @copyright (c) 2016, Intermesh BV http://www.intermesh.nl
 * @author Michael de Hart <mdhart@intermesh.nl>
 * @license http://www.gnu.org/licenses/agpl-3.0.html AGPLv3
 */

namespace go\modules\community\calendar\model;

use DateInterval;
use DateTimeInterface;
use DateTimeZone;
use Exception;
use Generator;
use go\core\acl\model\AclItemEntity;
use go\core\db\Criteria;
use go\core\ErrorHandler;
use go\core\exception\Forbidden;
use go\core\exception\JsonPointerException;
use go\core\fs\Blob;
use go\core\http\PostResponseProcessor;
use go\core\model\Acl;
use go\core\model\Alert as CoreAlert;
use go\core\model\Principal;
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
use go\core\validate\ErrorCode;

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
		'createdBy','modifiedBy', 'lastOccurrence','firstOccurrence','etag', 'eventId', 'recurrenceId'];

	const UserProperties = ['keywords', 'color', 'freeBusyStatus', 'useDefaultAlerts', 'veventBlobId'];

	/**
	 * Details of a private event that only its owner may see. Also hidden in the recurrence overrides.
	 */
	const PrivateDetails = ['title', 'description', 'location', 'categoryIds', 'participants', 'replyTo'];

	/**
	 * Check if a property, or a property of a recurrence override patch like "participants/1", is a detail of a private
	 * event that only its owner may see. See {@see PrivateDetails}.
	 *
	 * @param string $property
	 * @return bool
	 */
	public static function isPrivateDetail(string $property): bool
	{
		return in_array($property, self::PrivateDetails) || str_starts_with($property, 'participants/');
	}

	/**
	 * Check if a property, or a property of a recurrence override patch, tells who takes part in the event
	 *
	 * @param string $property
	 * @return bool
	 */
	public static function isParticipantDetail(string $property): bool
	{
		return $property === 'participants' || $property === 'replyTo' || str_starts_with($property, 'participants/');
	}

	private ?bool $managesResource = null;

	/**
	 * The user manages the resource of this calendar, like the admin of a room, who has write permission on all its
	 * events. They see who takes part in a private booking and may answer it for the resource
	 * ({@see onlyResourceAnswerModified()}), but the other details stay hidden.
	 *
	 * @return bool
	 * @throws Exception
	 */
	public function managesResource(): bool
	{
		if(!isset($this->managesResource)) {
			$this->managesResource = isset($this->calendarId) &&
				$this->getPermissionLevel() >= 35 /* write all, see Calendar::getMyRights() */ &&
				!empty(Calendar::find(['id', 'groupId'])->selectSingleValue('groupId')->where('id', '=', $this->calendarId)->single());
		}
		return $this->managesResource;
	}

	/**
	 * Check if the only change to the participants is the answer of the resource of this calendar: its participation
	 * status and comment. That's what the admins of a resource may change in a private booking.
	 *
	 * @return bool
	 * @throws Exception
	 */
	public function onlyResourceAnswerModified(): bool
	{
		if(!$this->isModified('participants')) {
			return true;
		}
		$old = $this->getOldValue('participants') ?? [];
		$new = $this->participants ?? [];
		$oldIds = array_map('strval', array_keys($old));
		$newIds = array_map('strval', array_keys($new));
		sort($oldIds);
		sort($newIds);
		if($oldIds !== $newIds) {
			return false;
		}
		$resourceId = 'Calendar:' . $this->calendarId;
		foreach($new as $id => $participant) {
			$before = $old[$id]->toArray();
			$after = $participant->toArray();
			if((string) $id === $resourceId) {
				unset($before['participationStatus'], $before['participationComment'],
					$after['participationStatus'], $after['participationComment']);
			}
			if($before != $after) {
				return false;
			}
		}
		return true;
	}

	/**
	 * The only properties of an event that others see when they may only read the free / busy information of the
	 * calendar. Anything else, like the participants, would tell what the event is about or who made it.
	 */
	const BusyOnlyProperties = ['id', 'calendarId', 'uid', 'start', 'duration', 'timeZone', 'showWithoutTime',
		'recurrenceRule', 'recurrenceOverrides', 'recurrenceId', 'status', 'privacy', 'firstOccurrence', 'lastOccurrence',
		'permissionLevel'];

	/**
	 * The properties of a recurrence override that others see when they may only read the free / busy information
	 */
	const BusyOnlyOverrideProperties = ['start', 'duration', 'timeZone', 'showWithoutTime', 'status', 'excluded'];

//	// If any of this properties is in the recurrenceOverrides the Object most be ignored
//	const IgnoredPropertiesInException = [
//		'uid', 'links', 'method', 'privacy', 'prodId',
//		'recurrenceId', 'recurrenceOverrides','recurrenceRule',
//		'relatedTo','replyTo','sentBy','timeZones'];


	static bool $sendSchedulingMessages = false;

	/**
	 * Fired when an online meeting is requested with {@see setOnlineMeeting()}. Listeners get the event and the name of
	 * the service, like "jitsi". The listener of that service returns the URL to join the meeting, other listeners
	 * return null.
	 */
	const EVENT_ONLINE_MEETING = 'onlinemeeting';

	/**
	 * Service of the online meeting to add on save
	 */
	private ?string $onlineMeetingService = null;

	/**
	 * Only true when saving from JMAP controller
	 * @var bool
	 */
	static bool $fromClient = false;

	/** ID of the calendar the event is in. Use Calendar/query to find the calendars of the user. */
	public string $calendarId;
	protected ?int $eventId = null;
	/**
	 * @var boolean true when this event is created by this calendar system
	 * false if the event is imported from an invitation and the organizer is in another system
	 */
	public bool $isOrigin;
	/**
	 * When isOrigin is false this is the organizers email
	 * @var ?string email
	 */
	public ?string $replyTo;

	public ?string $id = null;
	public ?string $prodId = null;
	/** IANA time zone of "start", like "Europe/Amsterdam". Null for a floating event, which happens at the same local time in every time zone. */
	public ?string $timeZone;
	/** Language of the text of the event, like "en" or "nl-NL" */
	public ?string $locale;
	/** 0 is undefined, 1 is the highest and 9 the lowest priority */
	public int $priority = 0;
	/** Color of the event for this user, as a CSS color. Overrides the color of the calendar. */
	public ?string $color;
	/** Use the default alerts of the calendar instead of "alerts" */
	public ?bool $useDefaultAlerts = true;

	/**
	 * A unique identifier for the object.
	 */
	public ?string $uid = null;

	/**
	 * This is only set when somebody is invited to a single occurrence of a series.
	 *
	 * Als een participant is uitgenodigd dan wordt er een aparte event met UID . ‘_’. RECURRENCE-ID gemaakt.
	 *
	 * @var string|null
	 */
	public ?string $recurrenceId = null;

	/**
	 * Used for recurring series when an override excludes this instance
	 *
	 * @var bool|null
	 */
	public ?bool $excluded = false;

	/**
	 * This is a revision number that is increased by 1 every time the organizer
	 * makes a change (except when only the "participants" property is changed)
	 * @var int
	 */
	public int $sequence = 0;

	/**
	 * Time is ignored for this event when true
	 * @var bool
	 */
	public bool $showWithoutTime = false;

	/**
	 * The start time of the event
	 * @var ?DateTimeInterface|null
	 */
	public ?DateTimeInterface $start;

	/**
	 * The duration of the event (or the occurence)
	 * (optional, default: PT0S)
	 *
	 */
	public ?string $duration =  null;

	/**
	 * The title
	 */
	public ?string $title = '';

	/**
	 * free text that would describe the event
	 */
	public ?string $description = '';

	/**
	 * The location where the event takes place
	 */
	public ?string $location = null;

	/**
	 * Status of event (confirmed, canceled, tentative)
	 */
	public string $status = self::Confirmed;

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

	const FREEBUSY_BUSY = 'busy';
	const FREEBUSY_FREE = 'free';

	/**
	 * Is event Transparent or Opaque
	 * @var ?string
	 */
	public ?string $freeBusyStatus = self::FREEBUSY_BUSY;

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
	 * @var string with CalDAV server the `uri` from the client needs to be persistent. It's per calendar because every
	 * client uses its own name for its copy of the event.
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

	/** @var string[] IDs of the categories of the event. See Category/query. */
	public $categoryIds = [];
	public $links = [];

	public $modifiedAt;
	public $createdAt;
	public ?string $modifiedBy;
	public ?string $createdBy;

	/** The end time of the last occurrence in the series. or end time if not recurring */
	protected $lastOccurrence;
	/** The start time of the first occurence in the series, or start time if not recurring */
	protected $firstOccurrence;
	protected ?string  $ownerId = null; // find calendar owner to see if Private events needs to be altered

	/**
	 * Set on an instance of patchedInstance(). It is a new copy of the series, so isNew() can't tell who owns it.
	 */
	private ?bool $seriesOwnedByCurrentUser = null;

	public static function customFieldsTableName(): string
	{
		return 'calendar_event_custom_fields';
	}

	public function customFieldsModelId() : string|int|null {
		return $this->eventId;
	}

	/**
	 * List of columns to ignore when determining if modifiedAt or modifiedBy should be set.
	 * @return array
	 */
	protected static function ignorePropertiesForModifiedAt() : array {
		return ['veventBlobId', 'uri', 'uid'];
	}

	protected function internalGetPermissionLevel(): int
	{
		// Sometimes this model is used in an email invite without a calendarId.
		if(!isset($this->calendarId)) {
			return Acl::LEVEL_READ;
		} else {
			return parent::internalGetPermissionLevel(); // TODO: Change the autogenerated stub
		}
	}

	protected static function defineMapping(): Mapping {
		return parent::defineMapping()
			->addTable('calendar_calendar_event', 'cce', ['eventId' => 'eventId'], ['id', 'calendarId', 'uri'])
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

	/**
	 * Find the event in a calendar the user owns and has at least rsvp permission
	 *
	 * @param $uid string UID of the event
	 * @param $userId int id of owner
	 */
	static function findForUser($uid, $userId) {
		return self::findFor($userId)
			->where([
				'cal.ownerId' => $userId,
				'eventdata.uid' => $uid
			]);
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
				} else {
					// empty inCalendars filter will return no CalendarEvents
					$query->andWhere('cce.calendarId', '=', 0);
				}
			}, 'subscribedOnly')
			->add("hideCancelled",  function(Criteria $criteria, $value, Query $query) {
				if($value) {
					$query->andWhere('status', '!=', self::Cancelled);
				}
			},true)
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
				// The participant is found by user id or by e-mail. The id is not the user id when the organizer could not
				// see the user and picked another principal like "Contact:1".
				$query->join('core_user', 'invitee', 'invitee.id = cal.ownerId');
				$crit->where('cal.ownerId', '=', go()->getUserId())
					->andWhere('eventdata.status', '=', 'confirmed')
					->andWhere('eventdata.start', '>=', new DateTime())
					->andWhereExists(go()->getDbConnection()
						->select('p.id')
						->from('calendar_participant', 'p')
						->where('p.eventId = eventdata.eventId')
						->andWhere('(p.id = cal.ownerId OR p.email COLLATE utf8mb4_unicode_ci = invitee.email)')
						->andWhere('p.rolesMask & 1 = 0') // !isOwner
						->andWhere('p.participationStatus', '=', 'needs-action'));
			})->add('hideSecret', function(Criteria $criteria, $value, $query) {
				$query->andWhere((new Criteria())
					->where('privacy', '!=', 'secret')
					->orWhere('cal.ownerId','=',go()->getUserId())
				);
			},1);
	}

	public function categoryNames(): array
	{
		return go()->getDbConnection()
			->select('name')
			->from('calendar_category')
			->where('id','IN', $this->categoryIds)
			->fetchMode(\PDO::FETCH_COLUMN, 0)
			->all();
	}
	public function categoryIdsByName($names): void
	{
		$this->categoryIds = [];
		foreach($names as $name) {
			$idsQuery = go()->getDbConnection()
				->selectSingleValue('id')
				->from('calendar_category')
				->where('name', '=', $name)
				->andWhere(
					(new Criteria())
						->where('ownerId','=', go()->getUserId())->orWhere('ownerId','IS', null));

			// sometimes not set for new events.
			if(isset($this->calendarId)) {
				$idsQuery->andWhere((new Criteria())->where('calendarId', '=', $this->calendarId)->orWhere('calendarId', 'IS', null));
			}

			$id = $idsQuery->single();
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
			$this->modifiedAt = new DateTime(); // important to set here so blob will get the same modifiedAt
			$blob = ICalendarHelper::makeBlob($this);
			$this->veventBlobId = $blob->id;
			if(!$this->isNew()) {
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
	 * How the event repeats, as a JSCalendar RecurrenceRule like {"frequency": "weekly", "byDay": [{"day": "mo"}]}. Null when it doesn't repeat.
	 *
	 * @link https://www.rfc-editor.org/rfc/rfc8984.html#name-recurrencerules
	 * @return RecurrenceRule | null
	 */
	public function getRecurrenceRule() {
		return !empty($this->recurrenceRule) ? json_decode($this->recurrenceRule): null;
	}

	public function setRecurrenceRule($rule) {
		$this->recurrenceRule = empty($rule) ? null : json_encode($rule);
	}

	/**
	 * The end of the recurrence in the time zone of the event, or null when it has no "until".
	 *
	 * An "until" without a time includes that whole day, so a daily event at 9:00 until 2026-03-04 still occurs on the 4th.
	 * JSCalendar only allows a date with time, but the GroupOffice web client saved dates before.
	 *
	 * @return DateTime|null
	 */
	public function recurrenceUntil(): ?DateTime
	{
		$until = $this->getRecurrenceRule()->until ?? null;
		if(empty($until)) {
			return null;
		}
		if(strlen($until) == 10) {
			$until .= $this->showWithoutTime ? ' 00:00:00' : ' 23:59:59';
		}
		return new DateTime($until, $this->timeZone());
	}

	/**
	 * Store "until" as a date for full-day events and as a date with time for events with a time, like iCalendar
	 * requires the same type as the start. A date becomes the end of that day, so the event keeps its occurrence on that day.
	 */
	private function normalizeRecurrenceUntil(): void
	{
		$rule = $this->getRecurrenceRule();
		if(empty($rule->until)) {
			return;
		}
		if($this->showWithoutTime) {
			if(strlen($rule->until) == 10) {
				return;
			}
			$rule->until = substr($rule->until, 0, 10);
		} else {
			if(strlen($rule->until) != 10) {
				return;
			}
			$rule->until .= 'T23:59:59';
		}
		$this->setRecurrenceRule($rule);
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

	public function start($withoutTime = false, string|null $setTimeZoneTo = null): DateTime
	{
		if(!isset($this->start)) {
			$this->start = new DateTime();
		}
		$dt = new DateTime($this->start->format("Y-m-d". ($withoutTime?'':" H:i:s")), $this->timeZone());
		if(isset($setTimeZoneTo)) {
			$dt->setTimezone(new DateTimeZone($setTimeZoneTo));
		}
		return $dt;
	}

	public function end($withoutTime = false, string|null $setTimeZoneTo = null): DateTime
	{
		if(empty($this->start)) {
			$end = new DateTime();
		} else {
			$end = $this->start();
		}
		if(!empty($this->duration))
			$end->add(new DateInterval($this->duration));


		if(isset($setTimeZoneTo)) {
			$end->setTimezone(new DateTimeZone($setTimeZoneTo));
		}

		return $end;
	}

	/**
	 * Used in iMip mails for display
	 * Would be 2 datetimes, 1 date and 2 times if same day, or just 1 or 2 dates when full-day(s)
	 * @return string[] 2 lines of human readable text
	 */
	public function humanReadableDate(?string $tz = null): array
	{
		$fmtDate = fn($d) => go()->t($d->format('l')) . $d->format(' j ') .
			go()->t('full_months')[$d->format('n')] . $d->format(' Y');

		$start = $this->start(false, $tz);
		$end   = $this->end();

		if ($this->showWithoutTime) {
			$end->modify('-1 day');
		}

		$sameDay = $start->format('Ymd') === $end->format('Ymd');

		if ($sameDay && $this->showWithoutTime) {
			return [$fmtDate($start), ''];
		}

		$line1 = go()->t($sameDay ? 'At' : 'From', 'community', 'calendar') . ' ' . $fmtDate($start);

		if (!$sameDay) {
			if (!$this->showWithoutTime) {
				$line1 .= ', ' . $start->format('H:i');
			}
			$line1 .= ' ' . go()->t('until', 'community', 'calendar');
		}

		$line2 = $sameDay
			? $start->format('H:i') . ' - ' . $end->format('H:i')
			: $fmtDate($end) . (!$this->showWithoutTime ? ', ' . $end->format('H:i') : '');

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

		return $this->uri ?? self::defaultUri($this->uid);
	}

	/**
	 * The CalDAV uri of an event in a calendar when no client gave it a name
	 *
	 * @param string $uid
	 * @return string
	 */
	public static function defaultUri(string $uid): string
	{
		return strtr($uid, '+/=', '-_.') . '.ics';
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
	 * @return Generator<string, CalendarEvent>
	 * @throws JsonPointerException
	 */
	public function overrides($modifiedOnly = false) {
		if($this->isInstance()) {
			return; // yield nothing
		}

		if(isset($this->recurrenceOverrides)) {
			foreach ($this->recurrenceOverrides as $recurrenceId => $override) {
				if (!$modifiedOnly || $override->isModified()) {
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
		$patchArray = isset($this->recurrenceOverrides[$recurrenceId]) ? $this->recurrenceOverrides[$recurrenceId]->toArray() : [];

		//if start is not patched then we must set the recurrence ID to set the right time
		if(!isset($patchArray['start'])) {
			$patchArray['start'] = new DateTime($recurrenceId);
		}

		$copy = $this->copy();
		$copy->seriesOwnedByCurrentUser = $this->currentUserIsOwner();
		$e = JSON::patch($copy, $patchArray);
		$e->recurrenceId = $recurrenceId;
		$e->excluded = isset($this->recurrenceOverrides[$recurrenceId]) && $this->recurrenceOverrides[$recurrenceId]->excluded;
		unset($e->recurrenceRule, $e->recurrenceOverrides); // , $e->sentBy, $e->relatedTo,
		return $e;
		//return (new self())->setValues(array_merge($this->toArray(), $patch->toArray()));
	}

	public function isInstance() {
		return !empty($this->recurrenceId);
	}

	/**
	 * Add an online meeting on save, like "jitsi". The link to join it becomes the location and is added to the
	 * description. A meeting added earlier is replaced. Which services are available depends on the installed modules.
	 * Isn't stored: get doesn't return it and set responds with null, so a client doesn't send it again with a later
	 * update.
	 *
	 * @apiWriteOnly
	 * @param string|null $service
	 */
	public function setOnlineMeeting(?string $service): void
	{
		$this->onlineMeetingService = $service;
	}

	protected function internalValidate()
	{
		parent::internalValidate();

		if ($this->isNew() && self::$fromClient && $this->scheduledEventWithSameUidExists()) {
			$this->setValidationError('uid', ErrorCode::UNIQUE,
				go()->t("This event already exists. Open it in your calendar to respond to it.", 'community', 'calendar'));
		}

		if (isset($this->onlineMeetingService) && !$this->hasValidationErrors()) {
			$this->addOnlineMeeting($this->onlineMeetingService);
			$this->onlineMeetingService = null;
		}
	}

	/**
	 * Whether an event with participants and the same UID is already in a calendar. GroupOffice stores such an event
	 * once for all calendars it's in, so a client must not create a second copy, for example by adding an invitation
	 * from an e-mail attachment or by importing it. The participants would get two events and their replies would go to
	 * the wrong address.
	 */
	public function scheduledEventWithSameUidExists(): bool
	{
		if (empty($this->uid)) {
			return false;
		}
		return (bool) go()->getDbConnection()->selectSingleValue('e.eventId')
			->from('calendar_event', 'e')
			->join('calendar_calendar_event', 'cce', 'cce.eventId = e.eventId')
			->join('calendar_participant', 'p', 'p.eventId = e.eventId')
			->where(['e.uid' => $this->uid, 'e.recurrenceId' => $this->recurrenceId])
			->limit(1)
			->single();
	}

	/**
	 * Ask the service for a link to join a new meeting and put it in the location and description
	 */
	private function addOnlineMeeting(string $service): void
	{
		$uri = static::fireEvent(self::EVENT_ONLINE_MEETING, $this, $service);
		if (!is_string($uri) || $uri === '') {
			$this->setValidationError('onlineMeeting', ErrorCode::INVALID_INPUT,
				"The online meeting service '" . $service . "' isn't available");
			return;
		}

		$separator = "--------------------------------";
		$label = go()->t("Join the online meeting now");

		// Replace a meeting that was added before, so the description doesn't get several links
		$description = preg_replace('/\n*' . $separator . '\n' . preg_quote($label, '/') . ':\n\n.*?\n' . $separator . '\n*/s', '', $this->description ?? '');
		$description = trim($description);
		if ($description !== '') {
			$description .= "\n\n";
		}

		$this->description = $description . $separator . "\n" . $label . ":\n\n" . $uri . "\n" . $separator . "\n";
		$this->location = $uri;
	}

	protected function internalSave() : bool {

		if(empty($this->uri)) {
			$this->uri = self::defaultUri($this->uid);
		}

		$this->checkScheduleFor();

		if($this->isNew() || $this->isModified(['recurrenceRule', 'showWithoutTime'])) {
			$this->normalizeRecurrenceUntil();
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
			if($this->isModified([...self::EventProperties, 'participants', 'recurrenceOverrides']) && $this->isOrigin) {
				if(!$this->isModified('sequence') || $this->sequence <= $this->getOldValue('sequence'))
					$this->sequence += 1;
			}
			if(self::$fromClient) {
				if (!$this->isOrigin && $this->isModified(self::EventProperties)) {
					// properties may only change by ITip. client is not allowed to change properties of invites
					throw new Forbidden('Not allowed to edit anything other than per-user properties when isOrigin is false');
				}

				if ($this->organizerRoleMoved() && empty(\go\modules\community\calendar\Module::get()->getModel()->getUserRights()->mayChangeOrganizer)) {
					throw new Forbidden('Not allowed to change the organizer');
				}

				$currPart = $this->calendarParticipant();
				if ($currPart && !$currPart->isOwner() && $this->ownerId !== null) { // not owner, not shared
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
		if(!empty($this->alerts)) {
			$this->useDefaultAlerts = false;
//			$this->checkAlertOwner();
		}
		if(empty($this->prodId) || $this->prodId === 'Unknown') {
			$this->prodId = self::prodId();
		}

//		if($this->isModified(['participants'])) {
//			//if the principal ID is not numeric it's not a user. We'll try to find a user for it.
//			foreach($this->participants as $pid => $participant) {
//				if ($participant->isNew() && !is_numeric($pid) && $participant->kind == 'individual') {
//					$principalId = User::findIdByEmail($participant->email);
//					if ($principalId) {
//						// Override ID with user ID
//						$this->participants[$pid]->pid($principalId);
//					}
//				}
//			}
//		}

		if(!empty($this->participants)) {
			// reset participation status if event changes "materially"
			if($this->isModified(['start', 'duration', 'recurrenceRule', 'location'])) {
				foreach ($this->participants as $participant) {
					if (!$participant->isNew() && !$participant->isOwner())
						$participant->participationStatus = Participant::NeedsAction;

					// AUTO-ACCEPT resources if checkbox is on
					if($participant->kind === 'resource') {
						$resource = Calendar::findById(str_replace('Calendar:', '', $participant->pid()));
						if($resource && !empty($resource->groupId)) {
							$rgroup = ResourceGroup::findById($resource->groupId);
							if ($rgroup && $rgroup->autoAccept && $participant->isFree($this->start(), $this->end())) {
								$participant->participationStatus = Participant::Accepted;
							}
						}
					}
				}
			}
			if (empty($this->replyTo)) {

				// In calendars without an owner the creator will be the organizer. Otherwise the calendar owner is
				// the organizer.
				$calendar = Calendar::findById($this->calendarId, ['ownerId']);
				if($calendar->isOwned()) {
					$owner = Principal::findById($calendar->getOwnerId(), ['email']);
				} else {
					$owner = $this->organizer();
					if (empty($owner)) {
						$owner = go()->getAuthState()->getUser(['email']);
					}
				}
				$this->replyTo = $owner->email;
			} else if ($this->organizerChanged()) {
				// "Change organizer": replies must go to the new organizer, not to the one who created the event
				$this->replyTo = $this->organizer()->email;
			}
		}

		if(self::$sendSchedulingMessages) {
			Scheduler::handle($this);
		}

		$removedParticipants = $this->removedParticipants();
		$success = parent::internalSave();

		if($success) {
			$this->addToKnownCalendars();
			$this->removeFromKnownCalendars($removedParticipants);
			if(!$this->isNew() && $this->isModified(['alerts', 'useDefaultAlerts'])) {
				// Other users may get these alerts, see Calendar::getUseCreatorAlerts()
				$this->resetICalBlobsOfAllUsers();
			}
			$this->updateAlerts();
			$this->changeEventsWithSameUID();
			$this->incrementCalendarModSeq();
		}
		return $success;
	}

	protected static function internalDelete(Query $query): bool
	{

		$declinedEventIds = [];
		$events = CalendarEvent::find()->mergeWith(clone $query);
		foreach ($events as $event) {
			/** @var CalendarEvent $event */
			if(!empty($event->participants)) {
				$current = $event->calendarParticipant();
				if(!empty($current) && $current->isOwner()) {
					// when owner deletes, free resources
					foreach ($event->participants as $pid => $participant) {
						if ($participant->kind === 'resource') {
							$calId = str_replace( 'Calendar:', '', $participant->pid());
							go()->getDbConnection()->delete('calendar_calendar_event', [
								'calendarId' => $calId, 'eventId' => $event->eventId
							])->execute();
						}
						if($participant->kind == 'individual' && is_numeric($pid)) {
							$personalCalendarId = Calendar::fetchPersonal($pid);
							if ($personalCalendarId) {
								go()->getDbConnection()->delete('calendar_calendar_event', [
									'calendarId'=>$personalCalendarId, 'eventId'=>$event->eventId
								])->execute();
							}
						}
					}
				} else if(!empty($current) && $current->participationStatus !== Participant::Declined) {
					// Deleting the event from your own (or resource) calendar declines it. The participants are shared
					// with the organizer's event, so store it also when no reply is sent ("Save only").
					go()->getDbConnection()->update('calendar_participant',
						['participationStatus' => Participant::Declined],
						['eventId' => $event->eventId, 'id' => $current->pid()]
					)->execute();
					$declinedEventIds[] = $event->eventId;
				}
				if(self::$sendSchedulingMessages) {
					Scheduler::handle($event, true);
				}
			}
			CoreAlert::deleteByEntity($event);

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
		if($success && !empty($declinedEventIds)) {
			// Let the organizer and other participants sync the declined status
			foreach(array_unique($declinedEventIds) as $eventId) {
				$remaining = CalendarEvent::find()->where('cce.eventId', '=', $eventId)->single();
				if($remaining) {
					$remaining->changeEventsWithSameUID();
					$remaining->incrementCalendarModSeq();
				}
			}
		}
		return $success;
	}

	public static function prodId() : string {
		return str_replace('{VERSION}', go()->getVersion(),self::PROD);
	}

	public function currentUserIsOwner() {
		if(isset($this->seriesOwnedByCurrentUser)) {
			return $this->seriesOwnedByCurrentUser;
		}
		return $this->isNew() || $this->ownerId === go()->getUserId() ||
		 ($this->ownerId === null && $this->createdBy === go()->getUserId());
	}

	/**
	 * Private events of others only show the time. So do all events in a calendar on which the user may only read the
	 * free / busy information.
	 *
	 * @return bool
	 */
	public function showAsPrivate(): bool
	{
		if($this->currentUserIsOwner()) {
			return false;
		}
		return $this->isPrivate() || $this->mayOnlyReadFreeBusy();
	}

	/**
	 * The user may only read the free / busy information of the calendar of this event, so the event shows only when
	 * it's busy. See {@see BusyOnlyProperties}.
	 *
	 * @return bool
	 */
	public function showAsBusyOnly(): bool
	{
		return !$this->currentUserIsOwner() && $this->mayOnlyReadFreeBusy();
	}

	/**
	 * The logged-in user has less than read permission on the calendar. Without a logged-in user, the event is shown
	 * through a secret instead of the permissions, like in a published calendar or the page of an invitation, so it
	 * shows as before.
	 */
	private function mayOnlyReadFreeBusy(): bool
	{
		$state = go()->getAuthState();
		return $state && $state->isAuthenticated() && $this->getPermissionLevel() < Acl::LEVEL_READ;
	}

	/**
	 * Remove everything but the busy time from an event array of {@see toArray()}, when {@see showAsBusyOnly()}
	 *
	 * @param array $arr
	 * @return array
	 */
	public function hideBusyOnlyDetails(array $arr): array
	{
		if(!$this->showAsBusyOnly()) {
			return $arr;
		}
		$arr = array_intersect_key($arr, array_flip(self::BusyOnlyProperties));
		if(!empty($arr['recurrenceOverrides'])) {
			foreach($arr['recurrenceOverrides'] as $recurrenceId => $patch) {
				if(is_array($patch)) {
					$arr['recurrenceOverrides'][$recurrenceId] = array_intersect_key($patch, array_flip(self::BusyOnlyOverrideProperties));
				}
			}
		}
		return $arr;
	}

	/**
	 * The events that users who may only read the free / busy information of a calendar see in it. The same events
	 * as {@see BusyPeriod::fetch()} uses for the availability: not secret, not cancelled and not "free" for the owner
	 * of the calendar. A resource calendar has no owner, so its events are always busy.
	 *
	 * For a query of {@see find()}.
	 *
	 * @return Criteria
	 */
	public static function busyCriteria(): Criteria
	{
		return (new Criteria())
			->where('eventdata.privacy', '!=', self::Secret)
			->andWhere('eventdata.status', '!=', self::Cancelled)
			->andWhereExists(go()->getDbConnection()
				->select('fb.eventId')
				->from('calendar_event_user', 'fb')
				->where('fb.eventId = cce.eventId AND fb.userId = cal.ownerId AND cal.groupId IS NULL')
				->andWhere('fb.freeBusyStatus', '=', self::FREEBUSY_FREE), true);
	}

	/**
	 * Limit a query of {@see find()} to the events the current user may see: all events in calendars with read
	 * permission and, with $withBusyOnly, the busy events in calendars with only free / busy permission
	 * ({@see busyCriteria()}).
	 *
	 * @param Query $query
	 * @param bool $withBusyOnly
	 * @return void
	 * @throws Exception
	 */
	public static function applyVisibleToQuery(Query $query, bool $withBusyOnly): void
	{
		if(User::isAdminById(go()->getUserId())) {
			return;
		}

		$visible = (new Criteria())->where('cce.calendarId', 'IN', self::calendarIdsWithLevel(Acl::LEVEL_READ));
		if($withBusyOnly) {
			$visible->orWhere((new Criteria())
				->where('cce.calendarId', 'IN', self::calendarIdsWithLevel(Calendar::LEVEL_FREEBUSY))
				->andWhere(self::busyCriteria()));
		}
		$query->andWhere($visible);
	}

	/**
	 * Subquery with the IDs of the calendars on which the current user has at least this permission level
	 */
	private static function calendarIdsWithLevel(int $level): Query
	{
		$calendars = Calendar::find(['id'])->selectSingleValue('calendar_calendar.id');
		Calendar::applyAclToQuery($calendars, $level);
		return $calendars;
	}

	/**
	 * Check if the current user may see this event while having only free / busy permission on its calendar. With the
	 * same query as {@see applyVisibleToQuery()}, so getting an event by ID shows the same events as a query.
	 *
	 * @return bool
	 * @throws Exception
	 */
	public function isVisibleAsBusy(): bool
	{
		return self::find(['id'])
			->selectSingleValue('cce.id')
			->where('cce.id', '=', $this->id)
			->andWhere(self::busyCriteria())
			->single() !== null;
	}

	/**
	 * Clear the details that others may not see of a private event. Use when the event is shown in another way than
	 * toArray(), like in the print reports. Don't save it afterwards.
	 */
	public function hidePrivateDetails(): void
	{
		if($this->showAsPrivate()) {
			$this->title = '';
			$this->description = '';
			$this->location = '';
			$this->categoryIds = [];
		}
	}

	public function toArray(array|null $properties = null): array
	{
		// Not loaded when the client asked for other properties only
		if(isset($this->start)) {
			if (!($this->start instanceof DateTimeInterface)) {
				//make sure timezone info is not sent by setting isLocal below. We can't be sure this datetime is a go\core\util\DateTime
				$this->start = new DateTime($this->start);
			}
			$this->start->isLocal = true;
		}

		$arr =  parent::toArray($properties);
		if($this->showAsPrivate()) {
			$arr['title'] = '';
			$arr['description'] = '';
			$arr['location'] = '';
			$arr['categoryIds'] = [];
			// Who takes part tells what the event is about. Left out, so the client doesn't send them back. Except for
			// the admins of a resource, who answer the booking.
			$hideParticipants = !$this->managesResource();
			if($hideParticipants) {
				unset($arr['participants'], $arr['replyTo']);
			}
			// An exception of the series may have its own details. Leave them out instead of clearing them, so the
			// client doesn't send empty values back. RecurrenceOverride::setValues() keeps them when it does.
			if(!empty($arr['recurrenceOverrides'])) {
				foreach($arr['recurrenceOverrides'] as $recurrenceId => $patch) {
					if(is_array($patch)) {
						$arr['recurrenceOverrides'][$recurrenceId] = array_filter($patch,
							fn($property) => !self::isPrivateDetail((string) $property) ||
								(!$hideParticipants && self::isParticipantDetail((string) $property)),
							ARRAY_FILTER_USE_KEY);
					}
				}
			}
		}
		unset($arr['recurrenceId'], $arr['excluded']);
		return $this->hideBusyOnlyDetails($arr);
	}

	private function addToKnownCalendars() {
		if(empty($this->participants)) return;
		foreach($this->participants as $pid => $participant) {
			// Don't re-add a resource when its admin removed the event from the resource calendar
			$isNew = $participant->kind == 'resource' ? ($participant->isNew() || $participant->isModified('participationStatus')) : $participant->isNew();
			$calendarId = $isNew ? self::participantCalendarId($pid, $participant) : null;
			if($calendarId) {
				go()->getDbConnection()->insertIgnore('calendar_calendar_event', [
					['calendarId'=>$calendarId, 'eventId'=>$this->eventId, 'uri' => self::defaultUri($this->uid)]
				])->execute();
			}
		}
	}

	/**
	 * Participants (not the organizer) that were removed from the event in this save
	 *
	 * @return Participant[] by participant id
	 */
	public function removedParticipants(): array {
		if($this->isNew() || !$this->isModified('participants')) return [];
		$removed = [];
		foreach($this->getOldValue('participants') ?? [] as $pid => $participant) {
			if(!isset($this->participants[$pid]) && !$participant->isOwner()) {
				$removed[$pid] = $participant;
			}
		}
		return $removed;
	}

	/**
	 * The calendar an event is shown in for a resource or local user participant
	 */
	private static function participantCalendarId(string|int $pid, Participant $participant): ?int {
		if($participant->kind == 'resource') {
			$calendar = Calendar::findById(str_replace('Calendar:', '', $pid));
			return $calendar ? (int) $calendar->id : null;
		}
		if($participant->kind == 'individual' && is_numeric($pid)) {
			return Calendar::fetchPersonal($pid) ?: null;
		}
		return null;
	}

	/**
	 * Take the event out of the calendars of removed participants
	 *
	 * @param Participant[] $removed
	 */
	private function removeFromKnownCalendars(array $removed): void {
		foreach($removed as $pid => $participant) {
			$calendarId = self::participantCalendarId($pid, $participant);
			if($calendarId && $calendarId != $this->calendarId) {
				go()->getDbConnection()->delete('calendar_calendar_event', [
					'calendarId' => $calendarId,
					'eventId' => $this->eventId
				])->execute();
			}
		}
	}

	/**
	 * Schedule the alerts of every subscriber of the calendar again when the event or its alerts changed.
	 */
	private function updateAlerts() : void {
		if(!CoreAlert::$enabled) {
			return;
		}

		if($this->isNew() ||
			$this->isModified(['useDefaultAlerts', 'alerts', 'start','duration','recurrenceOverrides']) ||
			($this->useDefaultAlerts && $this->isModified('calendarId'))
		) {
			// loop all calendar subscribers
			$subscribers = go()->getDbConnection()->query('select userId from calendar_calendar_user where id = '.$this->calendarId.' AND isSubscribed = 1')->fetchAll(\PDO::FETCH_COLUMN);
			foreach($subscribers as $subscriber) {
				$this->rescheduleAlertsFor((int) $subscriber);
			}

		}

	}

	/**
	 * Replace the scheduled alerts of a user for this event with the alerts that apply to the user now.
	 *
	 * @param int $userId
	 * @throws Exception
	 */
	public function rescheduleAlertsFor(int $userId): void {
		CoreAlert::delete(
			(new Query())
				->where(['entityTypeId' => self::entityType()->getId(), 'entityId' => $this->id, 'userId' => $userId])
				->setData(['preventDismiss' => true]) // dismissing would schedule the next occurrence
		);
		$alerts = $this->alertsFor($userId);
		// An alert shows the title, so not to subscribers that may only read the free / busy information
		if(empty($alerts) || !$this->mayReadDetails($userId)) {
			return;
		}
		foreach ($alerts as $alert) {
			$alert->schedule($this, $userId);
		}
	}

	/**
	 * Check if a user may read the details of this event, and not only its free / busy information
	 *
	 * @param int $userId
	 * @return bool
	 * @throws Exception
	 */
	private function mayReadDetails(int $userId): bool {
		if(isset($this->ownerId) && $this->ownerId == $userId) {
			return true;
		}
		$calendar = Calendar::findById($this->calendarId);
		return $calendar && Acl::getUserPermissionLevel($calendar->findAclId(), $userId) >= Acl::LEVEL_READ;
	}

	/**
	 * The alerts of the event for the user it was loaded for.
	 *
	 * @see alertsFor()
	 * @return Alert[]
	 */
	public function alerts(): array {
		return $this->alertsFor($this->forUserId());
	}

	/**
	 * The alerts of the event for a user:
	 *
	 * 1. The alerts the user set on the event.
	 * 2. Or the alerts the creator set on the event, when {@see Calendar::getUseCreatorAlerts()} is on for the user. In a
	 *    calendar with an owner these are the alerts of the owner, because {@see checkScheduleFor()} gives the alerts
	 *    that someone sets when creating an event to the owner.
	 * 3. Or the default alerts of the user's calendar.
	 *
	 * @param int|null $userId null when nobody is logged in
	 * @return Alert[]
	 */
	public function alertsFor(?int $userId): array {
		[$useDefaultAlerts, $alerts] = $this->ownAlertsOf($userId);
		if(!$useDefaultAlerts) {
			return $alerts;
		}
		$inherited = $this->inheritedAlertsFor($userId);
		return $inherited['creator'] ?? $inherited['default'];
	}

	/**
	 * The alerts a user gets when the user didn't set alerts on the event: "default" has the default alerts of the
	 * user's calendar and "creator" the alerts of the creator, when {@see Calendar::getUseCreatorAlerts()} is on for the
	 * user and the creator set alerts.
	 *
	 * @param int|null $userId null when nobody is logged in
	 * @return array{default: Alert[], creator?: Alert[]}
	 */
	public function inheritedAlertsFor(?int $userId): array {
		$defaultsProp = $this->showWithoutTime ? 'defaultAlertsWithoutTime' : 'defaultAlertsWithTime';
		$props = ['id', 'ownerId', 'groupId', 'useCreatorAlerts', $defaultsProp];
		$query = isset($userId) ? Calendar::findFor($userId, $props) : Calendar::find($props);
		$calendar = $query->where('id', '=', $this->calendarId)->single();
		if(!$calendar) {
			return ['default' => []];
		}

		$inherited = ['default' => $calendar->$defaultsProp ?? []];
		if(isset($userId) && $calendar->getUseCreatorAlerts()) {
			$creatorId = $calendar->getOwnerId() ?? $this->createdBy;
			if(!empty($creatorId) && $creatorId != $userId) {
				[$creatorUsesDefaultAlerts, $creatorAlerts] = $this->ownAlertsOf((int) $creatorId);
				if(!$creatorUsesDefaultAlerts && !empty($creatorAlerts)) {
					$inherited['creator'] = $creatorAlerts;
				}
			}
		}
		return $inherited;
	}

	/**
	 * Whether a user uses the default alerts on this event and the alerts the user set on it. A user without per-user
	 * data for the event uses the default alerts.
	 *
	 * @param int|null $userId
	 * @return array{0: bool, 1: Alert[]}
	 */
	private function ownAlertsOf(?int $userId): array {
		if($userId === null || $userId === $this->forUserId()) {
			return [$this->useDefaultAlerts !== false, $this->alerts ?? []];
		}
		if(empty($this->id)) {
			// not saved yet, so other users have no data for it
			return [true, []];
		}
		$event = self::findFor($userId, ['id', 'useDefaultAlerts', 'alerts'])->where('cce.id', '=', $this->id)->single();
		if(!$event) {
			return [true, []];
		}
		return [$event->useDefaultAlerts !== false, $event->alerts ?? []];
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
	 * Whether the current user may change the event itself and not only their own participation status and per-user
	 * properties. These are the same rules internalSave() enforces for JMAP clients.
	 *
	 * @return bool
	 */
	public function mayChangeEventData(): bool
	{
		if(!$this->isOrigin || !$this->hasPermissionLevel(Acl::LEVEL_WRITE)) {
			return false;
		}
		// Others get a private event without its details, so the data they send back would remove them
		if($this->showAsPrivate()) {
			return false;
		}
		$participant = $this->calendarParticipant();
		return !$participant || $participant->isOwner() || $this->ownerId === null;
	}

	/**
	 * Remove the stored iCalendar data of every user that has this event, so it's generated again on the next request.
	 * Use this when shared data changed that resetICalBlob() does not see, like the participants.
	 */
	public function resetICalBlobsOfAllUsers(): void
	{
		go()->getDbConnection()->update('calendar_event_user', ['veventBlobId' => null], ['eventId' => $this->eventId])->execute();
	}

	/**
	 * Remove the stored iCalendar data of a user for this event, so it's generated again on the next request. Use this
	 * when data of the user changed that is not saved with the event, like the alerts the user inherits.
	 *
	 * @param int $userId
	 */
	public function resetICalBlobOf(int $userId): void
	{
		go()->getDbConnection()->update('calendar_event_user', ['veventBlobId' => null], ['eventId' => $this->eventId, 'userId' => $userId])->execute();
	}

	/**
	 * The participant in the event that owns the calendar this event is in
	 * @return false|Participant
	 */
	public function calendarParticipant() {
		if($this->calendarParticipant === null && !empty($this->participants)) {

			$ownerId = go()->getDbConnection()
				->selectSingleValue("ownerId")
				->from("calendar_calendar")
				->where('id', '=', $this->calendarId)
				->single() ?? go()->getUserId();

			$scheduleIds = User::findEmailAliases($ownerId);

			foreach ($this->participants as $p) {
				if (in_array(strtolower($p->email), $scheduleIds)) {
					$this->calendarParticipant = $p;
					break;
				}
			}
			if (!isset($this->calendarParticipant)) {
				$this->calendarParticipant = false;
			}
		}
		return $this->calendarParticipant;
	}

	/**
	 * @param $email string the scheduleId
	 * @return false|Participant
	 */
	public function participantByScheduleId(string $email, ?string $kind = null): bool|Participant
	{
		if(empty($this->participants))
			return false;

		$email = strtolower($email);

		foreach($this->participants as $participant) {
			if(strtolower($participant->email) === $email && ($kind === null || $participant->kind === $kind)) {
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

	/**
	 * Whether another participant became the organizer of this event in this save. Only for events that were created
	 * in GroupOffice. For an invitation from outside, replyTo is the address of the external organizer.
	 */
	private function organizerChanged(): bool
	{
		if (!$this->isOrigin) {
			return false;
		}
		$previous = $this->previousOrganizer();
		$organizer = $this->organizer();
		return $previous && !empty($organizer->email) && strtolower($previous->email) !== strtolower($organizer->email);
	}

	/**
	 * Whether the organizer role was moved to another participant or taken away in this save. This needs the
	 * "mayChangeOrganizer" right. Removing all participants is not a change of the organizer.
	 */
	private function organizerRoleMoved(): bool
	{
		$previous = $this->previousOrganizer();
		if (!$previous || empty($this->participants)) {
			return false;
		}
		$organizer = $this->organizer();
		return !$organizer || strtolower($previous->email) !== strtolower($organizer->email);
	}

	/**
	 * The organizer before this save, when the participants changed
	 */
	private function previousOrganizer(): ?Participant
	{
		if ($this->isNew() || !$this->isModified('participants')) {
			return null;
		}
		foreach ($this->getOldValue('participants') ?? [] as $old) {
			if ($old->isOwner()) {
				return $old;
			}
		}
		return null;
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

	public function isInPast(): bool
	{
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
			return !empty($this->timeZone) ? new DateTimeZone($this->timeZone) : null;
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
		if($this->isModified('recurrenceRule')) {
			$r = $this->getRecurrenceRule();
			if (!empty($r->until)) {
				$until = $this->recurrenceUntil()->add(new DateInterval($this->duration));
				if ($this->lastOccurrence === null || $until > $this->lastOccurrence) {
					$this->lastOccurrence = $until;
				}
			} else  {
				$this->lastOccurrence = null;
				if (isset($r->count)) {
					$it = ICalendarHelper::makeRecurrenceIterator($this);
					$maxDate = new \DateTime(self::MAX_RECUR);
					while ($it->valid()) {
						$dt = $it->current(); // will clone :(
						if ($dt > $maxDate) break;
						if ($dt > $this->lastOccurrence) $this->lastOccurrence = $dt;
						$it->next();
					}
					$this->lastOccurrence->add(new DateInterval($this->duration));
				}
			}
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

	/**
	 * toArray() hides the details of private events of others with these, also when a client asks for other
	 * properties only. The calendar owner is always selected by the mapping.
	 */
	protected static function internalRequiredProperties(): array
	{
		return array_merge(parent::internalRequiredProperties(), ['privacy', 'createdBy']);
	}

	protected static function aclEntityKeys(): array
	{
		return ['calendarId' => 'id'];
	}

	protected function getSearchModifiedAt(): DateTimeInterface
	{
		// Link panel modifiedAt is always saved as UTC
		$date = $this->start()->setTimeZone(new DateTimeZone("UTC"));
		$year = (int) $date->format('Y');
		if( $year >= 1000 && $year <= 9999) //is valid year for mysql
			return $date;
		return new DateTime("1970-01-01 00:00:00");

	}

	public function title(): string {
		return $this->isPrivate() ? '' : $this->title;
	}

	protected function includeInSearch(): bool{
		return !$this->isPrivate();
	}

	protected static function neededSearchProperties(): array {
		return ['id', 'showWithoutTime', 'start', 'timeZone','location', 'title','privacy', 'description'];
	}

	private static $calNameCache = [];

	protected function getSearchDescription(): string
	{
		if(!isset(self::$calNameCache[$this->calendarId])) {
			self::$calNameCache[$this->calendarId] = Calendar::findById($this->calendarId, ['name'], true)->name;
		}

		$u = go()->getAuthState()->getUser();
		if($this->showWithoutTime) {
			$format = $u ? $u->dateFormat : "d-m-Y";
		} else {
			$format = $u ? $u->dateFormat . ' '. $u->timeFormat : "d-m-Y H:i";
		}

		return self::$calNameCache[$this->calendarId] .': '. $this->title() . ' - '. $this->start->format($format);
	}

	protected function getSearchKeywords(): ?array {
		$kw = [$this->title, $this->description, self::$calNameCache[$this->calendarId]];
		if(!str_starts_with(($this->location ?? ''), 'http')) {
			$kw[] = $this->location;
		}
		return $kw;
	}

	/**
	 * When the series start time changes all the recurrence id's must be recreated.
	 *
	 * @return void
	 * @throws Exception
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

	/**
	 * When a secretary makes an appointment on behalf of the calendar owner. We need to change the alerts
	 * to the calendar owner.
	 *
	 * This is not according to JMAP standards but it makes sense for users. The downside this only happens when
	 * creating the event. It can't be changed afterwards.
	 *
	 * It also only happens for non-default alerts. So only when a user explicitly sets an alert.
	 *
	 * @return void
	 * @throws Exception
	 */
	private function checkScheduleFor() : void
	{
		if(!$this->isNew()) {
			return;
		}

		$calendar = Calendar::findById($this->calendarId, ['ownerId', 'groupId']);
		$calendarOwnerId = $calendar->getOwnerId();
		if(!$calendarOwnerId || $this->forUserId() == $calendarOwnerId) {
			return;
		}

		$this->forUserId($calendarOwnerId);
		foreach($this->alerts as $alert) {
			$alert->forUserId($calendarOwnerId);
		}

	}
}