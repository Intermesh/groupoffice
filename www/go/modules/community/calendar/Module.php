<?php
namespace go\modules\community\calendar;

use DateInterval;
use Faker\Generator;
use go\core;
use go\core\cron\GarbageCollection;
use go\core\model\Group;
use go\core\model\Link;
use go\core\model\Module as GoModule;
use go\core\model\Permission;
use go\core\model\User;
use go\core\orm\Property;
use go\core\orm\Query;
use go\core\model\Module as CoreModule;
use go\core\util\DateTime;
use go\modules\community\calendar\model\Calendar;
use go\modules\community\calendar\model\Participant;
use go\modules\community\calendar\model\Preferences;
use go\modules\community\calendar\model\BusyPeriod;
use go\modules\community\calendar\model\CalendarEvent;
use go\modules\community\calendar\model\ICalendarHelper;
use Sabre\VObject\Component\VCalendar;

class Module extends core\Module
{
	public static function getCategory(): string
	{
		return go()->t("Calendar", static::getPackage(), static::getName());
	}
	public function autoInstall(): bool
	{
		return true;
	}

	public function getStatus() : string {
		return self::STATUS_STABLE;
	}

	/**
	 * Default sort order when installing. If null it will be auto generated.
	 * @return int|null
	 */
	public static function getDefaultSortOrder() : ?int{
		return 20;
	}


	public function getAuthor(): string
	{
		return "Intermesh BV <mdhart@intermesh.nl>";
	}

	public function getDocumentationURL(): ?string
	{
		return "https://groupoffice.readthedocs.io/en/latest/using/calendar.html";
	}

	protected function rights(): array
	{
		return [
			'mayChangeCalendars', // allows Calendar/set (hide ui elements that use this)
			'mayChangeCategories', // allows creating global categories for everyone. Personal cats can always be created.
			'mayChangeResources',
			'mayChangeViews'
		];
	}

	public static function getAvailability($id, $start, $end) {
		return BusyPeriod::fetch($id, $start, $end);
	}

	public function defineListeners()
	{
		User::on(Property::EVENT_MAPPING, static::class, 'onMap');
		User::on(User::EVENT_AFTER_SAVE, static::class, 'onUserSave');
		User::on(User::EVENT_ARCHIVE, static::class, 'onUserArchive');
		GarbageCollection::on(GarbageCollection::EVENT_RUN, static::class, 'onGarbageCollection');
	}

	static function onUserSave(User $user, bool $wasNew) {
		if($wasNew) {
			if(core\model\Module::isAvailableFor("community", "calendar", $user->id)) {
				Calendar::createDefault($user);
			}
		}else if (!$user->isNew() && $user->isModified('email')) {
			$pIds = go()->getDbConnection()->selectSingleValue('CONCAT("Calendar:",id)')->from('calendar_calendar')
				->where('groupId', 'IS NOT', null)
				->andWhere('ownerId', '=', $user->id)->all();

			if(!empty($pIds)) {
				go()->getDbConnection()
					->update('core_principal', ['email' => $user->email], ['id' => $pIds])
					->execute();
			}
		}

	}

	static function onUserArchive(User $user, core\util\ArrayObject $aclIds) {
		if (($calendarId = $user->calendarPreferences->defaultCalendarId) && ($calendar = Calendar::findById($calendarId))) {
			$aclIds[] = $calendar->findAclId();
		}
	}

	public static function onGarbageCollection() {

		// Delete event_data that is not in any calendar anymore.
		$stmt = go()->getDbConnection()->delete('calendar_event', (new Query)
			->tableAlias('e')
			->join("calendar_calendar_event", "ce", 'e.eventId = ce.eventId', 'LEFT')
			->where('ce.eventId', 'IS', null)
		);
//		 echo $stmt;
		$stmt->execute();

		go()->debug("Cleaned up " . $stmt->rowCount() ." events without any calendar entries.");
	}
	public static function onMap(core\orm\Mapping $mapping)
	{
		$mapping->addHasOne('calendarPreferences', Preferences::class, ['id' => 'userId'], true);
	}

	// https://uri/path/api/download.php?blob=community/calendar/calendar/1
	public function downloadCalendar($id) {
		$calendar = Calendar::findById($id);
		if($calendar->getPermissionLevel() < 50) {
			throw new core\exception\Forbidden('You need manage permission to export this calendar');
		}
		$this->outputIcs($calendar);
	}

	private function outputIcs(Calendar $calendar) {
		$events = CalendarEvent::find()->where(['calendarId' => $calendar->id]);
		header('Content-Type: text/calendar; charset=UTF-8; component=vcalendar');
		header('Content-Disposition: attachment; filename="'.$calendar->name.'export_'.$calendar->id.'_'.date('Y-m-d').'.ics"');
		$vcalendar = new VCalendar([
			'PRODID' => str_replace('{VERSION}', go()->getVersion(),CalendarEvent::PROD),
			'METHOD' => 'PUBLISH'
		]);
		if($calendar->timeZone) $vcalendar->add("X-WR-TIMEZONE", $calendar->timeZone);
		if($calendar->description) $vcalendar->add("X-WR-CALDESC", $calendar->description);
		foreach($events as $ev) {
			ICalendarHelper::toVObject($ev, $vcalendar);
		}
		echo $vcalendar->serialize();
	}

	// https://uri/path/api/page.php/community/calendar/ics/key
	public function pageIcs($key) {
		// No auth needed but publishKey most be known to read
		$calendar = Calendar::find()->where(['publishKey' => $key])->single();
		if($calendar) {
			$this->outputIcs($calendar);
		} else {
			throw new core\exception\Forbidden("Unauthorized");
		}
	}

	public function downloadIcs($key) {
		$ev = CalendarEvent::findById($key);

		header('Content-Type: text/calendar; charset=UTF-8; component=vevent');
		echo $ev->toVObject();
	}

	public function pagePrint($type,$date) {
		go()->setAuthState(new core\jmap\State());
		$calendarIds = Calendar::find()->selectSingleValue('calendar_calendar.id')
			//->join('calendar_calendar_user', 'cu','cu.userId = '.go()->getUserId().' AND cu.calendarId = t.id')
			->where('caluser.isVisible', '=',1)->andWhere('caluser.isSubscribed','=', true)->all();
		//$calendarIds = json_decode($calendars);
		switch($type) {
			case 'day': $this->printDay(new \DateTime($date), $calendarIds); break;
			case 'days': $this->printWeek($date, $calendarIds, 5);break;
			case 'week' : $this->printWeek($date, $calendarIds, 7);break;
			case 'month' : $this->printMonth(new \DateTime($date), $calendarIds);break;
			//case 'list' : $this->printList(new \DateTime($date), new DateTime($end), $calendarIds);break;
		}
	}

	public function pagePrintList($start, $end) {

		go()->setAuthState(new core\jmap\State());

		$calendarIds = Calendar::find()->selectSingleValue('calendar_calendar.id')
			->where('caluser.isVisible', '=',1)->andWhere('caluser.isSubscribed','=', true)->all();

		$report = new reports\ListView();
		$report->day = new DateTime($start);;
		$report->end = new DateTime($end);
		$report->calendarIds = $calendarIds;
		$report->render();

		$report->Output('calendar_list_'.$start.'_'.$end.'.pdf');
	}

	private function printDay($start, $calendarIds){

		$end = (clone $start)->modify('+1 days');

		$report = new reports\Day();
		$report->day = $start;
		$report->end = $end;
		foreach($calendarIds as $id) {
			$events = CalendarEvent::find()->filter([
				'before'=>$end->format('Y-m-d'),
				'after'=>$start->format('Y-m-d'),
				'inCalendars'=>[$id]
			])->all();


			$report->setEvents($events);
			$report->render();
			$report->calendarName = Calendar::find(['name'])->selectSingleValue('name')->where(['id'=>$id])->single();
		}
		$report->Output('calendar_day_'.$start->format('Y-m-d').'.pdf');
	}

	private function printWeek($date, $calendarIds, $span){

		$report = new reports\Week();

		$date = (new \DateTime($date));
		$dayDiff = (int) $date->format('w') - $report->firstWeekday;

		$start = $date->sub(new DateInterval("P" . $dayDiff . "D"));
		$end = (clone $start)->modify('+'.$span.' days');


		$report->dayCount = $span;
		$report->day = $start;
		$report->end = $end;
		foreach($calendarIds as $id) {
			$events = CalendarEvent::find()->filter([
				'before'=>$end->format('Y-m-d'),
				'after'=>$start->format('Y-m-d'),
				'inCalendars'=>[$id]
			])->all();


			$report->setEvents($events);
			$report->render();
			$report->calendarName = Calendar::find(['name'])->selectSingleValue('name')->where(['id'=>$id])->single();
		}
		$report->Output($report->Output('calendar_week_'.$start->format('Y-m-d').'_'.$end->format('Y-m-d').'.pdf'));
	}

	private function printMonth($date, $calendarIds) {
		$start = (clone $date)->modify('first day of this month');
		$end = (clone $start)->modify('+1 month');

		$report = new reports\Month();
		$report->day = $start;
		$report->end = $end;
		foreach($calendarIds as $id) {

			$events = CalendarEvent::find()->filter([
				'before'=>$end->format('Y-m-d'),
				'after'=>$start->format('Y-m-d'),
				'inCalendars'=>[$id]
			])->all();


			$report->setEvents($events);
			$report->render();
			$report->calendarName = Calendar::find(['name'])->selectSingleValue('name')->where(['id'=>$id])->single();
		}
		$report->Output($report->Output('calendar_month_'.$start->format('Y-m-d').'_'.$end->format('Y-m-d').'.pdf'));
	}



	public function pageInvite($uid,$secret) {

		go()->setAuthState(new core\jmap\State());
		$event = CalendarEvent::find()
			->join('calendar_participant', 'p', 'p.eventId = eventdata.eventId', 'LEFT')
			->where(['p.scheduleSecret' => $secret, 'eventdata.uid'=>$uid])->single();
		if(!$event) {
			throw new core\exception\Forbidden();
		}
		$title = go()->t('Event page', 'community', 'calendar');
		$method = 'PAGE'; // will show participation statusses or other participants
		$url = '';
		foreach($event->participants as $p) {
			if($p->checkSecret($secret)){
				$participant = $p;
			}
		}
		if(!$participant) {
			throw new core\exception\Forbidden();
		}
		if(isset($_GET['reply']) && in_array($_GET['reply'], ['accepted', 'tentative', 'declined'])) {
			$participant->participationStatus = $_GET['reply'];
			if(!$event->save()) {
				throw new \Exception('Could not update participation status');
			}
		}
		$status = [$participant->participationStatus => 'active'];
		require(go()->getEnvironment()->getInstallFolder() . '/views/Extjs3/themes/Paper/pageHeader.php');
		include __DIR__.'/views/imip.php'; // use same html as email because why not
		require(go()->getEnvironment()->getInstallFolder() . '/views/Extjs3/themes/Paper/pageFooter.php');

	}

	protected function beforeInstall(GoModule $model): bool
	{
		// Share module with Internal group
		$model->permissions[Group::ID_INTERNAL] = (new Permission($model))
			->setRights(['mayRead' => true]);

		return parent::beforeInstall($model);
	}

	protected function afterInstall(CoreModule $model): bool {
		cron\ScanEmailForInvites::install("*/5 * * * *");

		Calendar::entityType()->setDefaultAcl([core\model\Group::ID_INTERNAL => core\model\Acl::LEVEL_READ]);

		return parent::afterInstall($model);
	}

//	protected function rights(): array
//	{
//		return [
//			'mayChangeCalendars', // allows AddressBook/set (hide ui elements that use this)
//			'mayExportItems', // Allows users to export contacts
//		];
//	}


	public function demo(Generator $faker)
	{
		$users = User::find(['id', 'displayName', 'email', 'timezone'])->limit(10)->all();
		$userCount = count($users) - 1;

		$locations = ['Online', 'Office', 'Customer', ''];

		foreach($users as $user) {

			$calendar = Calendar::find()->where('name', '=', $user->displayName)->single();
			if(!$calendar) {
				$calendar = Calendar::createFor($user->id);
				$calendar->name = $user->displayName;
				$calendar->timeZone = $user->timezone;
				$calendar->setOwnerId($user->id);
				$calendar->createdBy = $user->id; // This will make the ACL owned by the user too
				$calendar->setAcl([
					core\model\Group::ID_INTERNAL => core\model\Acl::LEVEL_READ
				]);
				if(!$calendar->save()) {
					throw new core\orm\exception\SaveException($calendar);
				}
			}

			for($i = 0; $i < 5; $i++) {

				$time = new core\util\DateTime("-8 days");
				$time->setTime(6,0,0);
				$di = new DateInterval("P" . $faker->numberBetween(1, 14) ."D");
				$time->add($di);

				$event = new CalendarEvent();
				$event->timeZone = $calendar->timeZone;
				$event->start = (clone $time)->add(new DateInterval("PT" . $faker->numberBetween(1, 9) ."H"));
				$event->duration = "PT30M";
				$event->location = $locations[$faker->numberBetween(0, 3)];
				$event->title = $faker->company;
				$event->calendarId = $calendar->id;


				$participant = new Participant($event);
				$participant->email = $user->email;
				$participant->name = $user->displayName;
				$participant->setRoles(["owner" => true, 'attendee'=>true]); //organizer
				$participant->participationStatus = Participant::Accepted;
				$event->participants[$user->id] = $participant;

				$user2 = $users[$faker->numberBetween(0, $userCount)];
				$user3 = $users[$faker->numberBetween(0, $userCount)];

				if($user2->id != $user->id) {
					$participant = new Participant($event);
					$participant->setRoles(['attendee'=>true]); //organizer
					$participant->email = $user2->email;
					$participant->name = $user2->displayName;
					$participant->expectReply = true;
					$event->participants[$user2->id ] = $participant;
				}

				if($user3->id != $user->id) {
					$participant = new Participant($event);
					$participant->setRoles(['attendee'=>true]); //organizer
					$participant->email = $user3->email;
					$participant->name = $user3->displayName;
					$participant->expectReply = true;
					$event->participants[$user3->id] = $participant;
				}
				if(!$event->save()) {
					throw new core\orm\exception\SaveException($event);
				}

				Link::demo($faker, $event);

				echo ".";
			}
		}

	}
}