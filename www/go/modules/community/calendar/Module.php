<?php
namespace go\modules\community\calendar;

use GO\Base\Exception\AccessDenied;
use go\core;
use go\core\cron\GarbageCollection;
use go\core\model\User;
use go\core\orm\Property;
use go\core\orm\Query;
use go\core\model\Module as CoreModule;
use go\modules\community\calendar\cron;
use go\modules\community\calendar\model\Calendar;
use go\modules\community\calendar\model\Preferences;
use go\modules\community\calendar\model\BusyPeriod;
use go\modules\community\calendar\model\CalendarEvent;
use go\modules\community\calendar\model\ICalendarHelper;
use go\modules\community\calendar\model\Settings;
use Sabre\VObject\Component\VCalendar;

class Module extends core\Module
{

	public function autoInstall(): bool
	{
		return true;
	}

	public function getAuthor(): string
	{
		return "Intermesh BV <mdhart@intermesh.nl>";
	}

	public function getSettings() {
		return Settings::get();
	}

	protected function rights(): array
	{
		return [
			'mayChangeCalendars', // allows Calendar/set (hide ui elements that use this)
			'mayChangeCategories', // allows creating global categories for everyone. Personal cats can always be created.
			'mayChangeResources',
		];
	}

	public static function getAvailability($id, $start, $end) {
		return BusyPeriod::fetch($id, $start, $end);
	}

	public function defineListeners()
	{
		User::on(Property::EVENT_MAPPING, static::class, 'onMap');
		User::on(User::EVENT_SAVE, static::class, 'onUserSave');
		User::on(User::EVENT_ARCHIVE, static::class, 'onUserArchive');
		GarbageCollection::on(GarbageCollection::EVENT_RUN, static::class, 'onGarbageCollection');
	}

	static function onUserSave(User $user) {
		if (!$user->isNew() && $user->isModified('email')) {
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

	public function downloadCalendar($id) {
		$calendar = Calendar::findById($id);
		if($calendar->getPermissionLevel() < 50) {
			throw new core\exception\Forbidden('You need manage permission to export this calendar');
		}
		$events = CalendarEvent::find()->where(['calendarId' => $id]);
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
		}
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
		$report->Output('day.pdf');
	}

	private function printWeek($date, $calendarIds, $span){

		$start = (new \DateTime($date))->modify('Monday this week');
		$end = (clone $start)->modify('+'.$span.' days');

		$report = new reports\Week();
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
		$report->Output('week.pdf');
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
		$report->Output('month.pdf');
	}



	public function pageInvite($uid,$secret) {

		go()->setAuthState(new core\jmap\State());
		$event = CalendarEvent::find()
			->join('calendar_participant', 'p', 'p.eventId = eventdata.eventId', 'LEFT')
			->where(['p.scheduleSecret' => $secret, 'eventdata.uid'=>$uid])->single();
		if(!$event) {
			throw new AccessDenied();
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
			throw new AccessDenied();
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
}