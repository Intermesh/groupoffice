<?php
namespace go\modules\community\calendar;

use GO\Base\Exception\AccessDenied;
use go\core;
use go\modules\community\calendar\model\CalendarEvent;
use go\modules\community\calendar\model\ICalendarHelper;
use go\modules\community\calendar\model\Settings;

class Module extends core\Module
{

	public function getAuthor(): string
	{
		return "Intermesh BV <mdhart@intermesh.nl>";
	}

	public static function getTitle(): string
	{
		return 'Calendar GOUI';
	}

	public function getSettings() {
		return Settings::get();
	}

	protected function rights(): array
	{
		return [
			'mayChangeCalendars', // allows Calendar/set (hide ui elements that use this)
			'mayChangeCategories', // allows creating global  categories for everyone. Personal cats can always be created.
			'mayChangeResources',
		];
	}

	public function downloadIcs($key) {
		$ev = CalendarEvent::findById($key);

		header('Content-Type: text/calendar; charset=UTF-8; component=vevent');
		echo ICalendarHelper::toVObject($ev)->serialize();
	}

	public function pagePrint($type,$date, $calendars) {
		$calendarIds = json_decode($calendars);
		switch($type) {
			case 'week' : $this->printWeek($date, $calendarIds);
		}
	}

	private function printWeek($date, $calendarIds){

		$d = new \DateTime($date);
		$d->modify('last Monday');
		$end = (clone $d)->modify('+7 days');

		$report = new reports\Week();
		foreach($calendarIds as $id) {
			$events = CalendarEvent::find()->filter([
				'before'=>$end->format('Y-m-d'),
				'after'=>$d->format('Y-m-d')
			])->all();

			$report->day = $d;
			$report->setEvents($events);
			$report->render($date);
			$report->calendarName = model\Calendar::find(['name'])->selectSingleValue('name')->where(['id'=>$id])->single();
		}
		$report->Output('week.pdf');
	}



	public function pageInvite($uid,$secret) {

		go()->setAuthState(new core\jmap\State());
		$event = CalendarEvent::find()
			->join('calendar_participant', 'p', 'p.eventId = eventdata.eventId', 'LEFT')
			->where(['p.scheduleSecret' => $secret, 'eventdata.uid'=>$uid])->single();
		if(!$event) {
			throw new AccessDenied();
		}
		$title = go()->t('Event page');
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

//	protected function rights(): array
//	{
//		return [
//			'mayChangeCalendars', // allows AddressBook/set (hide ui elements that use this)
//			'mayExportItems', // Allows users to export contacts
//		];
//	}
}