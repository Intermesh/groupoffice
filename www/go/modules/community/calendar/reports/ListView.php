<?php

namespace go\modules\community\calendar\reports;

use go\modules\community\calendar\model;

class ListView extends Calendar {

	/** @var model\Calendar[] the selected calendars  */
	protected $calendars = [];
	public $calendarIds = [];


	const statusIcons = [
		'accepted' => ['check_circle', 'Accepted', 'green'],
		'tentative' => ['help', 'Maybe', 'orange'],
		'declined' => ['block', 'Declined', 'red'],
		'needs-action' => ['schedule', 'Awaiting reply', 'orange']
	];

	public function Header() {

		$this->Write(12, implode(' & ', array_map(fn($c) => $c->name, $this->calendars)));									// Print the name of the calendars that are printed in the view.

		$range = $this->day->format('d-m-Y') . ' - ' . $this->end->format('d-m-Y');
		$this->Cell($this->getPageWidth() - $this->getX() - $this->rMargin, 12, $range, 0, 0, 'R');

	}

	public function render() {

		$calendars = model\Calendar::find()->where(['id'=>$this->calendarIds]);
		foreach($calendars as $cal) {
			$this->calendars[$cal->id] = $cal;
		}

		$this->setEvents(model\CalendarEvent::find()->filter([
			'inCalendars' => $this->calendarIds,
			'after'=>$this->day->format('Y-m-d'),
			'before'=>$this->end->add(new \DateInterval("P1D"))->format('Y-m-d')
		]));

		$this->SetMargins(10,10,10);
		$this->setAutoPageBreak(true, 25);
		$this->AddPage();

		$this->drawItems();

	}

	protected function drawItems() {

		$fullDays = go()->t('full_days');

		$curDate = null;
		foreach($this->events as $event) {
			$date = $event->utcStart->format('Y-m-d');
			if($curDate !== $date) {
				$this->WriteHtml('<h2>'.$fullDays[$event->utcStart->format('w')] . ', ' . $event->utcStart->format(go()->getAuthState()->getUser()->dateFormat).'</h2>');
				$curDate = $date;
			}
			$this->WriteHtml($this->quickText($event), false);

		}
	}

	private function quickText(model\CalendarEvent $event): string {


		$calendar = $this->calendars[$event->calendarId];
		$cal = '';
		if ($calendar) {
			$cal = '<span style="color:#' . $calendar->color . ';">' . $calendar->name . '</span>';
		}

		$lines = [];
		$lines[] = '<b>' . $event->title . '</b><br>' . $cal;

		// Assuming humanReadableDate() returns an array of strings
		$lines[] =  implode(' ',$event->humanReadableDate());

		if ($event->isRecurring()) {
			$lines[] = model\RecurrenceRule::humanReadable($event);
		}

//		if (!empty($event->participants)) {
//			$lines[] = '<hr>' . go()->t('Participants');
//			foreach ($event->participants as $p) {
//				$status = $p->participationStatus ?? 'needs-action';
//				$icon = self::statusIcons[$status]; // Assuming statusIcons is a function returning an array like [$iconHtml, $title, $cssClass]
//				$i = '<i class="icon ' . go()->t($icon[2]) . '" title="' . $icon[1] . '">' . $icon[0] . '</i>';
//				$lines[] = $i . ' ' . ($p->name ?? $p->email);
//			}
//		}

		if (!empty($event->location)) {
			$lines[] = go()->t('Location') . ': ' . $event->location;
		}

		if (!empty($event->description)) {
			$lines[] = '<p style="max-width:360px;">' . substr(nl2br($event->description),0,512) . '</p>';
		}

		return implode('<br>', $lines).'<br><hr>';
	}
}