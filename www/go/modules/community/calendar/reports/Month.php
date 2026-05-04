<?php

namespace go\modules\community\calendar\reports;

use go\modules\community\calendar\model\Category;

/**
 * Copyright Intermesh
 *
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 *
 * If you have questions write an e-mail to info@intermesh.nl
 *
 * @copyright Copyright Intermesh
 * @version $Id: Month.php 21644 2017-11-07 13:08:07Z mschering $
 * @author Michael de Hart <mdhart@intermesh.nl>
 */
class Month extends Calendar {

	protected $events = array();
	public $calendars = [];
	private $categories = [];
	private $right;
	
	function normal() {
		parent::normal();
		$this->leftMargin = 10;
		$this->footerY = 15;
		$this->SetMargins($this->leftMargin, 41);
		$this->setPageUnit('mm');
		$this->SetDrawColor(0,0,0);
		$this->SetAutoPageBreak(true, 10);
		
	}
	
	public function Header() {
		//A4 = 21 x 29.7
		$this->setXY(12,12);
		
		$this->right = $this->getPageWidth()-$this->leftMargin*2;
		
		$this->Rect(10, 10, $this->right, 25,'DF', array(), array(240));
		$this->SetFont(null, 'B',$this->fSizeLarge);
		$this->Cell(100, 12, $this->months_long[$this->day->format('n')].$this->day->format(' Y'), 0, 1);

		$this->drawCalendar($this->day, $this->right-100, 11, 45, 22);
		$this->drawCalendar((clone $this->day)->modify('next month'), $this->right-40, 11, 45, 22);

//		$this->drawCalendar($this->day, $this->right-100, 11, 45, 22);
//		$this->drawCalendar($this->day+(32*24*3600), $this->right-40, 11, 45, 22);
	}

	public function render() { //$events
		//$this->events = $this->orderEvents($events);
		$this->AddPage('L');
		$this->SetFont(null,'',$this->fSizeSmall);
		$categories = Category::find()->filter(['operator'=>'OR', 'conditions'=>[
			['inCalendars'=>array_keys($this->calendars)],
			['mine'=>true]
		]])->all();
		$this->categories = array_column($categories, null, 'id');

		$this->drawEventCalendar();
	}

	protected function add($key, $instance) {
		$itr = clone $instance->start();

		$end = min($this->end, $instance->end());
		while($itr < $end) {
			$this->events[$itr->format('Ymd')][$key] = $instance;
			$itr = $itr->modify('+1 day');
		}
	}
	
	/**
	 * get week label
	 * @param \DateTime $date start of week
	 */
	private function getWeekLabel($firstDay) {
		$lastDay = (clone $firstDay)->modify('+6 days');
		$fmonth = ($firstDay->format('M')==$lastDay->format('M'))?'':' '.$this->months_short[$firstDay->format('n')];
		return $firstDay->format('j').$fmonth . ' - ' . $lastDay->format('j').' '.$this->months_short[$lastDay->format('n')];
	}
	
	private function getWeeksInMonth() {
		return ceil($this->day->diff($this->end)->days/7);
	}

	private function getAmountOfDays() {
		return $this->day->diff($this->end)->days;
	}
	
	public function drawEventCalendar() {
		
		$w=$this->right-7;
		$h=$this->getPageHeight()-$this->headerHeight-16;
		$days = min($this->getAmountOfDays(),7);

		$this->SetY(36);
		$this->SetFont(null,'',7.5);
		$this->Cell(7);

		$startDay = (int)$this->day->format('w');
		for($d=$startDay;$d < $startDay+$days; $d++) {
			// day naem
			$this->Cell($w/$days,6,go()->t("full_days")[$d%7], $d!=0,0,'C');
		}
		$this->Ln();

		//$day='';
		$weeks = $this->getWeeksInMonth();
		$dateh = 5;
		$rowh = $h/$weeks;
		$colw = $w/$days;
		$day = clone $this->day;
		for($r = 0; $r < $weeks; $r++){
			//Draw vertical dates
			$this->StartTransform();
				$this->Rotate(90);
				$this->Translate(-($rowh-9), 0);
				$this->Cell(7,7, $this->getWeekLabel($day),0,0,'C');
				$this->Rotate(-90);
			$this->StopTransform();
			
			$this->setCellPaddings(1,1,0,0);
			for($c=0;$c<$days;$c++){ //toggle weekday
				$coord = [$this->GetX(), $this->GetY()];

				$this->SetFont(null, 'B', $this->fSizeMedium-2);
				$this->Cell($colw,5,$day->format($day->format('j') == 1 ? 'j M' : 'j'), 1,0,'L',false,'',0,false,'T','T');
				$this->SetFont(null, '', $this->fSizeSmall);

				$events = [];
				$this->SetFillColor($this->day->format('M')===$day->format('M') ? 255: 240);

				if(isset($this->events[$day->format('Ymd')])) {
					ksort($this->events[$day->format('Ymd')]);
					$events = $this->events[$day->format('Ymd')];
				}

				$this->DayCell($events, $colw, $rowh-$dateh, $coord[0], $coord[1]+$dateh);
				
				$this->SetXY($coord[0]+$colw,$coord[1]);
				$day->modify('+1 day');
			}
			$this->SetXY($this->leftMargin,$this->GetY()+$rowh);
			
		}
		$this->SetXY(10,36);
		$this->Cell($colw+7, $rowh*$weeks+6, '',1);

	}
	
	protected function DayCell($events, $width, $h, $x=null, $y=null) {
		$tz = go()->getAuthState()->getUser()->timezone;
		if($x===null)
			$x=$this->GetX();
		if($y===null)
			$y=$this->GetY();
		$this->SetCellHeightRatio(1.45);
		$this->Rect($x, $y, $width, $h, 'DF');
		$this->StartTransform(); //will clip text in the Rectangle on the next line
		$this->Rect($x, $y, $width-1, $h, 'CEO');
		$currentY = $y+0.3;
		foreach ($events as $event) {

			list($r, $g, $b) = sscanf($this->calendars[$event->calendarId]['color'], "%02x%02x%02x");

			$pad = 0;
			if(!$event->showWithoutTime) {
				$pad =1.5;
				$this->SetFillColor($r, $g, $b);
				$this->Rect($x + 1, $currentY + 1, 1, 3.2, 'F');
			} else {
				$this->SetTextColor($r, $g, $b);
			}
			$this->SetXY($x + $pad, $currentY);

			$title = substr($event->title, 0, 25);

			if (!$event->showWithoutTime) {
				$start = $event->start(false, $tz)->format('G:i');
				$timeWidth = $this->GetStringWidth($start);
				$this->SetTextColor(0, 0, 0);
				$this->Cell($width - $timeWidth - 4, 4, $title, 0, 0);
				$catX = $this->GetX();

				$this->SetFillColor(255, 255, 255);
				$this->Rect($x + $width - $timeWidth - 1.5, $currentY+1, $timeWidth + 1.5, 4, 'F');

				$this->SetTextColor(150, 150, 150);
				$this->SetXY($x + $width - $timeWidth-1, $currentY);
				$this->Cell($timeWidth, 4, $start, 0, 0, 'R');


			} else {
				$this->SetTextColor($r, $g, $b);
				$this->Cell($width+10, 4, $title, 0, 0);
				$catX = $this->GetX() - 11;
			}
			foreach ($event->categoryIds as $i => $id) {

				if(!isset($this->categories[$id])) continue;
				$cat = $this->categories[$id];
				//var_dump($cat->color);
				//...sscanf($cat->color, "%02x%02x%02x")
				list($r, $g, $b) = sscanf($cat->color, "%02x%02x%02x");
				$this->SetFillColor($r, $g, $b);
				$this->RoundedRect($catX - (($i+1)*2.5), $currentY+1, 2, 3.2, .3,'1111','DF', []);
			}

			$this->SetTextColor(0, 0, 0);

			$currentY += 4;
		}

		//$this->writeHTMLCell($width+100,$h,$x,$y, mb_convert_encoding($text,'UTF-8', 'UTF-8'), 0,0,false,true,'L');
		$this->StopTransform();
		$hasMore = $currentY - $y > $h;
		if($hasMore) {
			$this->SetXY($x+($width-4),$y+($h-4));
			$this->SetCellHeightRatio(1);
			$this->Image(self::IMG_PATH . 'arrow_down.png', null,null, 3, 3, 'PNG');
			//$this->Cell(3, 2, html_entity_decode("&#x25BC;"), 0, 0, 'R', true);
		}
		$this->SetCellHeightRatio(1.2);
	}

}
