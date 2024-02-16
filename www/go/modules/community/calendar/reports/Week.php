<?php

namespace go\modules\community\calendar\reports;


use go\core\Module;
use go\modules\community\calendar\model\CalendarEvent;
use go\modules\community\calendar\model\RecurrenceRule;

/**
 * Copyright Intermesh
 *
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 *
 * If you have questions write an e-mail to info@intermesh.nl
 *
 * @copyright Copyright Intermesh
 * @version $Id: Week.php 21220 2017-06-12 09:43:55Z mschering $
 * @author Michael de Hart <mdhart@intermesh.nl>
 */
class Week extends Calendar {

	protected $notes = array('Note1', 'Note2', 'Note3');
	protected $tasks = array('Task1', 'Task2', 'Task3');
	
	public $dayCount = 7; //the amount of days to render in week view
	private $continues = [];

	protected $iter;

	protected $eventOptions = []; // pos, start, end, lanes

	public function Header() {
		//A4 = 21 x 29.7
		$this->setXY(12,12);
		
		$width = $this->getPageWidth()-$this->leftMargin*2;
		
		$this->Rect($this->leftMargin, 10, $width, 30,'DF', $this->thickBorder, $this->greyFill);
		$this->SetFont(null, 'B',$this->fSizeLarge);
		$this->Cell(100, 12, $this->day->format('d. ').$this->months_long[$this->day->format('n')].$this->day->format(' Y').' -', 0, 1);
		
		//$this->SetLin
		
		$this->setX(12);
		$this->SetFont(null, 'B', $this->fSizeLarge);
		//$end = $this->day+($this->dayCount-1)*24*3600;
		$this->Cell(100, 12, $this->end->format('d. ').$this->months_long[$this->end->format('n')].$this->end->format('Y'), 0, 1);
		
		$this->drawCalendar($this->day, 110, 12);
		$this->drawCalendar((clone $this->day)->modify('next month'), 160, 12);
		
		$this->setXY($this->leftMargin,41);
		
	}

	public function setEvents($events) {
		parent::setEvents($events);
		$this->events = array_values($this->events);
	}
	
	public function Footer() {
		$bottom = 238;
		$page = $this->getPageWidth()-(2*$this->leftMargin);
		
		$this->Rect($this->leftMargin, 41, $this->timeCol, $bottom-(3*$this->rowHeight),'D', $this->thickBorder);
		$this->Rect($this->leftMargin+$this->timeCol, 41, $page-$this->timeCol, $bottom-(3*$this->rowHeight),'D', $this->thickBorder);
		$this->Rect($this->leftMargin, $bottom+$this->headerHeight-(3*$this->rowHeight), $page, 3*$this->rowHeight,'D', $this->thickBorder);
		parent::Footer();
	}

	protected function add($key, $instance) {
		if($instance->showWithoutTime) {
			$this->early[$key] = $instance;
		} else if($this->getMinuteOfDay($instance->utcEnd) > 1140) { // 19:00
			$this->late[$key] = $instance;
		} else if($this->getMinuteOfDay($instance->utcStart) < 420) { // 7:00
			$this->early[$key] = $instance;
		} else {
			$this->events[$key] = $instance;
		}

	}

	public function render() { // $week
		//$this->events = $events;
		$this->AddPage();
		$this->SetFont(null,'',$this->fSizeSmall);
		
//		for($w=0;$w<$this->dayCount;$w++) {
//			$this->calculateOverlap($w);
//		}
		
		$this->drawEventsBackground();
		$this->iter = 0;
		for($w=0;$w<$this->dayCount;$w++) {
			$this->drawDay($w);
		}
	}
	
	public function drawEventsBackground() {

		$left = $this->timeCol; // width of cell with time
		$width = $this->getPageWidth()-(2*$this->leftMargin)-$this->timeCol;
		$width = $width/$this->dayCount;
		$minus = 2;
		
		//Render top items
		$this->Cell($left, ($this->rowHeight*3)-$minus, '', 1, 0, '', true);
		for($w=0;$w<$this->dayCount;$w++) {
			$day = (clone $this->day)->modify('+'.$w. ' days');
			$x=$this->GetX();
			$this->SetFont(null, 'B');
			$this->Cell($width, $this->rowHeight-$minus, $day->format('d'), 1, 0);
			$this->SetFont(null, '', $this->font_size-0.5);
			$this->SetX($x+1);
			$this->Cell($width, $this->rowHeight-$minus, $this->days_long[$day->format('N')-1], 0,0,'C');
			$this->SetFont(null, '', $this->font_size);
			$this->SetX($x);
			$this->Cell($width, ($this->rowHeight*3)-$minus, '', 1, 0);
		}
		$this->Ln();

		//Render rows
		for($i=0;$i<24;$i++){
			if($i%2==0) {
				$hour = ($i/2+7);
				$this->drawTime($hour);
			} else
				$this->SetX($this->leftMargin+$this->timeCol);
			
			for($w=0;$w<$this->dayCount;$w++) {
				$this->Cell($width, $this->rowHeight, '', 1, 0);
			}
			
			$this->Ln();
		}
		
		//Render bottom 3 rows
		for($i=0;$i<3;$i++) {
			$this->Cell($left, $this->rowHeight, '', 1, 0, '', true);
			for($w=0;$w<$this->dayCount;$w++)
				$this->Cell($width, $this->rowHeight, '', 1, 0);
			$this->Ln();
		}
		
	}

	private function drawEarly($w, $width, $left) {
		$this->SetDrawColorArray($this->eventLineColor);
		$x=$w*$width+20;
		$i=0;
		foreach($this->early as $e) {
			if($i>2) { //stop after 3 full day events (todo draw a triangle)
				$this->Image(self::IMG_PATH.'arrow_down.png',$left+($w+1)*$width-4, 48+5*$i, 3,3, 'PNG');
				break;
			}
			$this->SetXY($x, 48+5*$i);
			$this->EventCell($e->title, $width, 5);
			//$this->Cell($colWidth, 5 , $event->name, 1, 1, 'L', true);
			$i++;
		}
	}

	private function drawLate($w, $width, $left) {
		$this->SetDrawColorArray($this->lineStyle['color']);
		$x = $left+($w+1)*$width- 4;
		$this->SetXY($x ,$this->headerHeight+209);
$i=0;
		foreach($this->late as $event) {
			if($i>2) { //stop after 3 full day events (todo draw a triangle)
				$this->Image(self::IMG_PATH.'arrow_down.png',$left+($w+1)*$width-4, 252+$this->rowHeight*$i, 3,3, 'PNG');
				break;
			}
			$this->SetXY($left+$w*$width ,255+$this->rowHeight*$i);
			$this->EventCell($event->utcStart->format('G:i') .' - '. $event->utcEnd->format('G:i') .' '. $event->title, $width, $this->rowHeight);
			$i++;
		}
	}

	protected function drawDay($w)
	{
		$this->currentDay = $w;
		$dayStart = (clone $this->day)->modify('+' . $w . ' days');
		$stillContinuing = [];
		$end = clone $dayStart;
		$end->add(new \DateInterval('P1D'));

		$offsetLeft = $this->leftMargin + $this->timeCol;
		$width = $this->getPageWidth() - (2 * $this->leftMargin) - $this->timeCol;
		$width = $width / $this->dayCount;

		$this->drawEarly($w, $width, $offsetLeft);

		while (isset($this->events[$this->iter])) {
			$e = $this->events[$this->iter];
			if($e->utcStart >= $end) {
				break;
			}
			$this->iter++;
			if ($e->utcEnd > $dayStart) {
				$this->continues[] = $e;
			}
		}

		if (!empty($this->continues)) {
			$this->calculateOverlap($this->continues, $dayStart);
		}
		while ($e = array_shift($this->continues)) {
			$this->drawEvent($w*$width, $width, $e);
			if ($e->end() > $end) {
				$stillContinuing[] = $e; // push it back for next week
			}
		}
		$this->continues = $stillContinuing;


		$this->drawLate($w, $width, $offsetLeft);
	}

	protected function getMinuteOfDay($date) {
		return intval($date->format('G'))*60 + intval($date->format('i'));
	}

	protected function calculateOverlap(array $events, \DateTime $dayStart) {
		$highestEnd = 0;
		$blockStart = 0;
		$blockLanes = 1;
		$first = 0;
		foreach($events as $i => $event) {
			if($event->showWithoutTime) {
				continue;
			}
			$first = $i;
			break;
		}
		for ($i = $first; $i < count($events); $i++) {
			$this->eventOptions[$events[$i]->id.'-'.$this->currentDay] = ['start'=>$events[$i]->start(), 'end'=>$events[$i]->end(), 'lanes'=>1]; // current item
			$a =& $this->eventOptions[$events[$i]->id.'-'.$this->currentDay];
			$a['startM'] = $a['start']->format('Ymd') < $dayStart->format('Ymd') ? 0 : $this->getMinuteOfDay($a['start']);
			$a['endM'] = $a['end']->format('Ymd') > $dayStart->format('Ymd') ? 1440 : $this->getMinuteOfDay($a['end']);
			$a['pos'] = 0;

			if ($a['startM'] >= $highestEnd) { // end collision block
				$blockStart = $i;
				$blockLanes = 1;
			}

			for ($j = $blockStart; $j < $i; $j++) {
				$b = $this->eventOptions[$events[$j]->id.'-'.$this->currentDay]; // already positioned item

				if ($a['endM'] > $b['startM'] && $a['startM'] < $b['endM'] && $a['pos'] === $b['pos']) { // collides
					$a['pos']++;
					$blockLanes = max($blockLanes, $a['pos'] + 1);
					$j = $blockStart - 1; // restart from blockstart
				}

				$b['lanes'] = $blockLanes;
			}

			$a['lanes'] = $blockLanes;
			$highestEnd = max($highestEnd, $a['endM']);
		}
	}
	
}
