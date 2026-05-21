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

		$this->SetY(6);
		$x = $this->GetX();
		$y = $this->GetY();
		$leftWidth = 140;
		$rightWidth = $this->getPageWidth() - $this->lMargin - $this->rMargin - $leftWidth;
		$this->MultiCell($leftWidth, 5, implode(', ', array_map(fn($c) => $c->name, $this->calendars)), 0, 'L');									// Print the name of the calendars that are printed in the view.

		$range = $this->day->toUserFormat();

		$end = (clone $this->end)->sub(new \DateInterval("P1D"))->toUserFormat();
		$this->SetXY($x + $leftWidth, $y);

		if($range != $end) {
			$range .= " - ". $end;
			$this->Cell($rightWidth, 5, $range, 0, 0, 'R');
		}
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

		$this->SetMargins(10,20,10);
		$this->setAutoPageBreak(true, 20);
		$this->AddPage();

		$this->drawItems();

	}

	protected function drawItems() {

		$fullDays = go()->t('full_days');

		$curDate = null;
		$this->SetCellHeightRatio(1.1);
		foreach($this->events as $event) {
			$start = $event->start();
			$date = $start->format('Y-m-d');
			if($curDate !== $date) {
//				$this->WriteHtml('<h2>'.$fullDays[$start->format('w')] . ', ' . $start->format(go()->getAuthState()->getUser()->dateFormat).'</h2>');
//				$curDate = $date;
				$this->Ln(2);
				$this->SetFont(null, 'B', 12);

				$header = $fullDays[$start->format('w')] . ', ' .
					$start->format(go()->getAuthState()->getUser()->dateFormat);

				$this->Cell(0, 8, $header, 0, 1);

				$this->SetFont(null, '', 9);
				$this->Ln(2);

				$curDate = $date;
			}

			$this->drawEventBlock($event);
			//$this->WriteHtml($this->quickText($event), false);

		}
	}

	private function drawEventBlock(model\CalendarEvent $event)
	{
		$userTz = go()->getAuthState()->getUser()->timezone;

		$calendar = $this->calendars[$event->calendarId] ?? null;

		$gutter = 4; // left space for color bar
		$barHeight = 12;

		$bottomLimit = $this->getPageHeight() - $this->getBreakMargin();
		if ($this->GetY() + $barHeight > $bottomLimit) {
			$this->AddPage();
		}

		$x = $this->GetX() +4;
		$y = $this->GetY();

		// --- COLOR BAR ---
		if ($calendar) {
			list($r, $g, $b) = sscanf($calendar['color'], "%02x%02x%02x");
			$this->SetFillColor($r, $g, $b);
			$this->Rect($x-3, $y, 2.5, $barHeight, 'F'); // vertical bar
		}

		// --- TITLE ---
		$this->SetX($x);
		$this->SetFont(null, 'B', 11);
		$this->SetTextColor(0, 0, 0);
		$this->MultiCell(0, 4, $event->title, 0, 'L');

		// --- DATE/TIME LINE ---
		$this->SetX($x);
		$timeParts = $event->humanReadableDate($userTz);
		$this->SetFont(null, '', 11);
		$this->Cell(0, 4, implode(' ', $timeParts), 0, 1);

		// --- RECURRENCE ---
		if ($event->isRecurring()) {
			$this->SetFont(null, '', 9);
			$this->SetTextColor(80, 80, 80);
			$this->SetX($x);
			$this->MultiCell(0, 4, model\RecurrenceRule::humanReadable($event), 0, 'L');
		}

		// --- LOCATION ---
		if (!empty($event->location)) {
			$this->SetX($x);
			$this->SetTextColor(80, 80, 80);
			$this->Cell(0, 5, go()->t('Location') . ': ' . $event->location, 0, 1);
		}
		$this->SetTextColor(0, 0, 0);
		// --- DESCRIPTION ---
		if (!empty($event->description)) {
			$this->SetFont(null, '', 8);
			$desc = substr(strip_tags(nl2br($event->description)), 0, 512);
			$this->SetX($x);
			$this->MultiCell(0, 4, $desc, 0, 'L');
			$this->SetFont(null, '', 9);
		}

		// separator
		$this->Ln(2);
		$this->Cell(0, 0, '', 'T', 1);
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
		$lines[] =  implode(' ',$event->humanReadableDate(go()->getAuthState()->getUser()->timezone));

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