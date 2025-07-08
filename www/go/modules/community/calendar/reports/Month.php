<?php

namespace go\modules\community\calendar\reports;

use go\modules\community\calendar\model\CalendarEvent;
use \GO\Base\Util\Date;

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

		$this->drawEventCalendar();
	}

	protected function add($key, $instance) {
		$itr = clone $instance->utcStart;
		$end = min($this->end, $instance->utcEnd);
		while($itr <= $end) {
			$this->events[$itr->format('Ymd')][$key] = $instance;
			$itr->modify('+1 day');
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
		$firstDayOfMonth = strtotime($this->day->format('Y-m-01'));
		$firstWeekDay = date('N',$firstDayOfMonth)-1;
		$daysInMonth = $this->day->format('t');
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
		
		//$day='';
		$weeks = $this->getWeeksInMonth();
		$dateh = 5;
		$rowh = $h/$weeks;
		$colw = $w/7;
		$dayName = $this->firstWeekday===1 ? 'Monday' : 'Sunday';
		$day = (clone $this->day)->modify('last '.$dayName);
		for($r = 0; $r < $weeks; $r++){
			//Draw vertical dates
			$this->StartTransform();
				$this->Rotate(90);
				$this->Translate(-($rowh-9), 0);
				$this->Cell(7,7, $this->getWeekLabel($day),0,0,'C');
				$this->Rotate(-90);
			$this->StopTransform();
			
			$this->setCellPaddings(1,1,0,0);
			for($c=0;$c<7;$c++){ //toggle weekday
				$coord = [$this->GetX(), $this->GetY()];

				$this->SetFont(null, 'B', $this->fSizeMedium-2);
				$this->Cell($colw,5,$day->format($day->format('j') == 1 ? 'j M' : 'j'), 1,0,'L',false,'',0,false,'T','T');
				$this->SetFont(null, '', $this->fSizeSmall);

				$events = '';
				$amount = 0;
				if($this->day->format('M')===$day->format('M')) {

					if(isset($this->events[$day->format('Ymd')])) {
							
						foreach($this->events[$day->format('Ymd')] as $event) {
							if($event->showWithoutTime) {
								$events .= '<br><font color="blue">' . substr($event->title, 0, 25) . '</font>';
							} else {
								$events .= "<br>".$event->start->format('G:i') .'-'. $event->end()->format('G:i') .' '. substr($event->title,0,25);
							}
							$amount++;
						}
					}
						
					$this->SetFillColor(255);
				} else {
					$this->SetFillColor(240);
				}

				$this->DayCell($events, $colw, $rowh-$dateh, $coord[0], $coord[1]+$dateh,$amount>5);
				
				$this->SetXY($coord[0]+$colw,$coord[1]);
				$day->modify('+1 day');
			}
			$this->SetXY($this->leftMargin,$this->GetY()+$rowh);
			
		}
		$this->SetXY(10,36);
		$this->Cell($colw+7, $rowh*$weeks+6, '',1);

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
			$this->Image(self::IMG_PATH . 'arrow_down.png', null,null, 3, 3, 'PNG');
			//$this->Cell(3, 2, html_entity_decode("&#x25BC;"), 0, 0, 'R', true);
		}
		$this->SetCellHeightRatio(1.2);
	}

}
