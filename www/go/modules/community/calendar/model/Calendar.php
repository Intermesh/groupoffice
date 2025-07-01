<?php
namespace go\modules\community\calendar\model;

use go\core\acl\model\AclOwnerEntity;
use go\core\App;
use go\core\db\Criteria;
use go\core\fs\Blob;
use go\core\http;
use go\core\model\Principal;
use go\core\model\User;
use go\core\orm\Filters;
use go\core\orm\Mapping;
use go\core\orm\PrincipalTrait;
use go\core\orm\Query;
use go\core\orm\Relation;

/**
 * Calendar entity
 *
 */
class Calendar extends AclOwnerEntity {

	use PrincipalTrait {
		queryMissingPrincipals as protected traitMissingPrincipals;
	}

	/* Include In Availability */
	const All = 'all';
	const Attending = 'attending';
	const None = 'none';

	const UserProperties = ['color', 'sortOrder', 'isVisible', 'isSubscribed', 'includeInAvailability'];

	public ?string $id;
	/** @var string The user-visible name of the calendar */
	public string $name;
	public ?string $description;
	/** @var ?string Any valid CSS color value. The color to be used when displaying events associated with the calendar */
	public ?string $color = null;
	/** @var ?int uint32 Defines the sort order of calendars when presented in the client’s UI, so it is consistent between devices */
	public ?int $sortOrder = 0;
	/** @var bool Has the user indicated they wish to see this Calendar in their client */
	public ?bool $isSubscribed = null;
	/** @var bool Should the calendar’s events be displayed to the user at the moment? */
	public ?bool $isVisible = null; // per user
	/**
	 * @var string (default: all) Should the calendar’s events be used as part of availability calculation?
	 * This MUST be one of:
	 *	- all: all events are considered.
	 *	- attending: events the user is a confirmed or tentative participant of are considered.
	 *	- none: all events are ignored (but may be considered if also in another calendar).
	 */
	public ?string $includeInAvailability = null;

	/** @var ?string default for event. If NULL client will use the Users default timeZone  */
	public ?string $timeZone = null;

	protected ?string $defaultColor = null;


	/**
	 * @var DefaultAlert[]
	 */
	public ?array $defaultAlertsWithTime;

	/**
	 * @var DefaultAlertWT[]
	 */
	public ?array $defaultAlertsWithoutTime;
	protected ?int $ownerId;
	public ?string $createdBy;
	public ?string $webcalUri = null;

	public ?string $groupId;
	protected ?string $highestItemModSeq;

	protected static function defineMapping(): Mapping
	{
		return parent::defineMapping()
			->addTable("calendar_calendar")
			->addUserTable('calendar_calendar_user', 'caluser',['id' => 'id'], self::UserProperties)
			->add('defaultAlertsWithTime', Relation::map(DefaultAlert::class)->keys(['id'=>'fk']))
			->add('defaultAlertsWithoutTime', Relation::map(DefaultAlertWT::class)->keys(['id'=>'fk']));
	}

	protected static function textFilterColumns(): array
	{
		return ['name'];
	}

	public function setOwnerId($v) {
		$this->ownerId = $v;
	}

	public function getOwnerId() {
		return !empty($this->groupId) ? ('Calendar:'.$this->id) : $this->ownerId;
	}

	/** @return int */
	public static function fetchDefault($userId) {
		$user = User::findById($userId, ['calendarPreferences'], true);
		if(!empty($user)) {
			/** @var Preferences $pref */
			$pref = $user->calendarPreferences;
			if (!empty($pref->defaultCalendarId)) {
				return $pref->defaultCalendarId;
			}
		}
		// If default preference is empty use the first owned calendar
		return self::find()->selectSingleValue('calendar_calendar.id')
			->where(['calendar_calendar.ownerId' => $userId])
			->andWhere(['groupId'=>null])
			->orderBy(['sortOrder'=>'ASC'])
			->single();
	}

	public function getColor() {
		return $this->color ?? $this->defaultColor;
	}

	public function setColor($value) {
		$this->color = $value;
	}

	protected function getDefaultCreatedBy(): ?int
	{
		if(!empty($this->ownerId)) {
			return $this->ownerId;
		}
		return parent::getDefaultCreatedBy();
	}


	protected static function defineFilters(): Filters
	{
		return parent::defineFilters()->add('isSubscribed', function(Criteria $criteria, $value, Query $query) {
			$criteria->where('isSubscribed','=', $value);
				if($value === false) {
					$criteria->orWhere('isSubscribed', 'IS', null);
				}
		})->add('isResource', function(Criteria $criteria, $value, Query $query) {
			$criteria->where('groupId',$value?'IS NOT':'IS', null);
		})->add('groupId', function(Criteria $criteria, $value, Query $query) {
			$criteria->where('groupId','=', $value);
		})->add('davaccountId', function(Criteria $criteria, $value, Query $query) {
			//$criteria->where('davc.davaccountId','=', $value);
		});
	}

	/**
	 * @return int highest mod
	 */
	public function highestItemModSeq() {
		return $this->highestItemModSeq;
	}

	static function updateHighestModSeq(\go\core\db\Query|int|array $calendarId) {
		go()->getDbConnection()
			->update(self::getMapping()->getPrimaryTable()->getName(),
				['highestItemModSeq' => CalendarEvent::getState()],
				['id' => $calendarId]
			)->execute();
	}

	/**
	 *  per-user OR default = true ONLY IF current user is the owner
	 * @return bool
	 */
//	public function getIsSubscribed() {
//		return ($this->isSubscribed === NULL) ? $this->isOwner() :  $this->isSubscribed;
//	}

	public function isOwner() {
		return $this->ownerId === go()->getUserId();
	}

	public function isOwned() {
		return !empty($this->ownerId);
	}

	protected function internalValidate()
	{
		if($this->isNew()) {
			if(isset($this->webcalUri) && !$this->fetchWebcalBlob()) {
				$this->setValidationError('webcalUri', 404, 'Could not download webcal file');
			}
		}
		parent::internalValidate();
	}

	protected function internalSave(): bool
	{
		if(!isset($this->defaultColor) && !isset($this->color)) {
			$this->color = $this->defaultColor = self::randomColor($this->name);
		}
		if($this->isNew()) {
			$this->isSubscribed = true; // auto subscribe the creator.
			$this->isVisible = true;
			$this->defaultColor = $this->color;
		} else if($this->ownerId === go()->getUserId() && !empty($this->color)) {
			$this->defaultColor = $this->color;
		}
		if(empty($this->color)) {
			$this->color = $this->defaultColor;
		}
		if(!empty($this->groupId) && empty($this->ownerId)) {
			$this->ownerId = ResourceGroup::find()
				->selectSingleValue('defaultOwnerId')
				->where('id', '=', $this->groupId)
				->single();
		}
		if($this->isModified('defaultAlertsWithTime')) {
			$this->updateEventAlerts($this->defaultAlertsWithTime);
		}
		if($this->isModified('defaultAlertsWithoutTime')) {
			$this->updateEventAlerts($this->defaultAlertsWithoutTime, false);
		}
		if(empty($this->includeInAvailability) && !$this->isPrincipal()) {
			// set sane default
			$this->includeInAvailability = $this->ownerId == go()->getUserId() ? 'all' :
				(empty($this->ownerId) ? 'attending' : 'none');
		}
		$success = parent::internalSave();

		if(!empty($this->webcalBlob)) {
			CalendarEvent::import([$this->webcalBlob], $this->id, 'ignore');
		}

		return $success;
	}

	static function randomColor(string $seed): string
	{
		srand(crc32($seed));
		$nb = rand(0,17);
		return substr('#CDAD00#E74C3C#9B59B6#8E44AD#2980B9#3498DB#1ABC9C#16A085#27AE60#2ECC71#F1C40F#F39C12#E67E22#D35400#95A5A6#34495E#808B96#1652a1',
			($nb*7)+1,6);
	}

	/**
	 * @param DefaultAlert[] $defaultAlerts
	 * @param bool $withTime
	 * @return void
	 * @throws \Exception
	 */
	private function updateEventAlerts($defaultAlerts, $withTime = true) {
		// find all future eventIds
		$ids = CalendarEvent::find(['id'])->filter(['after' => date('Y-m-d\TH:i:s')])
			->andWhere('showWithoutTime', '=', $withTime?0:1)
			->andWhere('useDefaultAlerts', '=', 1)
			->fetchMode(\PDO::FETCH_COLUMN, 0)->all();
		if(empty($ids)) return;

		$type=CalendarEvent::entityType()->getId();
		// delete there alerts
		\go\core\model\Alert::delete((new Query())->where('entityId', 'in', $ids)
			->andWhere('entityTypeId', '=', $type)
			->andWhere('userId', '=', go()->getUserId()));
		// create new ones
		$events = CalendarEvent::find()->where('id','IN', $ids);
		foreach($events as $event) {
			foreach($defaultAlerts as $dalert) {
				$dalert->schedule($event);
			}
		}
	}

	public function getMyRights() {
		$lvl = $this->getPermissionLevel();
		return [
			'mayReadFreeBusy' => $lvl >= 5,
			'mayReadItems' => $lvl >= 10,
			'mayUpdatePrivate' => $lvl >= 20, // per-user properties only
			'mayRSVP' => $lvl >= 25 && !$this->webcalUri, // only own principal status
			'mayWriteOwn' => $lvl >= 30 && !$this->webcalUri, // write only owned events
			'mayWriteAll' => $lvl >= 35 && !$this->webcalUri,
			'mayAdmin' => $lvl >= 50,
			'mayDelete' => $lvl >= 35 && $this->isOwner() && !$this->webcalUri, // calendar itself
		];
	}

	protected function principalAttrs(): array {
		// isPrinicpal() dictates an owner exists
		$resourceGroupOwner = Principal::find(['name','email'])
			->join('calendar_resource_group', 'rg', 'rg.defaultOwnerId = principal.id')
			->where('rg.id', '=', $this->groupId)->single();
		return [
			'name'=>$this->name,
			'description' => $this->description ?? $resourceGroupOwner->name ?? '',
			'timeZone' => $this->timeZone,
			'email' => $resourceGroupOwner->email
		];
	}

	private $webcalBlob;

	private function fetchWebcalBlob() {
		if(!$this->webcalUri) {
			return false;
		}
		$httpClient = new http\Client();
		$tmpFile = \go\core\fs\File::tempFile('ics');
		$httpClient->download($this->webcalUri, $tmpFile);

		if ($tmpFile->isFile()) {
			$blob = Blob::fromTmp($tmpFile);
			if ($blob->save()) {
				$this->webcalBlob = $blob->id;
				return true;
			}
		}
		return false;
	}

	public function importWebcal() {

		if(!$this->webcalUri) {
			return false;
		}
		if(empty($this->webcalBlob)) {
			if(!$this->fetchWebcalBlob()){
				return false;
			}
		}
		// truncate
		CalendarEvent::delete((new Query)->where(['calendarId'=>$this->id]));
		// then insert
		return CalendarEvent::import([$this->webcalBlob], $this->id, 'ignore');
	}

	protected function isPrincipal() : bool
	{
		return isset($this->groupId) && isset($this->ownerId);
	}

	protected static function queryMissingPrincipals(int $offset = 0): Query {
		return self::traitMissingPrincipals($offset)->andWhere('groupId', 'IS NOT', NULL);
	}

	protected function isPrincipalModified() : bool
	{
		return $this->isModified(['name', 'description', 'timeZone', 'ownerId','groupId']);
	}

	protected function principalType(): string {
		return Principal::Resource;
	}
}
