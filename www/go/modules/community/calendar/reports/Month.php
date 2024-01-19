<?php

namespace go\modules\community\calendar\reports;

use go\modules\community\calendar\model\CalendarEvent;

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
	
	/**
	 * @var CalendarEvent[]
	 */
	protected $events = array();
	
	private $right;
	
	protected function init() {
		parent::init();
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
		$this->Cell(100, 12, $this->months_long[date('n',$this->day)].date(' Y',$this->day), 0, 1);
		
		$this->drawCalendar($this->day, $this->right-100, 11, 45, 22);
		$this->drawCalendar($this->day+(32*24*3600), $this->right-40, 11, 45, 22);
	}
	
	/**
	 * 
	 * @param CalendarEvent[] $events
	 */
	public function render() { //$events
		$this->events = $this->orderEvents($events);
		$this->AddPage('L');
		$this->SetFont(null,'',$this->fSizeSmall);

		$this->drawEventCalendar();
	}
	
	/**
	 * The first date that is rendered in this calendar print
	 * @return int UTC unixtimestamp
	 */
	private function getStartTime() {
		$firstDayOfMonth = strtotime(date('Y-m-01', $this->day));
		$wd = date('N',$firstDayOfMonth)-1;
		return $firstDayOfMonth-$wd*24*3600;
	}
	
	/**
	 * get week label
	 * @param timestamp $date start of week
	 */
	private function getWeekLabel($firstDay) {
		$lastDay = $firstDay+6*24*3600;
		$fmonth = (date('M',$firstDay)==date('M',$lastDay))?'':' '.$this->months_short[date('n',$firstDay)];
		return date('j',$firstDay).$fmonth . ' - ' . date('j ',$lastDay).$this->months_short[date('n',$lastDay)];
	}
	
	private function getWeeksInMonth() {
		$firstDayOfMonth = strtotime(date('Y-m-01', $this->day));
		$firstWeekDay = date('N',$firstDayOfMonth)-1;
		$daysInMonth = date('t', $this->day);
		return ceil(($daysInMonth+$firstWeekDay)/7);
	}
	
	public function drawEventCalendar() {
		
		$w=$this->right-7;
		$h=$this->getPageHeight()-$this->headerHeight-16;
		
		$this->SetY(36);
		$this->SetFont(null,'',7.5);
		$this->Cell(7);
		for($d=0;$d<7;$d++) {
			$this->Cell($w/7,6,$this->days_long[$d], $d!=0,0,'C');
		}
		$this->Ln();
		
		$day='';
		$weeks = $this->getWeeksInMonth();
		$dateh = 5;
		$rowh = $h/$weeks;
		$colw = $w/7;
		$date = $this->getStartTime();
		$month = date('M',$date);
		for($r = 0; $r < $weeks; $r++){
			//Draw vertical dates
			$this->StartTransform();
				$this->Rotate(90);
				$this->Translate(-($rowh-9), 0);
				$this->Cell(7,7, $this->getWeekLabel($date),0,0,'C');
				$this->Rotate(-90);
			$this->StopTransform();
			
			$this->setCellPaddings(1,1,0,0);
			for($c=0;$c<7;$c++){ //toggle weekday
				$coord = array($this->GetX(), $this->GetY());

				$this->SetFont(null, 'B', $this->fSizeMedium-2);
				$this->Cell($colw,5,date('j ',$date).$month, 1,0,'L',false,'',0,false,'T','T');
				$month='';
				$more=false;
				$this->SetFont(null, '', $this->fSizeSmall);
				if(date('M',$date)!=date('M',Date::date_add($date, 1))) {
					$month = date('M',Date::date_add($date, 1));
				}
				$events = '';
				if(date('M',$this->day)==date('M',$date)) {
					$amount = 0;
					if(isset($this->events[$date]['fd'])) {
							
						foreach($this->events[$date]['fd'] as $i => $event) { //full day
							$events.='<br><font color="blue">'.substr($event->name,0,25).'</font>';
							$amount++;
						}
					}
					$eventsMerge = $this->allEvents($date);
					if(!empty($eventsMerge)) {
						foreach($eventsMerge as $i => $event) { //part day
							if($i+$amount>5) {
								$more=true;
								break;
							}
							$events.="<br>".date('G:i',$event->start_time) .'-'. date('G:i',$event->end_time) .' '. substr($event->name,0,25);
						}
					}
						
					$this->SetFillColor(255);
				} else {
					$this->SetFillColor(240);
				}
				//$this->SetXY($coord[0], $coord[1]+$dateh);
				
				//\GO::debug($events);
				//$this->MultiCell($colw,$rowh-$dateh,$events, 1,'L',true,0,'','',true,0,true,true,$rowh-$dateh,'T',true);
				//$this->writeHTMLCell($colw,$rowh-$dateh,$this->GetX(),$this->GetY(),  mb_convert_encoding($events,'UTF-8', 'UTF-8'), 1,0,true);
				$this->DayCell($events, $colw, $rowh-$dateh, $coord[0], $coord[1]+$dateh,$more);
				
				$this->SetXY($coord[0]+$colw,$coord[1]);
				$date = \GO\Base\Util\Date::date_add($date, 1);
			}
			$this->SetXY($this->leftMargin,$this->GetY()+$rowh);
			
		}
		$this->SetXY(10,36);
		$this->Cell($colw+7, $rowh*$weeks+6, '',1);

	}
	
	private function allEvents($date) {
		$allEvents = array();
		if(isset($this->events[$date]['early']))
			$allEvents = array_merge($allEvents, $this->events[$date]['early']);
		if(isset($this->events[$date]['part']))
			$allEvents = array_merge($allEvents, $this->events[$date]['part']);
		if(isset($this->events[$date]['late']))
			$allEvents = array_merge($allEvents, $this->events[$date]['late']);
		return $allEvents;
	}
	
	protected function DayCell($text, $width, $h, $x=null, $y=null, $more=false) {
		if($x===null)
			$x=$this->GetX();
		if($y===null)
			$y=$this->GetY();
		$this->SetCellHeightRatio(1.45);
		$this->Rect($x, $y, $width, $h, 'DF');
		$this->StartTransform(); //will clip text in the Rectangle on the next line
		$this->Rect($x, $y, $width-2, $h, 'CEO');
		$this->writeHTMLCell($width+100,$h,$x,$y, mb_convert_encoding($text,'UTF-8', 'UTF-8'), 0,0,false,true,'L');
		$this->StopTransform();
		if($more) {
			$this->SetXY($x+($width-4),$y+($h-4));
			$this->SetCellHeightRatio(1);
			$this->Cell(3, 2, html_entity_decode("&#x25BC;"), 0, 0, 'R', true);
		}
		$this->SetCellHeightRatio(1.2);
	}

}
